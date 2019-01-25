<?php

namespace NorseBlue\Flow\Commands\Arguments;

use NorseBlue\Flow\Collections\BaseCollection;
use NorseBlue\Flow\Exceptions\InvalidArgumentIdentifierException;

/**
 * Class ArgumentsCollection
 *
 * @package NorseBlue\Flow\Commands\Arguments
 */
class ArgumentsCollection extends BaseCollection
{
    /**
     * Creates a new instance of the collection from a definition.
     *
     * @param array      $definition
     * @param array|null $items
     *
     * @return \NorseBlue\Flow\Commands\Arguments\ArgumentsCollection
     *
     * @todo Try to move this to the BaseCollection class when PHP7.4 arrives with the covariant return types
     * @see  https://wiki.php.net/rfc/covariant-returns-and-contravariant-parameters}
     */
    public static function create(array $definition, $items = null): ArgumentsCollection
    {
        return new static($definition, $items);
    }

    /**
     * {@inheritdoc}
     */
    protected function compile(array $definition): array
    {
        $compiled = ['indexed' => [], 'named' => []];

        foreach ($definition as $key => $argument) {
            if (!$this->validateIdentifier($key)) {
                throw new InvalidArgumentIdentifierException(
                    sprintf('The given argument identifier "%s" is not valid.', $key)
                );
            }

            $compiled['indexed'][] = $key;
            $compiled['named'][$key] = \array_merge([
                'index' => count($compiled['indexed']) - 1,
            ], $this->compileSpec($argument));
        }

        return $compiled;
    }

    /**
     * Compiles the argument spec.
     *
     * @param string|array $argument
     *
     * @return array
     */
    protected function compileSpec($argument): array
    {
        if (\is_string($argument)) {
            return [
                'type' => new ArgumentType($argument),
                'validation' => function ($value, $type): bool {
                    $callback = sprintf('is_%s', $type);
                    if (!is_callable($callback)) {
                        // @codeCoverageIgnoreStart
                        throw new \RuntimeException(
                            sprintf('Invalid callback \'%s\'.', $callback)
                        );
                        // @codeCoverageIgnoreEnd
                    }

                    return $callback($value);
                },
            ];
        }

        if (!\is_array($argument)) {
            throw new \UnexpectedValueException(sprintf(
                'The argument spec must be a string or an array, %s given',
                gettype($argument)
            ));
        }

        return [
            'type' => new ArgumentType($argument['type']),
            'validation' => $this->ensureValidationClosure($argument['validation']),
        ];
    }

    /**
     * Ensures that the validation is a closure or null.
     *
     * @param \Closure|callable|null $validation
     *
     * @return \Closure|null
     */
    protected function ensureValidationClosure($validation): ?callable
    {
        if ($validation instanceof \Closure) {
            return $validation;
        }

        if (is_callable($validation)) {
            return \Closure::fromCallable($validation);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $str = '';

        foreach ($this->control['indexed'] as $index => $key) {
            $str .= sprintf(' %s', $this->escapeValue($this->get($key)));
        }

        return ltrim($str);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(bool $filterUnset = false): array
    {
        $items = [];
        foreach ($this->control['indexed'] as $index => $key) {
            if ($filterUnset && !$this->isset($key)) {
                continue;
            }

            $items[$key] = $this->get($key);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseKey($key): string
    {
        if (\is_int($key)) {
            if (!\array_key_exists($key, $this->control['indexed'])) {
                throw new \InvalidArgumentException(sprintf('The index %s is invalid.', $key));
            }

            return $this->control['indexed'][$key];
        }

        return $key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateIdentifier($key): bool
    {
        if (!parent::validateIdentifier($key)) {
            return false;
        }

        return (bool)preg_match('/^[a-zA-Z](?:[a-zA-Z0-9_\-]+)?(?<!\-)$/', (string)$key);
    }

    /**
     * Gets the argument's index.
     *
     * @param string $key
     *
     * @return int
     */
    public function getIndex(string $key): int
    {
        $key = $this->ensureValidKey($key);

        return $this->control['named'][$key]['index'];
    }

    /**
     * Gets the argument's type.
     *
     * @param int|string $key
     *
     * @return ArgumentType
     */
    public function getType($key): ArgumentType
    {
        $key = $this->ensureValidKey($key);

        return $this->control['named'][$key]['type'];
    }

    /**
     * Gets the argument's validation.
     *
     * @param int|string $key
     *
     * @return null|callable
     */
    public function getValidation($key): ?callable
    {
        $key = $this->ensureValidKey($key);

        return $this->control['named'][$key]['validation'];
    }
}
