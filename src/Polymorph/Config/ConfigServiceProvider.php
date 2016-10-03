<?php

namespace Polymorph\Config;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;

use Silex\Application;

class ConfigServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{

    /** @var string Provider name */
    protected $name = null;

    /** @var Application Silex app instance */
    protected $app = null;

    /**
     * Constructor
     *
     * @param string $name - Name under which the provider is registered
     */
    public function __construct($name = 'config')
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
        $this->app = $app;
        $app[$this->name] = function () {
            return new Config();
        };
    }

    /**
     * Boots the service provider
     *
     * @param Application $app - Silex application
     */
    public function boot(Application $app)
    {
        $this->loadFiles();
    }

    /**
     * Loads all (JSON) configuration files specified via `{self::name}.files` (usually `config.files`)
     *
     */
    protected function loadFiles()
    {
        $files = isset($this->app[$this->name . '.files'])
            ? $this->app[$this->name . '.files']
            : [];

        $mergeFields = array(
            'meta',
            'icons',
            'templates',
            'routes'
        );

        /* @var Config $config */
        $config = $this->app[$this->name];

        foreach ($files as $path) {
            $config->loadFile($path, $mergeFields);
        }
    }

}
