<?php

/**
 * PHPSpec helper class with re-usable methods
 */
class SpecHelper
{

    public static function appPath()
    {
        return POLYMORPH_APP_DIR;
    }
    
    public static function fixturesPath()
    {
        return POLYMORPH_APP_DIR. 'dev/php-specs/fixtures/';
    }

}
