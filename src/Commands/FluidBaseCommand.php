<?php

namespace NorseBlue\Flow\Commands;

use NorseBlue\Flow\FluidCommand;

/**
 * Class FluidCommand
 *
 * @package NorseBlue\Flow\Commands
 */
abstract class FluidBaseCommand implements FluidCommand
{
    use HandlesCommandInternals;

    /**
     * FluidBaseCommand constructor.
     */
    public function __construct()
    {
        $this->init($this->arguments_definition ?? [], $this->options_definition ?? []);
    }
}
