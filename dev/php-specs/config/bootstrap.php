<?php

// Ensure time() is E_STRICT-compliant
date_default_timezone_set(@date_default_timezone_get());

// Define polymorph app path constant (root is 3 hops up from `dev/phpspecs/config/bootstrap.php`)
define("POLYMORPH_APP_DIR", dirname(dirname(dirname(__DIR__))) . '/');

// Define polymorph source path constant (directly in app dir during development)
define("POLYMORPH_SRC_DIR", POLYMORPH_APP_DIR . 'src/');

// Include autoloader
require_once POLYMORPH_APP_DIR . 'vendor/autoload.php';

// load phpspec helper
include_once(POLYMORPH_APP_DIR . 'dev/php-specs/config/SpecHelper.php');
