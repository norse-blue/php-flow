<?php

namespace NorseBlue\Flow\Enums;

use MyCLabs\Enum\Enum;

/**
 * Class Primitive
 *
 * @package NorseBlue\Flow\Enums
 *
 * @method static Primitive ARRAY()
 * @method static Primitive BOOL()
 * @method static Primitive FLOAT()
 * @method static Primitive INT()
 * @method static Primitive NULL()
 * @method static Primitive OBJECT()
 * @method static Primitive RESOURCE()
 * @method static Primitive STRING()
 */
class Primitive extends Enum
{
    public const ARRAY = 'array';
    public const BOOL = 'bool';
    public const FLOAT = 'float';
    public const INT = 'int';
    public const NULL = 'null';
    public const OBJECT = 'object';
    public const RESOURCE = 'resource';
    public const STRING = 'string';
}
