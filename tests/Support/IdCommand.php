<?php

namespace NorseBlue\Flow\Tests\Support;

use NorseBlue\Flow\Commands\Arguments\ArgumentType;
use NorseBlue\Flow\Commands\FluidBaseCommand;
use NorseBlue\Flow\Commands\Options\OptionType;

/**
 * Class FluidCommandExample
 *
 * @package NorseBlue\Flow\Tests\Support
 *
 * ===== Arguments =====
 * @method self user(string $value)
 * @property string $user
 *
 * ===== Options =====
 * @method self _a(bool $value)
 * @method self _g(bool $value)
 * @method self _G(bool $value)
 * @method self _n(bool $value)
 * @method self _r(bool $value)
 * @method self _u(bool $value)
 * @method self _z(bool $value)
 * @method self _Z(bool $value)
 * @method self __help(bool $value)
 * @method self __version(bool $value)
 * @property bool $_a
 * @property bool $_g
 * @property bool $_G
 * @property bool $_n
 * @property bool $_r
 * @property bool $_u
 * @property bool $_z
 * @property bool $_Z
 * @property bool $__help
 * @property bool $__version
 */
class IdCommand extends FluidBaseCommand
{
    protected $name = 'id';

    protected $arguments_definition = [
        'user' => ArgumentType::STRING,
    ];

    protected $options_definition = [
        '-a' => OptionType::BOOL,
        '-Z|--context' => OptionType::BOOL,
        '-g|--group' => OptionType::BOOL,
        '-G|--groups' => OptionType::BOOL,
        '-n|--name' => OptionType::BOOL,
        '-r|--real' => OptionType::BOOL,
        '-u|--user' => OptionType::BOOL,
        '-z|--zero' => OptionType::BOOL,
        '--help' => OptionType::BOOL,
        '--version' => OptionType::BOOL,
    ];
}
