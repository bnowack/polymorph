<?php

namespace Polymorph\User;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Symfony\Component\Security\Core\User\User;

class UserServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{

    /** @var string Provider name */
    protected $name = null;

    /** @var Application Silex app instance */
    protected $app = null;

    /** @var UserProvider User Provider instance */
    protected $userProvider = null;

    /**
     * Constructor
     *
     * @param string $name - Name under which the provider is registered
     */
    public function __construct($name = 'users')
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
        $app[$this->name] = function () use ($app) {
            // create user provider
            $userProviderClass = $app->config('userProvider', 'Polymorph\\User\\UserProvider');
            $this->userProvider = new $userProviderClass($app);
            // return service provider
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
    }

    /**
     * Validates username and password
     *
     * @param string $username
     * @param string $password plain password
     * @return bool TRUE if valid, FALSE otherwise
     */
    public function validateCredentials($username, $password)
    {
        return $this->userProvider->validateCredentials($username, $password);
    }

    /**
     * Returns the currently active user (defaults to guest user)
     *
     * @param bool $loadFromSession
     * @return User
     */
    public function getCurrentUser($loadFromSession = true)
    {
        return $this->userProvider->getCurrentUser($loadFromSession);
    }

    /**
     * Sets the currently active user
     *
     * @param $user
     * @return bool
     */
    public function setCurrentUser($user)
    {
        return $this->userProvider->setCurrentUser($user);
    }

    /**
     * Checks if a user (defaults to current user) has the given role
     *
     * @param $role
     * @param User|null $user
     * @return bool
     */
    public function hasRole($role, $user = null)
    {
        return $this->userProvider->hasRole($role, $user);
    }

    /**
     * Encodes a user password
     *
     * @param $password
     * @return string
     */
    public function encodePassword($password)
    {
        return $this->userProvider->encodePassword($password);
    }

    /**
     * Saves a user to the database
     *
     * @param User $user
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function saveUser(User $user)
    {
        return $this->userProvider->saveUser($user);
    }
}
