<?php

namespace NorseBlue\Flow\Commands\Options;

use NorseBlue\Flow\Collections\BaseCollection;
use NorseBlue\Flow\Exceptions\UnsupportedOptionTypeException;

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
            $type = \is_array($option) ? $option['type'] : $option;
            if (!OptionType::isValid($type)) {
                throw new UnsupportedOptionTypeException(
                    sprintf(
                        'The type for the option \'%s\' is not supported.',
                        \is_string($key) ? $key : $type
                    )
                );
            }

            $aliases = array_unique(explode('|', $key), SORT_REGULAR);
            if (($json = json_encode($aliases)) === false) {
                throw new \RuntimeException('An unknown error occurred while trying to generate the aliases hash.'); // @codeCoverageIgnore
            }
            $hash = sha1((string)$json);

            $compiled['hashed'][$hash] = $aliases;
            foreach ($aliases as $alias) {
                $compiled['named'][$alias] = [
                    'aliases' => array_values(array_diff($aliases, [$alias])),
                    'glue' => $option['glue'] ?? ' ',
                    'hash' => $hash,
                    'type' => new OptionType($type),
                    'validation' => $option['validation'] ?? function ($value, $type): bool {
                        $callback = sprintf('is_%s', $type);
                        if (!is_callable($callback)) {
                            throw new \RuntimeException(sprintf('Invalid callback \'%s\'.', $callback)); // @codeCoverageIgnore
                        }
                        return $callback($value);
                    },
                ];
            }
        }

        return $compiled;
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
                    $option['value']
                );
            }
        }

        return ltrim($str);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $items = [];
        foreach ($this->definition as $key => $definition) {
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
     * Gets the option's aliases.
     *
     * @param string $key
     * @param bool   $include_key
     *
     * @return array
     */
    public function getAliases(string $key, bool $include_key = false): array
    {
        $key = $this->ensureValidKey($key);

        return array_merge($include_key ? [$key] : [], $this->control['named'][$key]['aliases']);
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

        return $this->control['named'][$key]['hash'];
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
        $key = $this->ensureValidKey($key);

        return $this->control['named'][$key]['type'];
    }
}
