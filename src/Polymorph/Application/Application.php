<?php

namespace Polymorph\Application;

use Silex\Application as SilexApplication;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Polymorph;
use Polymorph\Config\ConfigServiceProvider;

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
    protected $base = null;

    /**
     * Instantiate a new Application.
     *
     * @param array $values Silex parameters or objects.
     */
    public function __construct(array $values = array())
    {
        parent::__construct($values);
        // register config service provider
        $this->register(new ConfigServiceProvider('config'));
        // allow loading templates from app and polymorph src directories
        $this->register(new TwigServiceProvider(), ['twig.path' => POLYMORPH_APP_DIR]);
        $this['twig.loader.filesystem']->addPath(POLYMORPH_SRC_DIR);
    }

    /**
     * Boots all service providers and initializes Polymorph.
     */
    public function boot()
    {
        if (!$this->booted) {
            parent::boot();
            $this->initBase();
            $this->initRoutes();
            $this->initErrorHandler();
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
     * Initializes a custom handler for errors
     */
    protected function initErrorHandler()
    {
        $this->error(function (\Exception $exception, $code) {
            $controller = new ApplicationController();
            $method = "handle{$code}Request";
            if (method_exists($controller, $method)) {
                return $controller->$method($this, $exception);
            } else {
                return $controller->handleErrorRequest($this, $exception);
            }
        });
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
    protected function buildTemplateParameters($parameters)
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
            "resetCss" => $this->getResetCss(),
            "startupBgColor" => $this->config('startupBgColor'),
            "request" => $request,
            "view" => [
                "path" => $request->getPathInfo()
            ]
        ];
    }

    /**
     * Returns the configured reset-CSS code on a single line
     *
     * @return string CSS rules
     */
    protected function getResetCss()
    {
        $path = $this->config('resetCss');
        if (!$path) {
            return '';
        }
        $css = file_get_contents(POLYMORPH_APP_DIR . $path);
        $cssOnSingleLine = str_replace("\n", '', $css);
        return $cssOnSingleLine;
    }

    /**
     * Checks if the current request asks for a layout-free view partial or the whole page
     *
     * @return bool TRUE for partials, FALSE for complete pages
     */
    public function isPartialRequest()
    {
        /* @var Request $request */
        $request = $this['request_stack']->getCurrentRequest();
        return ($request->query->get('partials') === 'true');
    }
}
