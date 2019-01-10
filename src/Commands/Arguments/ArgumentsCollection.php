<?php

namespace NorseBlue\Flow\Commands\Arguments;

use NorseBlue\Flow\Collections\BaseCollection;
use NorseBlue\Flow\FluidCommand;
use NorseBlue\Flow\Exceptions\UnsupportedArgumentTypeException;

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
            $type = \is_array($argument) ? $argument['type'] : $argument;

            if (!ArgumentType::isValid($type)) {
                throw new UnsupportedArgumentTypeException(
                    sprintf(
                        'The type (%s) for the given argument \'%s\' is not one of the supported types'
                        . ' or it does not implement interface \'%s\'.',
                        $type,
                        \is_string($key) ? $key : $type,
                        FluidCommand::class
                    )
                );
            }

            $validation = $argument['validation'] ?? function ($value, $type): bool {
                $callback = sprintf('is_%s', $type);
                if (!is_callable($callback)) {
                    throw new \RuntimeException(sprintf('Invalid callback \'%s\'.', $callback)); // @codeCoverageIgnore
                }

                return $callback($value);
            };

            $compiled['indexed'][] = $key;
            $compiled['named'][$key] = [
                'index' => count($compiled['indexed']) - 1,
                'type' => new ArgumentType($type),
                'validation' => $validation,
            ];
        }

        return $compiled;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $str = '';

        foreach ($this->control['indexed'] as $index => $key) {
            $str .= sprintf(' %s', $this->items[$key] ?? '');
        }

        return ltrim($str);
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
