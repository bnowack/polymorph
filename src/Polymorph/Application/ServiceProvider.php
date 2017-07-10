<?php

namespace Polymorph\Application;

use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Pimple\Container;
use Silex\Application as SilexApplication;
use Polymorph\Application\Application as PolymorphApplication;

class ServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    /** @var string Provider name */
    protected $name = null;

    /** @var PolymorphApplication Polymorph app */
    protected $app = null;

    /**
     * Constructor
     *
     * @param string $name Name under which the provider is registered
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Registers the service provider
     *
     * @param Container $app Pimple container / Silex app
     */
    public function register(Container $app)
    {
        $app[$this->name] = function () use ($app) {
            // return service provider
            return $this;
        };
    }

    /**
     * Boots the service provider
     *
     * @param SilexApplication $app Silex application
     */
    public function boot(SilexApplication $app)
    {
        $this->app = $app;
    }
}
