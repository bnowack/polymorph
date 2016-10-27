<?php

namespace Polymorph\Database;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Polymorph\Application\Application as PolymorphApplication;

class DatabaseServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{

    /** @var string Provider name */
    protected $name = null;

    /** @var PolymorphApplication Application instance */
    protected $app = null;

    /** @var string Directory for sqlite databases */
    protected $directory = null;

    /** @var array Database connections */
    protected $connections = [];

    /**
     * Constructor
     *
     * @param string $name - Name under which the provider is registered
     */
    public function __construct($name = 'db')
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

        // init directory
        $this->initDirectory();
    }

    /**
     * Creates the directory for sqlite database files
     *
     * @throws \Exception When the directory cannot be created
     */
    protected function initDirectory()
    {
        $this->directory = POLYMORPH_APP_DIR . $this->app->config('dataDirectory') . '/databases';
        if (!is_dir($this->directory)) {
            $umask = umask(0);
            mkdir($this->directory, 0777, true);
            chmod($this->directory, 0777);
            umask($umask);
        }
        if (!is_dir($this->directory)) {
            throw new \Exception('Could not create `databases` directory');
        }
    }

    /**
     * Connects to a database
     *
     * @param string $dbName Database name as specified in the configuration
     * @return Connection Doctrine DBAL connection
     */
    public function connect($dbName)
    {
        if (isset($this->connections[$dbName])) {
            return $this->connections[$dbName];
        }
        // get options
        $options = $this->getConnectionOptions($dbName);
        // create configuration
        $config = new Configuration();
        // create manager
        $manager = new EventManager();
        // create database file
        if ($options['driver'] === 'pdo_sqlite' && !is_file($options['path'])) {
            touch($options['path']);
            chmod($options['path'], 0777);
        }
        // create and return connection
        $this->connections[$dbName] = DriverManager::getConnection($options, $config, $manager);
        return $this->connections[$dbName];
    }

    /**
     * Returns DB connection options and injects a database path for sqlite DBs
     *
     * @param string $dbName Database name as specified in the configuration
     * @return array Connection options
     */
    protected function getConnectionOptions($dbName)
    {
        $options = (array)$this->app->config('dbs')->$dbName;
        if ($options['driver'] === 'pdo_sqlite' && !isset($options['path'])) {
            $options['path'] = $this->directory . '/' . $dbName . '.sqlite';
        }
        return $options;
    }
}
