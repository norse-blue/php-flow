<?php

namespace NorseBlue\Flow\Commands\Arguments;

use MyCLabs\Enum\Enum;
use NorseBlue\Flow\FluidCommand;
use NorseBlue\Flow\Enums\Primitive;

/**
 * Class ArgumentType
 *
 * @package NorseBlue\Flow\Commands\Arguments
 *
 * @method static ArgumentType STRING()
 */
class ArgumentType extends Enum
{
    public const STRING = Primitive::STRING;

    /**
     * Validates the given argument type value.
     *
     * @param mixed $type
     *
     * @return bool
     */
    public static function isValid($type): bool
    {
        $implements = @class_implements($type);

        return parent::isValid($type)
            || ($implements !== false && \in_array(FluidCommand::class, $implements, true));
    }
}
