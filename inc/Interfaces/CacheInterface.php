<?php
/**
 * CacheInterface - Simple key-value cache abstraction.
 *
 * @package Counto\Interfaces
 * @copyright  2026 Counto Analytics
 * @version 1.4.1
 * @license    GPL-3.0-or-later
 */

declare(strict_types=1);

namespace Counto\Interfaces;

interface CacheInterface
{
    /**
     * Get a cached value by key.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Set a cached value.
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $ttl Time-to-live in seconds (0 = forever)
     * @return bool
     */
    public function set(string $key, $value, int $ttl = 0): bool;

    /**
     * Check if a key exists in the cache.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Delete a cached value.
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * Clear all cached values.
     *
     * @return bool
     */
    public function clear(): bool;
}