<?php

namespace NorseBlue\Flow\Tests\Unit;

use NorseBlue\Flow\Commands\Arguments\ArgumentsCollection;
use NorseBlue\Flow\Commands\Arguments\ArgumentType;
use NorseBlue\Flow\FluidCommand;
use NorseBlue\Flow\Exceptions\UnsupportedArgumentTypeException;
use NorseBlue\Flow\Tests\TestCase;

/**
 * Class ArgumentsCollectionTest
 *
 * @package NorseBlue\Flow\Tests\Unit
 */
class ArgumentsCollectionTest extends TestCase
{
    /** @test */
    public function canCompileEmptyItemsDefinition(): void
    {
        $collection = ArgumentsCollection::create([]);

        $this->assertEquals([], $collection->getDefinition());
        $this->assertEquals([
            'indexed' => [],
            'named' => [],
        ], $collection->getControl());
    }

    /** @test */
    public function canCompileItemsDefinitionWithOneElement(): void
    {
        $collection = ArgumentsCollection::create([
            'path' => ArgumentType::STRING,
        ]);

        $this->assertEquals([
            'path' => ArgumentType::STRING(),
        ], $collection->getDefinition());
        $this->assertEquals([
            'indexed' => [
                0 => 'path',
            ],
            'named' => [
                'path' => [
                    'index' => 0,
                    'type' => ArgumentType::STRING(),
                    'validation' => function ($value, $type) {
                    },
                ],
            ],
        ], $collection->getControl());
    }

    /** @test */
    public function canCompileItemsDefinitionWithMultipleElements(): void
    {
        $collection = ArgumentsCollection::create([
            'path' => ArgumentType::STRING,
            'user' => ArgumentType::STRING,
            'server' => ArgumentType::STRING,
        ]);

        $this->assertEquals([
            'indexed' => [
                0 => 'path',
                1 => 'user',
                2 => 'server',
            ],
            'named' => [
                'path' => [
                    'index' => 0,
                    'type' => ArgumentType::STRING(),
                    'validation' => function ($value, $type) {
                    },
                ],
                'user' => [
                    'index' => 1,
                    'type' => ArgumentType::STRING(),
                    'validation' => function ($value, $type) {
                    },
                ],
                'server' => [
                    'index' => 2,
                    'type' => ArgumentType::STRING(),
                    'validation' => function ($value, $type) {
                    },
                ],
            ],
        ], $collection->getControl());
    }

    /** @test */
    public function canCompileItemsDefinitionWithCustomValidation(): void
    {
        $collection = ArgumentsCollection::create([
            'path' => [
                'type' => ArgumentType::STRING,
                'validation' => function ($value, $type) {
                    return $type === 'string' && $value === 'custom_validation';
                },
            ],
        ]);

        $this->assertEquals([
            'indexed' => [
                0 => 'path',
            ],
            'named' => [
                'path' => [
                    'index' => 0,
                    'type' => ArgumentType::STRING(),
                    'validation' => function ($value, $type) {
                    },
                ],
            ],
        ], $collection->getControl());
    }

    /** @test */
    public function missingArgumentTypeInDefinitionThrowsException(): void
    {
        try {
            ArgumentsCollection::create([
                'argument',
            ]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(UnsupportedArgumentTypeException::class, $e);
            $this->assertEquals(
                sprintf(
                    'The type (argument) for the given argument \'argument\' is not one of the'
                    . ' supported types or it does not implement interface \'%s\'.',
                    FluidCommand::class
                ),
                $e->getMessage()
            );
            return;
        }

        $this->fail('The Exception was not thrown.');
    }

    /** @test */
    public function unsupportedArgumentTypeInDefinitionThrowsException(): void
    {
        try {
            ArgumentsCollection::create([
                'argument' => 'unsupported_type',
            ]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(UnsupportedArgumentTypeException::class, $e);
            $this->assertEquals(
                sprintf(
                    'The type (unsupported_type) for the given argument \'argument\' is not one of the'
                    . ' supported types or it does not implement interface \'%s\'.',
                    FluidCommand::class
                ),
                $e->getMessage()
            );
            return;
        }

        $this->fail('The Exception was not thrown.');
    }

    /** @test */
    public function itemsDefinitionDefaultValidationValidatesTypeCorrectly(): void
    {
        $collection = ArgumentsCollection::create([
            'string-argument' => ArgumentType::STRING,
        ]);

        $control = $collection->getControl();

        $this->assertTrue($control['named']['string-argument']['validation']('string', ArgumentType::STRING));
        $this->assertFalse($control['named']['string-argument']['validation'](true, ArgumentType::STRING));
    }

    /** @test */
    public function setCanSetArgumentValue(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ]);

        $collection->set('command', 'argument-value');

        $this->assertEquals([
            'command' => 'argument-value',
        ], $collection->getItems());
    }

    /** @test */
    public function getCanGetArgumentValueByKey(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ], [
            'command' => 'argument-value',
        ]);

        $this->assertEquals('argument-value', $collection->get('command'));
    }

    /** @test */
    public function getCanGetArgumentValueByIndex(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ], [
            'command' => 'argument-value',
        ]);

        $this->assertEquals('argument-value', $collection->get(0));
    }

    /** @test */
    public function getThrowsExceptionWhenIndexDoesNotExist(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ], [
            'command' => 'argument-value',
        ]);

        try {
            $this->assertEquals('argument-value', $collection->get(9));
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('The index 9 is invalid.', $e->getMessage());
            return;
        }

        $this->fail('The Exception was not thrown.');
    }

    /** @test */
    public function getReturnsDefaultValueWhenArgumentNotSet(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ]);

        $this->assertEquals([], $collection->getItems());
        $this->assertEquals('default-value', $collection->get('command', 'default-value'));
    }

    /** @test */
    public function issetReturnsArgumentStateCorrectly(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ]);

        $this->assertFalse($collection->isset('command'));

        $collection->set('command', 'argument-value');

        $this->assertTrue($collection->isset('command'));
    }

    /** @test */
    public function unsetRemovesArgumentStateCorrectly(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ], [
            'command' => 'argument-value',
        ]);

        $this->assertTrue($collection->isset('command'));

        $collection->unset('command');

        $this->assertFalse($collection->isset('command'));
    }

    /** @test */
    public function getIndexReturnsCompiledArgumentIndex(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ]);

        $this->assertEquals(0, $collection->getIndex('command'));
    }

    /** @test */
    public function getIndexThrowsExceptionWhenArgumentIsInvalid(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ]);

        try {
            $collection->getIndex('invalid');
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('The key \'invalid\' is invalid.', $e->getMessage());
            return;
        }

        $this->fail('The Exception was not thrown.');
    }

    /** @test */
    public function getTypeReturnsCompiledArgumentType(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ]);

        $this->assertEquals(ArgumentType::STRING, $collection->getType('command'));
    }

    /** @test */
    public function getTypeThrowsExceptionWhenArgumentIsInvalid(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ]);

        try {
            $collection->getType('invalid');
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('The key \'invalid\' is invalid.', $e->getMessage());
            return;
        }

        $this->fail('The Exception was not thrown.');
    }

    /** @test */
    public function getValidationReturnsCompiledArgumentValidation(): void
    {
        $validation = function ($value) {
            return \is_bool($value);
        };
        $collection = ArgumentsCollection::create([
            'command' => [
                'type' => ArgumentType::STRING,
                'validation' => $validation,
            ],
        ]);

        $this->assertEquals($validation, $collection->getValidation('command'));
    }

    /** @test */
    public function getValidationThrowsExceptionWhenArgumentIsInvalid(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => [
                'type' => ArgumentType::STRING,
                'validation' => function ($value) {
                    return \is_bool($value);
                },
            ],
        ]);

        try {
            $collection->getValidation('invalid');
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('The key \'invalid\' is invalid.', $e->getMessage());
            return;
        }

        $this->fail('The Exception was not thrown.');
    }

    /** @test */
    public function emptyCollectionIsConvertedToStringCorrectly(): void
    {
        $collection = ArgumentsCollection::create([
            'source' => ArgumentType::STRING,
            'destination' => ArgumentType::STRING,
        ]);

        $this->assertEquals('', (string)$collection);
    }

    /** @test */
    public function collectionIsConvertedToStringCorrectly(): void
    {
        $collection = ArgumentsCollection::create([
            'source' => ArgumentType::STRING,
            'destination' => ArgumentType::STRING,
        ], [
            'source' => '/home/user/Downloads/my-download.tar.gz',
            'destination' => '/home/user/Backups/my-download.tar.gz',
        ]);

        $this->assertEquals(
            '/home/user/Downloads/my-download.tar.gz /home/user/Backups/my-download.tar.gz',
            (string)$collection
        );
    }

    /** @test */
    public function collectionIsConvertedToStringCorrectlyWithEscapedValues(): void
    {
        $collection = ArgumentsCollection::create([
            'source' => ArgumentType::STRING,
            'destination' => ArgumentType::STRING,
        ], [
            'source' => '/home/user/My Downloads/my-download.tar.gz',
            'destination' => '/home/user/My Backups/my-download.tar.gz',
        ]);

        $this->assertEquals(
            '"/home/user/My Downloads/my-download.tar.gz" "/home/user/My Backups/my-download.tar.gz"',
            (string)$collection
        );
    }

    /** @test */
    public function collectionIsConvertedToArrayCorrectly(): void
    {
        $collection = ArgumentsCollection::create([
            'source' => ArgumentType::STRING,
            'destination' => ArgumentType::STRING,
        ], [
            'source' => '/home/user/Downloads/my-download.tar.gz',
            'destination' => '/home/user/Backups/my-download.tar.gz',
        ]);

        $this->assertEquals(
            [
                'source' => '/home/user/Downloads/my-download.tar.gz',
                'destination' => '/home/user/Backups/my-download.tar.gz',
            ],
            $collection->toArray()
        );
    }
}
