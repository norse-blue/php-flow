<?php

namespace NorseBlue\Flow\Collections;

/**
 * Class BaseCollection
 *
 * @package NorseBlue\Flow\Collections
 */
abstract class BaseCollection implements CompilableCollection
{
    /**
     * @var array The collection definition.
     */
    protected $definition;

    /**
     * @var array The compiled control structure.
     */
    protected $control;

    /**
     * @var array The collection's actual items.
     */
    protected $items;

    /**
     * BaseCollection constructor.
     *
     * @param array      $definition
     * @param array|null $items
     */
    public function __construct(array $definition, array $items = null)
    {
        $this->definition = $definition;
        $this->control = $this->compile($this->definition);
        $this->load($items ?? []);
    }

    /**
     * Compiles the given definition into a control structure.
     *
     * @param array $definition
     *
     * @return array
     */
    abstract protected function compile(array $definition): array;

    /**
     * {@inheritdoc}
     */
    public function exists($key): bool
    {
        $key = $this->parseKey($key);

        return \array_key_exists($key, $this->control['named']);
    }

    /**
     * {@inheritdoc}
     */
    public function isset($key): bool
    {
        $key = $this->ensureValidKey($key);

        return \array_key_exists($key, $this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $key = $this->ensureValidKey($key);

        return $this->items[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value): void
    {
        $key = $this->ensureValidKey($key);

        $this->items[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function unset($key): void
    {
        $key = $this->ensureValidKey($key);

        unset($this->items[$key]);
    }

    /**
     * {@inheritdoc}
     */
    abstract public function __toString(): string;

    /**
     * {@inheritdoc}
     */
    abstract public function toArray(): array;

    /**
     * @param array $items
     *
     * @return void
     */
    protected function load(array $items): void
    {
        $this->items = [];
        foreach ($items ?? [] as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition(): array
    {
        return $this->definition;
    }

    /**
     * {@inheritdoc}
     */
    public function getControl(): array
    {
        return $this->control;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Parses a key to the specifications of the collection.
     *
     * @param int|string $key
     *
     * @return string
     */
    abstract protected function parseKey($key): string;

    /**
     * Ensures a valid key was given.
     *
     * @param int|string $key
     *
     * @return string
     */
    protected function ensureValidKey($key): string
    {
        $key = $this->parseKey($key);

        if (!$this->exists($key)) {
            throw new \InvalidArgumentException(sprintf('The key \'%s\' is invalid.', $key));
        }

        return $key;
    }

    /**
     * Escapes the item's value when needed.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function escapeValue($value)
    {
        if ($value === null) {
            return $value;
        }

        $value = trim($value);
        return strpos($value, ' ') === false ? $value : sprintf('"%s"', $value);
    }
}
