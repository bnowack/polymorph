<?php

namespace Polymorph\Config;

/**
 * Polymorph Config trait *
 */
trait ConfigTrait
{
    /**
     * Gets a config option
     *
     * @param string $name Option name
     * @param mixed $default Default value
     *
     * @return mixed|null Option value
     */
    public function config($name, $default = null)
    {
        // retrieve from config
        /* @var $config Config */
        $config = $this['config'];
        $value = $config->get($name, $default);

        // replace constants
        $matches = null;
        while (is_string($value) && preg_match('/{([A-Z0-9_]+)}/', $value, $matches)) {
            $value = str_replace("{{$matches[1]}}", defined($matches[1]) ? constant($matches[1]) : '', $value);
        }

        return $value;
    }
}
