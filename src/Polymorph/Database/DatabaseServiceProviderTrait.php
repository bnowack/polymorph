<?php

namespace Polymorph\Database;
use Doctrine\DBAL\Connection;

/**
 * Polymorph database service provider trait
 *
 * Extends service providers with database-specific features
 *
 * @property array $tableDefinitions
 */
trait DatabaseServiceProviderTrait
{

    /** @var Connection */
    protected $connection;

    /**
     * Returns the table definitions, e.g. for schema diffs
     *
     * @return array
     */
    public function getTableDefinitions()
    {
        return $this->tableDefinitions;
    }

    /**
     * Returns a table definition
     *
     * @return array
     */
    public function getTableDefinition($tableName)
    {
        return $this->tableDefinitions[$tableName];
    }

    /**
     * Returns a database connection
     *
     * @return Connection
     */
    public function getConnection($databaseName)
    {
        if (!$this->connection) {
            $this->connection = $this->app['db']->connect($databaseName);
        }

        return $this->connection;
    }

    /**
     * Builds database table values from an instance and the table definition
     *
     * @param object $instance
     * @param string $tableName
     *
     * @return array
     */
    public function encodeTableValues($instance, $tableName)
    {
        $values = [];
        $tableDefinition = $this->getTableDefinition($tableName);
        array_walk($tableDefinition, function ($type, $column) use ($instance, &$values) {
            $values[$column] = $this->encodeTableValue($instance, $column, $type);
        });
        return $values;
    }

    /**
     * Encodes an instance property to a database table column
     *
     * @param object $instance
     * @param string $property
     * @param string $type
     * @return int|string
     */
    protected function encodeTableValue($instance, $property, $type)
    {
        $value = $instance->$property;

        if ($value === null) {
            return null;
        }

        // convert className in $type to 'object'
        if (class_exists($type, false)) {
            $type = 'object';
        }

        // encode value
        switch ($type) {
            case 'int':
                return intval($value);
            case 'bool':
                return ($value === true) ? 1 : 0;
            case 'array':
                return json_encode($value ?: []);
            case 'object':
                return json_encode($value);
            default:
                return $value;
        }
    }

    /**
     * Parses database table values to object property values
     *
     * @param array $row
     * @param string $tableName
     *
     * @return array
     */
    public function decodeTableValues($row, $tableName)
    {
        $values = [];
        $tableDefinition = $this->getTableDefinition($tableName);
        array_walk($tableDefinition, function ($type, $column) use ($row, &$values) {
            $values[$column] = $this->decodeTableValue($row, $column, $type);
        });
        return $values;
    }

    /**
     * Converts a single database table value to an object property value
     *
     * @param array $row
     * @param string $field Field name
     * @param string $type Field target type (int, bool, string, array, object, or an object::class)
     *
     * @return bool|int|object|string|null
     */
    protected function decodeTableValue($row, $field, $type = 'string')
    {
        if (!isset($row[$field])) {
            return null;
        }

        // return instance if $type is a class name
        if (class_exists($type, true)) {
            return new $type(json_decode($row[$field]));
        }

        switch ($type) {
            case 'int':
                return intval($row[$field]);
            case 'bool':
                return (intval($row[$field]) === 1);
            case 'array':
                return json_decode($row[$field], true);
            case 'object':
                return json_decode($row[$field]);
            default:
                return $row[$field];
        }
    }
}
