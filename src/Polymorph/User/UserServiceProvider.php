<?php

namespace Polymorph\User;

use Polymorph\Application\ServiceProvider;

use Pimple\Container;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Registers the service provider
     *
     * @param Container $app - Pimple container / Silex app
     */
    public function register(Container $app)
    {
        // register self
        $app[$this->name] = function () use ($app) {
            /** @noinspection PhpUndefinedMethodInspection */
            $userProviderClass = $app->config('userProvider', 'Polymorph\\User\\UserProvider');
            // instantiate and return user provider
            return new $userProviderClass($app);
        };
    }
}
