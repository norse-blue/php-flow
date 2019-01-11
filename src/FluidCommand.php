<?php

namespace NorseBlue\Flow;

use NorseBlue\Flow\Commands\Arguments\ArgumentsCollection;
use NorseBlue\Flow\Commands\Options\OptionsCollection;

/**
 * Interface FluidCommand
 *
 * @package NorseBlue\Flow
 */
interface FluidCommand
{
    /**
     * Initializes the command compiling the arguments and options collections.
     *
     * @param string|array $arguments_definition The arguments definition property name or the actual definition array
     * @param string|array $options_definition   The options definition property name or the actual definition array
     */
    public function init($arguments_definition, $options_definition): void;

    /**
     * Gets the command name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Gets the arguments collection.
     *
     * @return \NorseBlue\Flow\Commands\Arguments\ArgumentsCollection
     */
    public function getArguments(): ArgumentsCollection;

    /**
     * Gets the options collection.
     *
     * @return \NorseBlue\Flow\Commands\Options\OptionsCollection
     */
    public function getOptions(): OptionsCollection;
}
