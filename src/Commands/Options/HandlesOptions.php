<?php

namespace NorseBlue\Flow\Commands\Options;

/**
 * Trait HandlesOptions
 *
 * @package NorseBlue\Flow\Commands\Options
 */
trait HandlesOptions
{
    /** @var \NorseBlue\Flow\Commands\Options\OptionsCollection The options collection */
    protected $options;

    /** @var array The options definition */
    protected $options_definition;

    /**
     * Converts the method name to option name.
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function methodToOptionName(string $name)
    {
        return str_replace('_', '-', $name);
    }
}
