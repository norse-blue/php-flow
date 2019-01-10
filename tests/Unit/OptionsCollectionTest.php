<?php

namespace NorseBlue\Flow\Tests\Unit;

use NorseBlue\Flow\Commands\Options\OptionsCollection;
use NorseBlue\Flow\Commands\Options\OptionType;
use NorseBlue\Flow\Exceptions\UnsupportedOptionTypeException;
use NorseBlue\Flow\Tests\TestCase;

/**
 * Class OptionsCollectionTest
 *
 * @package NorseBlue\Flow\Tests\Unit
 */
class OptionsCollectionTest extends TestCase
{
    /** @test */
    public function canCompileEmptyOptionsDefinition(): void
    {
        $collection = OptionsCollection::create([]);

        $this->assertEquals([], $collection->getDefinition());
        $this->assertEquals([
            'hashed' => [],
            'named' => [],
        ], $collection->getControl());
    }

    /** @test */
    public function canCompileOptionsDefinitionWithOneSingleElement(): void
    {
        $collection = OptionsCollection::create([
            '-f' => OptionType::BOOL,
        ]);

        $this->assertEquals([
            '-f' => OptionType::BOOL(),
        ], $collection->getDefinition());
        $this->assertEquals([
            'hashed' => [
                sha1((string)json_encode(['-f'])) => ['-f'],
            ],
            'named' => [
                '-f' => [
                    'aliases' => [],
                    'glue' => ' ',
                    'hash' => sha1((string)json_encode(['-f'])),
                    'type' => OptionType::BOOL(),
                    'validation' => function ($value, $type) {
                    },
                ],
            ],
        ], $collection->getControl());
    }

    /** @test */
    public function canCompileOptionsDefinitionWithOneSingleElementWithAliasInDifferentCase(): void
    {
        $collection = OptionsCollection::create([
            '-f|-F' => OptionType::BOOL,
        ]);

        $this->assertEquals([
            '-f|-F' => OptionType::BOOL(),
        ], $collection->getDefinition());
        $this->assertEquals([
            'hashed' => [
                sha1((string)json_encode(['-f', '-F'])) => ['-f', '-F'],
            ],
            'named' => [
                '-f' => [
                    'aliases' => ['-F'],
                    'glue' => ' ',
                    'hash' => sha1((string)json_encode(['-f', '-F'])),
                    'type' => OptionType::BOOL(),
                    'validation' => function ($value, $type) {
                    },
                ],
                '-F' => [
                    'aliases' => ['-f'],
                    'glue' => ' ',
                    'hash' => sha1((string)json_encode(['-f', '-F'])),
                    'type' => OptionType::BOOL(),
                    'validation' => function ($value, $type) {
                    },
                ],
            ],
        ], $collection->getControl());
    }

    /** @test */
    public function canCompileOptionsDefinitionWithOneDualElement(): void
    {
        $collection = OptionsCollection::create([
            '-f|--flag' => OptionType::BOOL,
        ]);

        $this->assertEquals([
            '-f|--flag' => OptionType::BOOL(),
        ], $collection->getDefinition());
        $this->assertEquals([
            'hashed' => [
                sha1((string)json_encode(['-f', '--flag'])) => ['-f', '--flag'],
            ],
            'named' => [
                '-f' => [
                    'aliases' => ['--flag'],
                    'glue' => ' ',
                    'hash' => sha1((string)json_encode(['-f', '--flag'])),
                    'type' => OptionType::BOOL(),
                    'validation' => function ($value, $type) {
                    },
                ],
                '--flag' => [
                    'aliases' => ['-f'],
                    'glue' => ' ',
                    'hash' => sha1((string)json_encode(['-f', '--flag'])),
                    'type' => OptionType::BOOL(),
                    'validation' => function ($value, $type) {
                    },
                ],
            ],
        ], $collection->getControl());
    }

    /** @test */
    public function canCompileOptionsDefinitionWithMultipleElements(): void
    {
        $collection = OptionsCollection::create([
            '-f' => OptionType::BOOL,
            '--flag' => OptionType::BOOL,
            '-d|--dual-flag' => OptionType::BOOL,
            '-i' => OptionType::STRING,
            '-c|--config' => OptionType::STRING,
        ]);

        $this->assertEquals([
            '-f' => OptionType::BOOL(),
            '--flag' => OptionType::BOOL(),
            '-d|--dual-flag' => OptionType::BOOL(),
            '-i' => OptionType::STRING(),
            '-c|--config' => OptionType::STRING(),
        ], $collection->getDefinition());
        $this->assertEquals([
            'hashed' => [
                sha1((string)json_encode(['-f'])) => ['-f'],
                sha1((string)json_encode(['--flag'])) => ['--flag'],
                sha1((string)json_encode(['-d', '--dual-flag'])) => ['-d', '--dual-flag'],
                sha1((string)json_encode(['-i'])) => ['-i'],
                sha1((string)json_encode(['-c', '--config'])) => ['-c', '--config'],
            ],
            'named' => [
                '-f' => [
                    'aliases' => [],
                    'glue' => ' ',
                    'hash' => sha1((string)json_encode(['-f'])),
                    'type' => OptionType::BOOL(),
                    'validation' => function ($value, $type) {
                    },
                ],
                '--flag' => [
                    'aliases' => [],
                    'glue' => ' ',
                    'hash' => sha1((string)json_encode(['--flag'])),
                    'type' => OptionType::BOOL(),
                    'validation' => function ($value, $type) {
                    },
                ],
                '-d' => [
                    'aliases' => ['--dual-flag'],
                    'glue' => ' ',
                    'hash' => sha1((string)json_encode(['-d', '--dual-flag'])),
                    'type' => OptionType::BOOL(),
                    'validation' => function ($value, $type) {
                    },
                ],
                '--dual-flag' => [
                    'aliases' => ['-d'],
                    'glue' => ' ',
                    'hash' => sha1((string)json_encode(['-d', '--dual-flag'])),
                    'type' => OptionType::BOOL(),
                    'validation' => function ($value, $type) {
                    },
                ],
                '-i' => [
                    'aliases' => [],
                    'glue' => ' ',
                    'hash' => sha1((string)json_encode(['-i'])),
                    'type' => OptionType::STRING(),
                    'validation' => function ($value, $type) {
                    },
                ],
                '-c' => [
                    'aliases' => ['--config'],
                    'glue' => ' ',
                    'hash' => sha1((string)json_encode(['-c', '--config'])),
                    'type' => OptionType::STRING(),
                    'validation' => function ($value, $type) {
                    },
                ],
                '--config' => [
                    'aliases' => ['-c'],
                    'glue' => ' ',
                    'hash' => sha1((string)json_encode(['-c', '--config'])),
                    'type' => OptionType::STRING(),
                    'validation' => function ($value, $type) {
                    },
                ],
            ],
        ], $collection->getControl());
    }

    /** @test */
    public function canCompileOptionsDefinitionWithCustomValidation(): void
    {
        $collection = OptionsCollection::create([
            '-i' => [
                'type' => OptionType::STRING,
                'validation' => function ($value, $type) {
                },
            ],
        ]);

        $this->assertEquals([
            '-i' => [
                'type' => OptionType::STRING(),
                'validation' => function ($value, $type) {
                },
            ],
        ], $collection->getDefinition());
        $this->assertEquals([
            'hashed' => [
                sha1((string)json_encode(['-i'])) => ['-i'],
            ],
            'named' => [
                '-i' => [
                    'aliases' => [],
                    'glue' => ' ',
                    'hash' => sha1((string)json_encode(['-i'])),
                    'type' => OptionType::STRING(),
                    'validation' => function ($value, $type) {
                    },
                ],
            ],
        ], $collection->getControl());
    }

    /** @test */
    public function unsupportedOptionTypeInDefinitionThrowsException(): void
    {
        try {
            OptionsCollection::create([
                'option' => 'unsupported_type',
            ]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(UnsupportedOptionTypeException::class, $e);
            $this->assertEquals('The type for the option \'option\' is not supported.', $e->getMessage());
            return;
        }

        $this->fail('The Exception was not thrown.');
    }

    /** @test */
    public function optionsDefinitionDefaultValidationValidatesTypeCorrectly(): void
    {
        $collection = OptionsCollection::create([
            'bool-option' => OptionType::BOOL,
            'string-option' => OptionType::STRING,
        ]);

        $compilation = $collection->getControl();

        $this->assertTrue($compilation['named']['bool-option']['validation'](false, OptionType::BOOL));
        $this->assertFalse($compilation['named']['bool-option']['validation']('true', OptionType::BOOL));
        $this->assertTrue($compilation['named']['string-option']['validation']('string', OptionType::STRING));
        $this->assertFalse($compilation['named']['string-option']['validation'](true, OptionType::STRING));
    }

    /** @test */
    public function setCanSetOptionValue(): void
    {
        $collection = OptionsCollection::create([
            'bool-option' => OptionType::BOOL,
        ]);

        $collection->set('bool-option', true);

        $this->assertEquals([
            sha1((string)json_encode(['bool-option'])) => [
                'key' => 'bool-option',
                'value' => true
            ],
        ], $collection->getItems());
    }

    /** @test */
    public function getCanGetOptionValueByKey(): void
    {
        $collection = OptionsCollection::create([
            'bool-option' => OptionType::BOOL,
        ], [
            'bool-option' => true,
        ]);

        $this->assertTrue($collection->get('bool-option'));
    }

    /** @test */
    public function getReturnsDefaultValueWhenOptionNotSet(): void
    {
        $collection = OptionsCollection::create([
            'bool-option' => OptionType::BOOL,
        ]);

        $this->assertEquals([], $collection->getItems());
        $this->assertEquals('default-value', $collection->get('bool-option', 'default-value'));
    }

    /** @test */
    public function issetReturnsOptionStateCorrectly(): void
    {
        $collection = OptionsCollection::create([
            'bool-option' => OptionType::BOOL,
        ]);

        $this->assertFalse($collection->isset('bool-option'));

        $collection->set('bool-option', false);

        $this->assertTrue($collection->isset('bool-option'));
    }

    /** @test */
    public function unsetRemovesArgumentStateCorrectly(): void
    {
        $collection = OptionsCollection::create([
            'bool-option' => OptionType::STRING,
        ], [
            'bool-option' => false,
        ]);

        $this->assertTrue($collection->isset('bool-option'));

        $collection->unset('bool-option');

        $this->assertFalse($collection->isset('bool-option'));
    }

    /** @test */
    public function getAliasesReturnsCompiledOptionAliases(): void
    {
        $collection = OptionsCollection::create([
            'bool-option|bool-alias' => OptionType::BOOL,
        ]);

        $this->assertEquals(['bool-alias'], $collection->getAliases('bool-option'));
        $this->assertEquals(['bool-option', 'bool-alias'], $collection->getAliases('bool-option', true));
    }

    /** @test */
    public function getAliasesThrowsExceptionWhenOptionIsInvalid(): void
    {
        $collection = OptionsCollection::create([
            'bool-option|bool-alias' => OptionType::BOOL,
        ]);

        try {
            $collection->getAliases('invalid');
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('The key \'invalid\' is invalid.', $e->getMessage());
            return;
        }

        $this->fail('The Exception was not thrown.');
    }

    /** @test */
    public function getHashIsReturnedForOption(): void
    {
        $collection = OptionsCollection::create([
            'b|bool-option' => OptionType::BOOL,
        ]);

        $this->assertEquals(sha1((string)json_encode(['b', 'bool-option'])), $collection->getHash('bool-option'));
    }

    /** @test */
    public function getHashThrowsExceptionWhenOptionIsInvalid(): void
    {
        $collection = OptionsCollection::create([
            'b|bool-option' => OptionType::BOOL,
        ]);

        try {
            $collection->getHash('invalid');
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('The key \'invalid\' is invalid.', $e->getMessage());
            return;
        }

        $this->fail('The Exception was not thrown.');
    }

    /** @test */
    public function getTypeReturnsCompiledOptionType(): void
    {
        $collection = OptionsCollection::create([
            'bool-option' => OptionType::BOOL,
        ]);

        $this->assertEquals(OptionType::BOOL, $collection->getType('bool-option'));
    }

    /** @test */
    public function getTypeThrowsExceptionWhenOptionIsInvalid(): void
    {
        $collection = OptionsCollection::create([
            'bool-option' => OptionType::BOOL,
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
    public function emptyCollectionIsConvertedToStringCorrectly(): void
    {
        $collection = OptionsCollection::create([
            'bool-option' => OptionType::BOOL,
            'string-option' => OptionType::STRING,
        ]);

        $this->assertEquals('', (string)$collection);
    }

    /** @test */
    public function collectionIsConvertedToStringCorrectly(): void
    {
        $collection = OptionsCollection::create([
            'bool-option' => OptionType::BOOL,
            'string-option' => OptionType::STRING,
        ], [
            'bool-option' => true,
            'string-option' => 'string-value',
        ]);

        $this->assertEquals(
            'bool-option string-option string-value',
            (string)$collection
        );
    }

    /** @test */
    public function collectionWithBoolOptionSetToFalseIsConvertedToStringCorrectly(): void
    {
        $collection = OptionsCollection::create([
            'bool-option' => OptionType::BOOL,
            'string-option' => OptionType::STRING,
        ], [
            'bool-option' => false,
            'string-option' => 'string-value',
        ]);

        $this->assertEquals(
            'string-option string-value',
            (string)$collection
        );
    }
}
