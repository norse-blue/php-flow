<?php

namespace NorseBlue\Flow\Commands\Options;

use MyCLabs\Enum\Enum;
use NorseBlue\Flow\Enums\Primitive;

/**
 * Class OptionType
 *
 * @package NorseBlue\Flow\Commands\Options
 *
 * @method static OptionType BOOL()
 * @method static OptionType FLOAT()
 * @method static OptionType KVPAIR()
 * @method static OptionType INT()
 * @method static OptionType NUMERIC()
 * @method static OptionType STRING()
 */
class OptionType extends Enum
{
    public const BOOL = Primitive::BOOL;
    public const FLOAT = Primitive::FLOAT;
    public const KVPAIR = Primitive::STRING . ':key-value';
    public const INT = Primitive::INT;
    public const NUMERIC = 'numeric';
    public const STRING = Primitive::STRING;
}
