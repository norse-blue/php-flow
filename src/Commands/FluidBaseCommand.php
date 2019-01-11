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

    /**
     * Gets the command as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        $str = $this->name;

        $options = (string)$this->options;
        if (!empty($options)) {
            $str .= sprintf(' %s', $options);
        }

        $arguments = (string)$this->arguments;
        if (!empty($arguments)) {
            $str .= sprintf(' %s', $arguments);
        }

        return $str;
    }
}
