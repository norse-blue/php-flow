<?php

namespace NorseBlue\Flow\Tests\Unit;

use NorseBlue\Flow\Exceptions\InvalidArgumentIdentifierException;
use NorseBlue\Flow\Commands\Arguments\ArgumentsCollection;
use NorseBlue\Flow\Commands\Arguments\ArgumentType;
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
    }

    /** @test */
    public function invalidItemsDefinitionThrowsException(): void
    {
        try {
            ArgumentsCollection::create([
                'path' => new \stdClass(),
            ]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\UnexpectedValueException::class, $e);
            $this->assertEquals('The argument spec must be a string or an array, \'object\' given.', $e->getMessage());
            return;
        }

        $this->fail('The Exception was not thrown.');
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
            'path' => ArgumentType::STRING(),
            'user' => ArgumentType::STRING(),
            'server' => ArgumentType::STRING(),
        ], $collection->getDefinition());
    }

    /** @test */
    public function canCompileItemsDefinitionWithCustomClosureValidation(): void
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
            'path' => [
                'type' => ArgumentType::STRING(),
                'validation' => function ($value, $type) {
                },
            ],
        ], $collection->getDefinition());
    }

    /** @test */
    public function canCompileItemsDefinitionWithCustomCallableValidation(): void
    {
        $collection = ArgumentsCollection::create([
            'path' => [
                'type' => ArgumentType::STRING,
                'validation' => 'is_string',
            ],
        ]);

        $this->assertEquals([
            'path' => [
                'type' => ArgumentType::STRING(),
                'validation' => 'is_string',
            ],
        ], $collection->getDefinition());
        $this->assertEquals(\Closure::fromCallable('is_string'), $collection->getValidation('path'));
    }

    /** @test */
    public function canCompileItemsDefinitionWithInvalidValidation(): void
    {
        $collection = ArgumentsCollection::create([
            'path' => [
                'type' => ArgumentType::STRING,
                'validation' => 'invalid_validation',
            ],
        ]);

        $this->assertEquals([
            'path' => [
                'type' => ArgumentType::STRING(),
                'validation' => 'invalid_validation',
            ],
        ], $collection->getDefinition());
        $this->assertNull($collection->getValidation('path'));
    }

    /** @test */
    public function missingArgumentTypeInDefinitionThrowsException(): void
    {
        try {
            ArgumentsCollection::create([
                'argument',
            ]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(InvalidArgumentIdentifierException::class, $e);
            $this->assertEquals('The given argument identifier "0" is not valid.', $e->getMessage());
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
            $this->assertInstanceOf(\UnexpectedValueException::class, $e);
            $this->assertEquals(
                sprintf(
                    'Value \'unsupported_type\' is not part of the enum %s',
                    ArgumentType::class
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

        $validation = $collection->getValidation('string-argument');

        $this->assertInstanceOf(\Closure::class, $validation);
        $this->assertTrue($validation('string', ArgumentType::STRING));
        $this->assertFalse($validation(true, ArgumentType::STRING));
    }

    /** @test */
    public function issetReturnsFalseWhenItemIsNotSet(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ]);

        $this->assertFalse($collection->isset('command'));
    }

    /** @test */
    public function issetReturnsTrueWhenItemIsSet(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ], [
            'command' => 'argument-value',
        ]);

        $this->assertTrue($collection->isset('command'));
    }

    /** @test */
    public function setCanSetArgumentValue(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ]);

        $collection->set('command', 'argument-value');

        $this->assertTrue($collection->isset('command'));
        $this->assertEquals([
            'command' => 'argument-value',
        ], $collection->getItems());
    }

    /** @test */
    public function unsetCanUnsetArgumentValue(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ], [
            'command' => 'argument-value',
        ]);

        $collection->unset('command');

        $this->assertFalse($collection->isset('command'));
        $this->assertNull($collection->get('command'));
    }

    /** @test */
    public function getCanGetArgumentValueByKey(): void
    {
        $collection = ArgumentsCollection::create([
            'command' => ArgumentType::STRING,
        ], [
            'command' => 'argument-value',
        ]);

        $this->assertTrue($collection->isset('command'));
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

        $this->assertFalse($collection->isset('command'));
        $this->assertEquals('default-value', $collection->get('command', 'default-value'));
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

    /** @test */
    public function collectionIsConvertedToArrayWithNotSetValuesFilteredOutCorrectly(): void
    {
        $collection = ArgumentsCollection::create([
            'source' => ArgumentType::STRING,
            'destination' => ArgumentType::STRING,
        ], [
            'source' => '/home/user/Downloads/my-download.tar.gz',
        ]);

        $this->assertEquals(
            [
                'source' => '/home/user/Downloads/my-download.tar.gz',
            ],
            $collection->toArray(true)
        );
    }

    /** @test */
    public function validatesInvalidIdentifierTypeCorrectly(): void
    {
        $collection = ArgumentsCollection::create([]);

        $this->assertFalse($collection->validateIdentifier(new \stdClass()));
    }

    /** @test */
    public function validIdentifiersAreCorrectlyValidated(): void
    {
        $collection = ArgumentsCollection::create([
            'validIdentifier' => ArgumentType::STRING,
            'valid_identifier' => ArgumentType::STRING,
            'valid-identifier' => ArgumentType::STRING,
        ]);

        $this->assertEquals([
            'validIdentifier' => ArgumentType::STRING,
            'valid_identifier' => ArgumentType::STRING,
            'valid-identifier' => ArgumentType::STRING,
        ], $collection->getDefinition());
    }

    /** @test */
    public function invalidIdentifierBeginningWithNumberIsCorrectlyValidated(): void
    {
        try {
            ArgumentsCollection::create([
                '0_invalidIdentifier' => ArgumentType::STRING,
            ]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(InvalidArgumentIdentifierException::class, $e);
            return;
        }

        $this->fail('The Exception was not thrown.');
    }

    /** @test */
    public function invalidIdentifierWithSpaceIsCorrectlyValidated(): void
    {
        try {
            ArgumentsCollection::create([
                'invalid identifier' => ArgumentType::STRING,
            ]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(InvalidArgumentIdentifierException::class, $e);
            return;
        }

        $this->fail('The Exception was not thrown.');
    }
}
