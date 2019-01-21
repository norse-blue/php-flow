<p align="center">
  <a href="https://travis-ci.org/norse-blue/flow"><img src="https://img.shields.io/travis/com/norse-blue/flow.svg" alt="Build Status"></img></a>
  <a href="https://scrutinizer-ci.com/g/norse-blue/flow"><img src="https://img.shields.io/scrutinizer/g/norse-blue/flow.svg" alt="Quality Score"></img></a>
  <a href="https://scrutinizer-ci.com/g/norse-blue/flow"><img src="https://img.shields.io/scrutinizer/coverage/g/norse-blue/flow.svg" alt="Coverage"></img></a>
  <a href="https://packagist.org/packages/norse-blue/flow"><img src="https://img.shields.io/packagist/dt/norse-blue/flow.svg" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/norse-blue/flow"><img src="https://img.shields.io/packagist/v/norse-blue/flow.svg" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/norse-blue/flow"><img src="https://img.shields.io/packagist/l/norse-blue/flow.svg" alt="License"></a>
</p>

## About

Flow allows to create command classes that have a fluid interface to set arguments an options, e.g. `$id_cmd->user('axel')->_g(true);` to get the command as an executable string: `id -g axel`.

## Installation

> **Requires [PHP 7.3+](https://php.net/releases/)**

Require Flow using [Composer](https://getcomposer.org):

```bash
composer require norse-blue/flow
```
## Usage

There are two main ways to use this package, both methods are equivalent.

### Inheritance

Create a new command class that inherits from `NorseBlue\Flow\Commands\FluidBaseCommand` and configure the arguments and options accordingly.

```php
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
```

### Composition

If you can't/won't use inheritance you can include the `NorseBlue\Flow\Commands\HandlesCommandInternals` trait to your class, configure the arguments and options accordingly and initialize the command anywhere (preferably in the constructor).

```php
class IdCommand
{
    use NorseBlue\Flow\Commands\HandloesCommandInternals;
  
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
    
    public function __construct() {
        $this->init($this->arguments_definition ?? [], $this->options_definition ?? []);
    }
}
```

## Command Configuration

The command uses three properties to be configured: `name`, `arguments_definition` and `options_definition`.

### Name

This is the commands name. This will be prepended as-is to the resulting string. This name is the name of the command to be executed: `id`, `cat`, `git`, `composer`, etc.

### Arguments definition

This is the definition array for the command arguments. The item's key is how the argument is identified and used for the command calls, e.g. `'user'`: `$cmd->user('valiue');`.

#### Array structure

```php
// Arguments definition
$arguments_defintiion = [
    {key} => {type},
];
/**
 * {key}: string argument identifier. It has to begin with a letter (either case) and can contain letters, numbers, underscore and dash. It cannot end in a dash. No spaces allowed
 * {type}: string or array. If a string is given then it has to be one of the ArgumentType enum values. If an array is given it has to match the argument spec structure.
 */

// Argument spec structure
$argument_spec = [
    'type' => {type},
    'validation' => {validation},
];
/**
 * {type}: string. One of the ArgumentType enum values.
 * {validation}: \Closure [optional]. A closure that validates the argument value when it is set.
 */
```

### Options definition

This is the definition array for the command options. The item's key is how the option is identified and used for the command calls, e.g. `'-g'`: `$cmd->_f(true);` (_note that for calls the underscore is used instead of the dash, but the definition requires the dash_).

#### Array structure

```php
// Options definition
$options_defintiion = [
    {key} => {type},
];
/**
 * {key}: string argument identifier. It must begin with '-' or '--' and a letter (either case). IT can contain letters, numbers, underscore, dash. It cannot end ina  dash. The option can have aliases separating them with '|', e.g. '-f|--flag'. No spaces allowed.
 * {type}: string or array. If a string is given then it has to be one of the OptionType enum values. If an array is given it has to match the option spec structure.
 */

// Option spec structure
$option_spec = [
    'type' => {type},
    'glue' => {glue},
    'validation' => {validation},
];
/**
 * {type}: string. One of the OptionType enum values.
 * {glue}: string [optional]. It is the string that glues the option identifier with its value when converting to string. Usually it does not need to be set. The default value is ' '.
 * {validation}: \Closure [optional]. A closure that validates the option value when it is set.
 */
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security
If you discover any security-related issues, please email [security@norse.blue](axel.pardemann@norse.blue) instead of using the issue tracker.

## License

Flow is release under the [MIT license](LICENSE.md).