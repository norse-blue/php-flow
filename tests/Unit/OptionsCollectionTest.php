<?php

namespace NorseBlue\Flow\Tests\Unit;

use NorseBlue\Flow\Commands\Options\OptionsCollection;
use NorseBlue\Flow\Commands\Options\OptionType;
use NorseBlue\Flow\Exceptions\InvalidOptionIdentifierException;
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
        $this->assertEquals([], $collection->getDefinition());
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
    }

    /** @test */
    public function invalidItemsDefinitionThrowsException(): void
    {
        try {
            OptionsCollection::create([
                '--bool-option' => new \stdClass(),
            ]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\UnexpectedValueException::class, $e);
            $this->assertEquals('The option spec must be a string or an array, \'object\' given.', $e->getMessage());
            return;
        }

        $this->fail('The Exception was not thrown.');
    }

    /** @test */
    public function canCompileOptionsDefinitionWithCustomValidation(): void
    {
        $collection = OptionsCollection::create([
            '-i' => [
                'type' => OptionType::STRING,
                'validation' => function ($value, $type) {
                    return $type === 'string' && $value === 'custom_validation';
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
    }

    /** @test */
    public function canCompileItemsDefinitionWithCustomCallableValidation(): void
    {
        $collection = OptionsCollection::create([
            '--string-option' => [
                'type' => OptionType::STRING,
                'validation' => 'is_string',
            ],
        ]);

        $this->assertEquals([
            '--string-option' => [
                'type' => OptionType::STRING(),
                'validation' => 'is_string',
            ],
        ], $collection->getDefinition());
        $this->assertEquals(\Closure::fromCallable('is_string'), $collection->getValidation('--string-option'));
    }

    /** @test */
    public function canCompileItemsDefinitionWithInvalidValidation(): void
    {
        $collection = OptionsCollection::create([
            '--string-option' => [
                'type' => OptionType::STRING,
                'validation' => 'invalid_validation',
            ],
        ]);

        $this->assertEquals([
            '--string-option' => [
                'type' => OptionType::STRING(),
                'validation' => 'invalid_validation',
            ],
        ], $collection->getDefinition());
        $this->assertNull($collection->getValidation('--string-option'));
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

        $bool_validation = $collection->getValidation('--bool-option');
        $string_validation = $collection->getValidation('--string-option');

        $this->assertInstanceOf(\Closure::class, $bool_validation);
        $this->assertTrue($bool_validation(false, OptionType::BOOL));
        $this->assertFalse($bool_validation('true', OptionType::BOOL));
        $this->assertInstanceOf(\Closure::class, $string_validation);
        $this->assertTrue($string_validation('string', OptionType::STRING));
        $this->assertFalse($string_validation(true, OptionType::STRING));
    }

    /** @test */
    public function issetReturnsFalseWhenItemIsNotSet(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option' => OptionType::BOOL,
            '--string-option' => OptionType::STRING,
        ], [
            '--string-option' => 'option-value',
        ]);

        $this->assertFalse($collection->isset('--bool-option'));
    }

    /** @test */
    public function issetReturnsTrueWhenItemIsSet(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option' => OptionType::BOOL,
            '--string-option' => OptionType::STRING,
        ], [
            '--string-option' => 'option-value',
        ]);

        $this->assertTrue($collection->isset('--string-option'));
    }

    /** @test */
    public function setCanSetOptionValue(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option' => OptionType::BOOL,
        ]);

        $collection->set('--bool-option', true);

        $this->assertTrue($collection->isset('--bool-option'));
        $this->assertEquals([
            '--bool-option' => true,
        ], $collection->getItems());
    }

    /** @test */
    public function unsetCanUnsetOptionValue(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option' => OptionType::STRING,
        ], [
            '--bool-option' => true,
        ]);

        $collection->unset('--bool-option');

        $this->assertFalse($collection->isset('--bool-option'));
        $this->assertNull($collection->get('--bool-option'));
    }

    /** @test */
    public function getCanGetOptionValueByKey(): void
    {
        $collection = OptionsCollection::create([
            '--bool-option' => OptionType::BOOL,
        ], [
            '--bool-option' => true,
        ]);

        $this->assertTrue($collection->isset('--bool-option'));
        $this->assertTrue($collection->get('--bool-option'));
    }

    /** @test */
    public function getReturnsDefaultValueWhenOptionNotSet(): void
    {
        $collection = OptionsCollection::create([
            '--string-option' => OptionType::STRING,
        ]);

        $this->assertEquals([], $collection->getItems());
        $this->assertEquals('default-value', $collection->get('--string-option', 'default-value'));
    }

    /** @test */
    public function getAliasesReturnsOptionAliasesCorrectly(): void
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
