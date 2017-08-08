<?php

namespace Polymorph\Schema\Versions;

use Polymorph\Schema\Version;

class Version201708081250 extends Version
{

    /**
     * Applies the version
     *
     * @return bool TRUE on success
     * @throws \Exception When admin account is not configured
     */
    public function apply()
    {
        return $this->initQuickCheckTable();
    }

    /**
     * Creates a table for schema quick-checks
     *
     * @return bool TRUE on success
     */
    protected function initQuickCheckTable()
    {
        $sql = '
          CREATE TABLE IF NOT EXISTS `QuickCheck` (
            `hash` TEXT,
            `checked` INTEGER
          );
        ';
        return $this->executeSql($sql, 'schema');
    }

    /**
     * Reverts the schema migration
     *
     * @return bool
     */
    public function revert()
    {
        // drop QuickCheck table
        return $this->executeSql('DROP TABLE IF EXISTS `QuickCheck`', 'schema');
    }
}
