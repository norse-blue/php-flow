<?php

namespace NorseBlue\Flow\Tests\Unit;

use NorseBlue\Flow\Commands\Options\OptionsCollection;
use NorseBlue\Flow\Commands\Options\OptionType;
use NorseBlue\Flow\Exceptions\InvalidOptionIdentifierException;
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
                sha1('-f') => [
                    'aliases' => [
                        '-f',
                    ],
                    'glue' => ' ',
                    'type' => OptionType::BOOL(),
                    'validation' => function ($value, $type) {
                    },
                ],
            ],
            'named' => [
                '-f' => sha1('-f'),
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
                sha1('-f|-F') => [
                    'aliases' => ['-f', '-F'],
                    'glue' => ' ',
                    'type' => OptionType::BOOL(),
                    'validation' => function ($value, $type) {
                    },
                ],
            ],
            'named' => [
                '-f' => sha1('-f|-F'),
                '-F' => sha1('-f|-F'),
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
                sha1('-f|--flag') => [
                    'aliases' => ['-f', '--flag'],
                    'glue' => ' ',
                    'type' => OptionType::BOOL(),
                    'validation' => function ($value, $type) {
                    },
                ],
            ],
            'named' => [
                '-f' => sha1('-f|--flag'),
                '--flag' => sha1('-f|--flag'),
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
                sha1('-f') => [
                    'aliases' => ['-f'],
                    'glue' => ' ',
                    'type' => OptionType::BOOL(),
                    'validation' => function ($value, $type) {
                    },
                ],
                sha1('--flag') => [
                    'aliases' => ['--flag'],
                    'glue' => ' ',
                    'type' => OptionType::BOOL(),
                    'validation' => function ($value, $type) {
                    },
                ],
                sha1('-d|--dual-flag') => [
                    'aliases' => ['-d', '--dual-flag'],
                    'glue' => ' ',
                    'type' => OptionType::BOOL(),
                    'validation' => function ($value, $type) {
                    },
                ],
                sha1('-i') => [
                    'aliases' => ['-i'],
                    'glue' => ' ',
                    'type' => OptionType::STRING(),
                    'validation' => function ($value, $type) {
                    },
                ],
                sha1('-c|--config') => [
                    'aliases' => ['-c', '--config'],
                    'glue' => ' ',
                    'type' => OptionType::STRING(),
                    'validation' => function ($value, $type) {
                    },
                ],
            ],
            'named' => [
                '-f' => sha1('-f'),
                '--flag' => sha1('--flag'),
                '-d' => sha1('-d|--dual-flag'),
                '--dual-flag' => sha1('-d|--dual-flag'),
                '-i' => sha1('-i'),
                '-c' => sha1('-c|--config'),
                '--config' => sha1('-c|--config'),
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
                sha1('-i') => [
                    'aliases' => ['-i'],
                    'glue' => ' ',
                    'type' => OptionType::STRING(),
                    'validation' => function ($value, $type) {
                    },
                ],
            ],
            'named' => [
                '-i' => sha1('-i'),
            ],
        ], $collection->getControl());
    }

    /** @test */
    public function missingOptionTypeInDefinitionThrowsException(): void
    {
        try {
            OptionsCollection::create([
                '--option',
            ]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(InvalidOptionIdentifierException::class, $e);
            $this->assertEquals('The given option identifier "0" is not valid.', $e->getMessage());
            return;
        }

        $this->fail('The Exception was not thrown.');
    }

    /** @test */
    public function unsupportedOptionTypeInDefinitionThrowsException(): void
    {
        try {
            OptionsCollection::create([
                '--option' => 'unsupported_type',
            ]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\UnexpectedValueException::class, $e);
            $this->assertEquals(sprintf(
                'Value \'unsupported_type\' is not part of the enum %s',
                OptionType::class
            ), $e->getMessage());
            return;
        }

        $this->fail('The Exception was not thrown.');
    }

    /** @test */
    public function optionsDefinitionDefaultValidationValidatesTypeCorrectly(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option' => OptionType::BOOL,
            '--string-option' => OptionType::STRING,
        ]);

        $this->assertTrue(is_callable($collection->getValidation('--bool-option'))
            ? $collection->getValidation('--bool-option')(false, OptionType::BOOL)
            : false);
        $this->assertFalse(is_callable($collection->getValidation('--bool-option'))
            ? $collection->getValidation('--bool-option')('true', OptionType::BOOL)
            : true);
        $this->assertTrue(is_callable($collection->getValidation('--string-option'))
            ? $collection->getValidation('--string-option')('string', OptionType::STRING)
            : false);
        $this->assertFalse(is_callable($collection->getValidation('--string-option'))
            ? $collection->getValidation('--string-option')(true, OptionType::STRING)
            : true);
    }

    /** @test */
    public function setCanSetOptionValue(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option' => OptionType::BOOL,
        ]);

        $collection->set('--bool-option', true);

        $this->assertEquals([
            sha1('--bool-option') => [
                'key' => '--bool-option',
                'value' => true,
            ],
        ], $collection->getItems());
    }

    /** @test */
    public function getCanGetOptionValueByKey(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option' => OptionType::BOOL,
        ], [
            '--bool-option' => true,
        ]);

        $this->assertTrue($collection->get('--bool-option'));
    }

    /** @test */
    public function getReturnsDefaultValueWhenOptionNotSet(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option' => OptionType::BOOL,
        ]);

        $this->assertEquals([], $collection->getItems());
        $this->assertEquals('default-value', $collection->get('--bool-option', 'default-value'));
    }

    /** @test */
    public function issetReturnsOptionStateCorrectly(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option' => OptionType::BOOL,
        ]);

        $this->assertFalse($collection->isset('--bool-option'));

        $collection->set('--bool-option', false);

        $this->assertTrue($collection->isset('--bool-option'));
    }

    /** @test */
    public function unsetRemovesArgumentStateCorrectly(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option' => OptionType::STRING,
        ], [
            '--bool-option' => false,
        ]);

        $this->assertTrue($collection->isset('--bool-option'));

        $collection->unset('--bool-option');

        $this->assertFalse($collection->isset('--bool-option'));
    }

    /** @test */
    public function getAliasesReturnsCompiledOptionAliases(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option|--bool-alias' => OptionType::BOOL,
        ]);

        $this->assertEquals(['--bool-alias'], $collection->getAliases('--bool-option'));
        $this->assertEquals(['--bool-option', '--bool-alias'], $collection->getAliases('--bool-option', true));
    }

    /** @test */
    public function getAliasesThrowsExceptionWhenOptionIsInvalid(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option|--bool-alias' => OptionType::BOOL,
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
            '-b|--bool-option' => OptionType::BOOL,
        ]);

        $this->assertEquals(sha1('-b|--bool-option'), $collection->getHash('--bool-option'));
    }

    /** @test */
    public function getHashThrowsExceptionWhenOptionIsInvalid(): void
    {
        $collection = OptionsCollection::create([
            '-b|--bool-option' => OptionType::BOOL,
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
            '--bool-option' => OptionType::BOOL,
        ]);

        $this->assertEquals(OptionType::BOOL, $collection->getType('--bool-option'));
    }

    /** @test */
    public function getTypeThrowsExceptionWhenOptionIsInvalid(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option' => OptionType::BOOL,
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
            '--bool-option' => OptionType::BOOL,
            '--string-option' => OptionType::STRING,
        ]);

        $this->assertEquals('', (string)$collection);
    }

    /** @test */
    public function collectionIsConvertedToStringCorrectly(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option' => OptionType::BOOL,
            '--string-option' => OptionType::STRING,
        ], [
            '--bool-option' => true,
            '--string-option' => 'string-value',
        ]);

        $this->assertEquals(
            '--bool-option --string-option string-value',
            (string)$collection
        );
    }

    /** @test */
    public function collectionIsConvertedToStringCorrectlyWithEscapedValues(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option' => OptionType::BOOL,
            '--string-option' => OptionType::STRING,
        ], [
            '--bool-option' => true,
            '--string-option' => 'string value',
        ]);

        $this->assertEquals(
            '--bool-option --string-option "string value"',
            (string)$collection
        );
    }

    /** @test */
    public function collectionWithBoolOptionSetToFalseIsConvertedToStringCorrectly(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option' => OptionType::BOOL,
            '--string-option' => OptionType::STRING,
        ], [
            '--bool-option' => false,
            '--string-option' => 'string-value',
        ]);

        $this->assertEquals(
            '--string-option string-value',
            (string)$collection
        );
    }

    /** @test */
    public function collectionIsConvertedToArrayCorrectly(): void
    {
        $collection = OptionsCollection::create([
            '--bool-true-option' => OptionType::BOOL,
            '--bool-false-option' => OptionType::BOOL,
            '--bool-null-option' => OptionType::BOOL,
            '--string-option' => OptionType::STRING,
            '--string-null-option' => OptionType::STRING,
        ], [
            '--bool-true-option' => true,
            '--bool-false-option' => false,
            '--string-option' => 'string-value',
        ]);

        $this->assertEquals(
            [
                '--bool-true-option' => true,
                '--bool-false-option' => false,
                '--bool-null-option' => null,
                '--string-option' => 'string-value',
                '--string-null-option' => null,
            ],
            $collection->toArray()
        );
    }

    /** @test */
    public function collectionIsConvertedToArrayWithNotSetValuesFilteredOutCorrectly(): void
    {
        $collection = OptionsCollection::create([
            '--bool-true-option' => OptionType::BOOL,
            '--bool-false-option' => OptionType::BOOL,
            '--bool-null-option' => OptionType::BOOL,
            '--string-option' => OptionType::STRING,
            '--string-null-option' => OptionType::STRING,
        ], [
            '--bool-true-option' => true,
            '--bool-false-option' => false,
            '--string-option' => 'string-value',
        ]);

        $this->assertEquals(
            [
                '--bool-true-option' => true,
                '--bool-false-option' => false,
                '--string-option' => 'string-value',
            ],
            $collection->toArray(true)
        );
    }

    /** @test */
    public function validatesInvalidIdentifierTypeCorrectly(): void
    {
        $collection = OptionsCollection::create([]);

        $this->assertFalse($collection->validateIdentifier(new \stdClass()));
    }

    /** @test */
    public function validIdentifiersAreCorrectlyValidated(): void
    {
        $collection = OptionsCollection::create([
            '-v|--validId' => OptionType::STRING,
            '--validIdentifier' => OptionType::STRING,
            '--valid_identifier' => OptionType::STRING,
            '--valid-identifier' => OptionType::STRING,
        ]);

        $this->assertEquals([
            '-v|--validId' => OptionType::STRING,
            '--validIdentifier' => OptionType::STRING,
            '--valid_identifier' => OptionType::STRING,
            '--valid-identifier' => OptionType::STRING,
        ], $collection->getDefinition());
    }

    /** @test */
    public function invalidIdentifierBeginningWithNumberIsCorrectlyValidated(): void
    {
        try {
            OptionsCollection::create([
                '0_invalidIdentifier' => OptionType::STRING,
            ]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(InvalidOptionIdentifierException::class, $e);
            return;
        }

        $this->fail('The Exception was not thrown.');
    }

    /** @test */
    public function invalidIdentifierWithSpaceIsCorrectlyValidated(): void
    {
        try {
            OptionsCollection::create([
                'invalid identifier' => OptionType::STRING,
            ]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(InvalidOptionIdentifierException::class, $e);
            return;
        }

        $this->fail('The Exception was not thrown.');
    }
}
