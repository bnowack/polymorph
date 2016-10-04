<?php

// Ensure time() is E_STRICT-compliant
date_default_timezone_set(@date_default_timezone_get());

// Define polymorph app path constant (root is 4 hops up from `/vendor/bnowack/polymorph/src/index.php`)
if (!defined("POLYMORPH_APP_DIR")) {
    define("POLYMORPH_APP_DIR", dirname(dirname(dirname(dirname(__DIR__)))) . '/');
}

// Define polymorph source path constant (same dir as this file)
if (!defined("POLYMORPH_SRC_DIR")) {
    define("POLYMORPH_SRC_DIR", __DIR__ . '/');
}

// Include autoloader
require_once POLYMORPH_APP_DIR . 'vendor/autoload.php';

// Create and start the application
$app = new Polymorph\Application\Application();
$app['debug'] = false;
$app['config.files'] = [
    POLYMORPH_SRC_DIR . 'Polymorph/Application/config/base-config.json',// base config
    POLYMORPH_APP_DIR . 'config/app-config.json'// app config
];
$app->run();
