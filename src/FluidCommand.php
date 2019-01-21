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
     * @param array $arguments_definition The arguments definition array
     * @param array $options_definition   The options definition array
     */
    public function init(array $arguments_definition, array $options_definition): void;

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
