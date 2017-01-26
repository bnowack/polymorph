<?php

namespace Polymorph\Application;

use Silex\Application as SilexApplication;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Polymorph;
use Polymorph\Config\ConfigServiceProvider;
use Polymorph\Database\DatabaseServiceProvider;
use Polymorph\Security\SecurityServiceProvider;
use Polymorph\User\UserServiceProvider;
use Polymorph\Schema\SchemaServiceProvider;

/**
 * Polymorph Application class
 *
 */
class Application extends SilexApplication
{
    use SilexApplication\TwigTrait {
        render as twigRender;
    }
    use Polymorph\Config\ConfigTrait;

    /** @var string Application base path (with trailing slash) */
    public $base = null;

    /**
     * Instantiate a new Application.
     *
     * @param array $values Silex parameters or objects.
     */
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        // register error handler
        $this->error(array($this, 'onError'));

        // register config service provider
        $this->register(new ConfigServiceProvider('config'));

        // register twig service provider, allow loading templates from app and polymorph src directories
        $this->register(new TwigServiceProvider(), [
            'twig.path' => POLYMORPH_APP_DIR,
            'twig.options' => [
                'strict_variables' => false
            ]
        ]);
        $this['twig.loader.filesystem']->addPath(POLYMORPH_SRC_DIR);

        // register DB service provider
        $this->register(new DatabaseServiceProvider('db'));

        // register schema service provider
        $this->register(new SchemaServiceProvider('schema'));

        // register session service provider
        $this->register(new SessionServiceProvider());

        // register user service provider
        $this->register(new UserServiceProvider('users'));

        // register security service provider
        $this->register(new SecurityServiceProvider('security'));
    }

    /**
     * Boots all service providers and initializes Polymorph.
     */
    public function boot()
    {
        if (!$this->booted) {
            parent::boot();
            $this->initCustomServiceProviders();
            $this->initBase();
            $this->initRoutes();
        }
    }

    /**
     * Registers and boots any service providers defined in config
     */
    protected function initCustomServiceProviders()
    {
        foreach ($this->config('serviceProviders', []) as $serviceName => $providerClassName) {
            $this->register(new $providerClassName($serviceName));
            $this[$serviceName]->boot($this);
        }
    }

    /**
     * Detects and sets the application's base path from configured bases and the given request
     *
     * @param Request|null $request Request
     */
    protected function initBase(Request $request = null)
    {
        if (null === $request) {
            $request = Request::createFromGlobals();
        }
        $base = '/';// default
        $requestPath = $request->getPathInfo();// includes any sub-dir paths from web root
        $configuredBases = $this->config('base');
        if (!is_array($configuredBases)) {
            $configuredBases = [$configuredBases];
        }
        foreach ($configuredBases as $configuredBase) {
            if (strpos($requestPath, $configuredBase) === 0) {
                $base = $configuredBase;
                break;// break on first match
            }
        }
        $this->base = $base;
    }

    /**
     * Initializes the application routes specified in the configuration
     */
    public function initRoutes()
    {
        $routes = $this->config('routes', array());
        foreach ($routes as $path => $handler) {
            $pathWithBase = $this->base . ltrim($path, '/');
            $this->match($pathWithBase, $handler);
        }
    }

    /**
     * Renders errors
     *
     * @param \Exception $exception Exception instance
     * @param integer $code Error code
     *
     * @return Response
     */
    public function onError(\Exception $exception, $code = 500)
    {
        $controller = new ApplicationController();
        $method = "handle{$code}Request";
        if (method_exists($controller, $method)) {
            return $controller->$method($this, $exception);
        } else {
            return $controller->handleErrorRequest($this, $exception);
        }
    }

    /**
     * Renders a view and returns a Response
     *
     * To stream a view, pass an instance of StreamedResponse as a third argument.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     * @param Response $response A Response instance
     *
     * @return Response A Response instance
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        $templateParameters = $this->buildTemplateParameters($parameters);
        return $this->twigRender($view, $templateParameters, $response);
    }

    /**
     * Extends the passed parameters with parameters shared by all views
     *
     * @param array $parameters List of view parameters
     * @return array Extended parameters
     */
    public function buildTemplateParameters($parameters)
    {
        $globalParameters = $this->getGlobalTemplateParameters();
        $combinedParameters = $globalParameters;
        foreach ($parameters as $name => $value) {
            if (!isset($combinedParameters[$name])) {
                $combinedParameters[$name] = $value;
            } elseif (is_array($combinedParameters[$name])) {
                $combinedParameters[$name] = array_merge($combinedParameters[$name], $value);
            } else {
                $combinedParameters[$name] = $value;
            }
        }
        return $combinedParameters;
    }

    /**
     * Returns parameters that are shared/used by all view templates
     *
     * @return array Template parameters
     */
    protected function getGlobalTemplateParameters()
    {
        /* @var Request $request */
        $request = $this['request_stack']->getCurrentRequest();

        return [
            "base" => $this->base,
            "meta" => (array)$this->config('meta'),
            "icons" => (array)$this->config('icons'),
            "templates" => (array)$this->config('templates'),
            "startupBgColor" => $this->config('startupBgColor'),
            "request" => $request,
            "view" => [
                "path" => $request->getPathInfo()
            ],
            "baseTemplate" => $this->isPartialRequest($request)
                ? $this->config('templates')->partial
                : $this->config('templates')->page
        ];
    }

    /**
     * Checks if the given request asks for a layout-free view partial or the whole page
     *
     * @param Request $request
     *
     * @return bool TRUE for partials, FALSE for complete pages
     */
    public function isPartialRequest($request = null)
    {
        /* @var Request $request */
        if (null === $request) {
            $request = $this['request_stack']->getCurrentRequest();
        }
        return ($request->query->get('partials') === 'true');
    }
}
