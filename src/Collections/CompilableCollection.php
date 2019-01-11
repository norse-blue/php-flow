<?php

namespace NorseBlue\Flow\Collections;

/**
 * Interface CompilableCollection
 *
 * @package NorseBlue\Flow\Collections
 */
interface CompilableCollection
{
    /**
     * Checks if the item exists in the compiled control structure.
     *
     * @param int|string $key
     *
     * @return bool
     */
    public function exists($key): bool;

    /**
     * Checks if the argument is set (the argument has an actual value).
     *
     * @param int|string $key
     *
     * @return bool
     */
    public function isset($key): bool;

    /**
     * Gets the item's value.
     *
     * @param int|string $key
     * @param mixed      $default
     *
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Sets the item's value.
     *
     * @param int|string $key
     * @param mixed      $value
     *
     * @return void
     */
    public function set($key, $value): void;

    /**
     * Unsets the item.
     *
     * @param int|string $key
     */
    public function unset($key): void;

    /**
     * Returns the collection items as a formatted string
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Gets the collection definition.
     *
     * @return array
     */
    public function getDefinition(): array;

    /**
     * Gets the collection control structure.
     *
     * @return array
     */
    public function getControl(): array;

    /**
     * Get the collection's raw internal items array.
     *
     * @return array
     */
    public function getItems(): array;

    /**
     * Gets the collection's processed items array.
     *
     * @return array
     */
    public function toArray(): array;
}
