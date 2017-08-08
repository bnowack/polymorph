<?php

namespace Polymorph\Schema\Versions;

use Polymorph\Schema\Version;
use Polymorph\User\UserServiceProvider;
use Polymorph\User\User;

class Version201610261615 extends Version
{

    /**
     * Applies the version
     *
     * @return bool TRUE on success
     * @throws \Exception When admin account is not configured
     * @todo consider backup dump for loss-less re-applying?
     */
    public function apply()
    {
        // make sure admin account exists
        $adminAccount = $this->app->config('adminAccount');
        if (!$adminAccount) {
            throw new \Exception('Please configure an administrator account (e.g. "adminAccount": "mail@example.com")');
        }

        return
            $this->initVersionsTable() &&
            $this->initUserTable() &&
            $this->initAdminAccount();
    }

    /**
     * Creates a table for schema versions
     *
     * @return bool TRUE on success
     */
    protected function initVersionsTable()
    {
        $sql = '
          CREATE TABLE IF NOT EXISTS `Version` (
            `version` TEXT,
            `applied` INTEGER
          );
        ';
        return $this->executeSql($sql, 'schema');
    }

    /**
     * Creates a table for user accounts
     *
     * @return bool TRUE on success
     */
    protected function initUserTable()
    {
        $sql = '
          CREATE TABLE IF NOT EXISTS `User` (
            `username` TEXT UNIQUE NOT NULL,
            `password` TEXT,
            `roles` TEXT,
            `enabled` INTEGER NOT NULL DEFAULT 1,
            `expired` INTEGER NOT NULL DEFAULT 0,
            `credentialsExpired` INTEGER NOT NULL DEFAULT 0,
            `locked` INTEGER NOT NULL DEFAULT 0
          );
        ';
        return $this->executeSql($sql, 'users');
    }

    /**
     * Creates the initial admin account with an empty password
     *
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    protected function initAdminAccount()
    {
        /* @var UserServiceProvider $users */
        $users = $this->app['users'];

        // create user account with empty password
        $adminAccount = $this->app->config('adminAccount');
        $user = new User($adminAccount, $users->encodePassword(''), ['admin']);
        return $users->saveUser($user);
    }

    /**
     * Removes Version and User tables
     *
     * @todo create backup dump for loss-less re-applying?
     * @return bool
     */
    public function revert()
    {
        // drop User table
        $this->executeSql('DROP TABLE IF EXISTS `User`', 'users');

        // drop Version table
        return $this->executeSql('DROP TABLE IF EXISTS `Version`', 'schema');
    }
}
