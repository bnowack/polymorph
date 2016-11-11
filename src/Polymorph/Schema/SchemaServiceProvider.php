<?php

namespace Polymorph\Schema;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use FilesystemIterator;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Polymorph\Application\Application as PolymorphApplication;

class SchemaServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{

    /** @var string Provider name */
    protected $name = null;

    /** @var PolymorphApplication App instance */
    protected $app = null;

    /**
     * Constructor
     *
     * @param string $name - Name under which the provider is registered
     */
    public function __construct($name = 'schema')
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
        // set app reference
        $this->app = $app;
    }

    /**
     * Checks the schema for applied versions and migrates the database if needed
     *
     * @return array List of currently applied versions
     */
    public function checkSchema()
    {
        $applied = $this->getAppliedVersions();// flat list with version strings
        $available = $this->getAvailableVersions();
        $latestAvailable = empty($available)
            ? null
            : $available[0]['version'];
        $targetVersion = $this->app->config('schemaVersion', $latestAvailable);

        // versions that should be applied
        $applicable = array_filter($available, function ($version) use ($targetVersion) {
            return $version['version'] <= $targetVersion;
        });

        // versions that should not be applied
        $nonApplicable = array_reverse(array_filter($available, function ($version) use ($targetVersion) {
            return $version['version'] > $targetVersion;
        }));

        // add missing versions
        foreach ($applicable as $version) {
            if (!in_array($version['version'], $applied)) {
                $this->applyVersion($version['className']);
            }
        }

        // remove superfluous versions
        foreach ($nonApplicable as $version) {
            if (in_array($version['version'], $applied)) {
                $this->revertVersion($version['className']);
            }
        }

        return $this->getAppliedVersions();// flat list with version strings
    }

    /**
     * Returns a list of currently applied versions
     *
     * @return array List of applied versions
     */
    protected function getAppliedVersions()
    {
        /* @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->app['db']->connect('schema');
        $queryBuilder = $conn->createQueryBuilder();
        $query = $queryBuilder
            ->select('version')
            ->from('Version')
            ->orderBy('version', 'DESC');
        try {
            $rows = $query->execute()->fetchAll();
            $result = array_map(function ($row) {
                return $row['version'];
            }, $rows);
        } catch (TableNotFoundException $exception) {
            // migration table not created yet
            $result = [];
        }
        return $result;
    }

    /**
     * Scans version directories for schema version files
     *
     * @return array List of version structures with `path`, `className`, and `version` info, sorted by date
     * @throws \Exception When there are conflicting version files
     */
    protected function getAvailableVersions()
    {
        $versions = [];
        $dirs = $this->getVersionDirectories();
        foreach ($dirs as $dir) {
            foreach (new FilesystemIterator($dir) as $file) {
                if (!$file->isFile()) {
                    continue;
                }
                $className = $file->getBasename('.php');
                $versionString = $className;
                if (isset($versions[$versionString])) {
                    $versionPath = $versions[$versionString]['path'];
                    throw new \Exception("Schema version $className already defined at $versionPath.");
                }
                $versions[$versionString] = [
                    'path' => $file->getPathname(),
                    'className' => 'Polymorph\\Schema\\Versions\\' . $className,
                    'version'=> $versionString
                ];
            }
        }
        ksort($versions);// sort by version/date ascending
        return array_values($versions);
    }

    /**
     * Returns a list of version file directories
     *
     * @return array List of paths
     */
    protected function getVersionDirectories()
    {
        $dirs = [
            POLYMORPH_SRC_DIR . 'Polymorph/Application/config/schema-versions', // polymorph system schema versions
            POLYMORPH_APP_DIR . 'config/schema-versions'// application schema versions
        ];
        return array_filter($dirs, function ($dir) {
            return is_dir($dir);
        });
    }

    /**
     * Applies a database version
     *
     * @param $versionClass Version class name
     * @return bool TRUE on success, FALSE otherwise
     */
    public function applyVersion($versionClass)
    {
        /* @var Version $version */
        $version = new $versionClass($this->app);
        if ($version->apply()) {
            return $version->applied();
        }
        return false;
    }

    /**
     * Reverts a database version
     *
     * @param $versionClass Version class name
     * @return bool TRUE on success, FALSE otherwise
     */
    public function revertVersion($versionClass)
    {
        /* @var Version $version */
        $version = new $versionClass($this->app);
        if ($version->revert()) {
            return $version->reverted();
        }
        return false;
    }
}
