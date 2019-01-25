<?php

namespace NorseBlue\Flow\Commands\Options;

use NorseBlue\Flow\Collections\BaseCollection;
use NorseBlue\Flow\Exceptions\InvalidOptionIdentifierException;

/**
 * Class OptionsCollection
 *
 * @package NorseBlue\Flow\Commands\Options
 */
class OptionsCollection extends BaseCollection
{
    /**
     * Creates a new instance of the collection from a definition.
     *
     * @param array      $definition
     * @param array|null $items
     *
     * @return \NorseBlue\Flow\Commands\Options\OptionsCollection
     *
     * @todo Try to move this to the BaseCollection class when PHP7.4 arrives with the covariant return types
     * @see  https://wiki.php.net/rfc/covariant-returns-and-contravariant-parameters}
     */
    public static function create(array $definition, $items = null): OptionsCollection
    {
        return new static($definition, $items);
    }

    /**
     * {@inheritdoc}
     */
    protected function compile(array $definition): array
    {
        $compiled = ['hashed' => [], 'named' => []];

        foreach ($definition as $key => $option) {
            if (!$this->validateIdentifier($key)) {
                throw new InvalidOptionIdentifierException(
                    sprintf('The given option identifier "%s" is not valid.', $key)
                );
            }

            $hash = sha1($key);
            $compiled['hashed'][$hash] = $this->compileSpec($key, $option);
            $compiled['named'] += $this->compileNamedOptions($hash, $compiled['hashed'][$hash]['aliases']);
        }

        return $compiled;
    }

    /**
     * Compiles the option spec.
     *
     * @param string       $key
     * @param string|array $option
     *
     * @return array
     */
    protected function compileSpec(string $key, $option): array
    {
        $aliases = array_unique(explode('|', $key), SORT_REGULAR);

        if (\is_string($option)) {
            return [
                'aliases' => $aliases,
                'glue' => ' ',
                'type' => new OptionType($option),
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

        if (!\is_array($option)) {
            throw new \UnexpectedValueException(sprintf(
                'The option spec must be a string or an array, %s given',
                gettype($option)
            ));
        }

        return [
            'aliases' => $aliases,
            'glue' => $option['glue'] ?? ' ',
            'type' => new OptionType($option['type']),
            'validation' => $this->ensureValidationClosure($option['validation']),
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
     * Compiles the named options from the spec.
     *
     * @param string $hash
     * @param array  $aliases
     *
     * @return array
     */
    protected function compileNamedOptions(string $hash, array $aliases): array
    {
        $named = [];
        foreach ($aliases as $alias) {
            $named[$alias] = $hash;
        }

        return $named;
    }

    /**
     * {@inheritdoc}
     */
    public function isset($key, $default = null): bool
    {
        $hash = $this->getHash($key);

        return \array_key_exists($hash, $this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $hash = $this->getHash($key);

        return $this->items[$hash]['value'] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value): void
    {
        $hash = $this->getHash($key);

        $this->items[$hash] = [
            'key' => $key,
            'value' => $value,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function unset($key): void
    {
        $hash = $this->getHash($key);

        unset($this->items[$hash]);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $str = '';

        foreach ($this->items as $hash => $option) {
            if ($this->getType($option['key'])->equals(OptionType::BOOL())) {
                $str .= sprintf(' %s', $option['value'] ? $option['key'] : '');
            } else {
                $str .= sprintf(
                    ' %s%s%s',
                    $option['key'],
                    $this->control['named'][$option['key']]['glue'] ?? ' ',
                    $this->escapeValue($option['value'])
                );
            }
        }

        return ltrim($str);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(bool $filterUnset = false): array
    {
        $items = [];
        foreach ($this->definition as $key => $definition) {
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
        return (string)$key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateIdentifier($key): bool
    {
        if (!parent::validateIdentifier($key)) {
            return false;
        }

        return (bool)preg_match('/^\-{1,2}[a-zA-Z](?:[a-zA-Z0-9_\-]|\|\-{1,2}[a-zA-Z])*(?<!\-)$/', (string)$key);
    }

    /**
     * Gets the option's aliases.
     *
     * @param string $key
     * @param bool   $include_key
     *
     * @return array
     */
    public function getAliases(string $key, bool $include_key = false): array
    {
        $aliases = $this->control['hashed'][$this->getHash($key)]['aliases'];

        return $include_key ? $aliases : array_values(array_diff($aliases, [$key]));
    }

    /**
     * Gets the option's hash.
     *
     * @param int|string $key
     *
     * @return string
     */
    public function getHash($key): string
    {
        $key = $this->ensureValidKey($key);

        return $this->control['named'][$key];
    }

    /**
     * Gets the option's type.
     *
     * @param string $key
     *
     * @return OptionType
     */
    public function getType(string $key): OptionType
    {
        return $this->control['hashed'][$this->getHash($key)]['type'];
    }

    /**
     * Gets the option's validation.
     *
     * @param string $key
     *
     * @return callable|null
     */
    public function getValidation(string $key): ?callable
    {
        return $this->control['hashed'][$this->getHash($key)]['validation'];
    }
}
