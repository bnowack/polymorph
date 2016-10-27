<?php

namespace Polymorph\Security;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Provider\CsrfServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;
use Symfony\Component\Security\Csrf\CsrfToken;
use Polymorph\Application\Application as PolymorphApplication;

class SecurityServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{

    /** @var string Provider name */
    protected $name = null;

    /** @var PolymorphApplication Application instance */
    protected $app = null;

    /**
     * Constructor
     *
     * @param string $name - Name under which the provider is registered
     */
    public function __construct($name = 'security')
    {
        $this->name = $name;
    }

    /**
     * Registers the service provider
     *
     * @param Container $app - Pimple container / Silex app
     */
    public function register(Container $app)
    {
        // register self
        $app[$this->name] = function () {
            return $this;
        };

        // register CSRF service provider
        $app->register(new CsrfServiceProvider());
    }

    /**
     * Boots the service provider
     *
     * @param Application $app - Silex application
     */
    public function boot(Application $app)
    {
        // save app reference
        $this->app = $app;
    }

    /**
     * Renders the login form
     *
     * @return Response
     */
    public function renderLoginForm()
    {
        $controller = new SecurityController();
        return $controller->renderLoginForm($this->app);
    }

    /**
     * Returns a CSRF form token
     *
     * @param string $contextId Token context, e.g. 'login'
     * @return string Token
     */
    public function getToken($contextId)
    {
        return $this->app['csrf.token_manager']->getToken($contextId);
    }

    /**
     * Validates a CSRF form token
     *
     * @param string $contextId Token context, e.g. 'login'
     * @param string $token Token string
     * @return TRUE if valid, FALSE otherwise
     */
    public function validateToken($contextId, $token)
    {
        return $this->app['csrf.token_manager']->isTokenValid(new CsrfToken($contextId, $token));
    }
}
