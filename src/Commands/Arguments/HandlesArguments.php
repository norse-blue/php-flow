<?php

namespace NorseBlue\Flow\Commands\Arguments;

/**
 * Trait HandlesArguments
 *
 * @package NorseBlue\Flow\Commands\Arguments
 */
trait HandlesArguments
{
    /** @var \NorseBlue\Flow\Commands\Arguments\ArgumentsCollection The arguments collection */
    protected $arguments;

    /** @var array The arguments definition */
    protected $arguments_definition;
}
