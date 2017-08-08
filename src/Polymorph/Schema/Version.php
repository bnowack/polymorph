<?php

namespace Polymorph\Schema;

use Polymorph\Application\Application;

/**
 * Base class for schema versions
 */
class Version
{

    /** @var Application $app Application instance */
    protected $app = null;

    /** @var string $name Version name (class name without namespaces) */
    protected $name = null;

    /**
     * Constructor
     *
     * @param Application $app - Application instance
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->name = preg_replace('/^.*\\\([^\\\]+)/', '\\1', get_class($this));
    }

    /**
     * Runs SQL against a database
     *
     * @param string $sql SQL query
     * @param string $dbName Database name as specified in the configuration
     * @return bool TRUE on success, FALSE otherwise
     */
    protected function executeSql($sql, $dbName)
    {
        /* @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->app['db']->connect($dbName);
        $statement = $conn->prepare($sql);
        return $statement->execute();
    }

    /**
     * Returns a query builder instance for a database
     *
     * @param string $dbName Database name as specified in the configuration
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function query($dbName)
    {
        /* @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->app['db']->connect($dbName);
        return $conn->createQueryBuilder();
    }

    /**
     * Applies a version, extensible by sub-class
     *
     * @return bool TRUE on success, FALSE otherwise
     */
    public function apply()
    {
        return true;
    }

    /**
     * Reverts a version, extensible by sub-class
     *
     * @return bool TRUE on success, FALSE otherwise
     */
    public function revert()
    {
        return true;
    }

    /**
     * Logs the applied version to the version table
     *
     * @return bool TRUE on success, FALSE otherwise
     */
    public function applied()
    {
        return $this->query('schema')
            ->insert('Version')
            ->setValue('version', ':version')->setParameter('version', $this->name)
            ->setValue('applied', ':applied')->setParameter('applied', time())
            ->execute();
    }

    /**
     * Removes the reverted version from the version table
     *
     * @return bool TRUE on success, FALSE otherwise
     */
    public function reverted()
    {
        return $this->query('schema')
            ->delete('Version')
            ->where('version = :version')->setParameter('version', $this->name)
            ->execute();
    }
}
