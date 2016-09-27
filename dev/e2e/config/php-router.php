<?php

/**
 * Router script for developing and testing the Polymorph framework
 */

// don't allow access to the router script during production
if (php_sapi_name() !== 'cli-server') {
    die('The router script is only available during development');
}

// Ensure time() is E_STRICT-compliant
date_default_timezone_set(@date_default_timezone_get());

// Define polymorph app path constant (root is 3 hops up from `dev/e2e/config/php-router.php`)
define("POLYMORPH_APP_DIR", dirname(dirname(dirname(__DIR__))) . '/');

// Define polymorph source path constant (directly in app dir during development)
define("POLYMORPH_SRC_DIR", POLYMORPH_APP_DIR . 'src/');

// Include autoloader
require_once POLYMORPH_APP_DIR . 'vendor/autoload.php';

$asset = POLYMORPH_APP_DIR . preg_replace('/(\?.*)$/', '', ltrim($_SERVER['REQUEST_URI'], '/'));

if (is_file($asset)) {// Serve static assets
    return false;
} else {// Serve dynamic contents
    $app = new Polymorph\Application\Application();
    $app['debug'] = true;
    $app['config.files'] = [
        POLYMORPH_SRC_DIR . 'Polymorph/Application/config/base-config.json',// base config
        POLYMORPH_APP_DIR . 'dev/polymorph/app-config.json'// dev config
    ];
    $app->run();
}
