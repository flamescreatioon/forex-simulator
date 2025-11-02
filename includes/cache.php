<?php
/**
 * Simple caching helper for the Forex Trading Platform
 * Provides basic caching functionality without external dependencies
 */

class SimpleCache {
    private $cache_dir;
    private $default_ttl = 300; // 5 minutes default

    public function __construct($cache_dir = null) {
        $this->cache_dir = $cache_dir ?? sys_get_temp_dir() . '/forex_cache';
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cache_dir)) {
            @mkdir($this->cache_dir, 0755, true);
        }
    }

    /**
     * Get a value from cache
     */
    public function get($key, $default = null) {
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return $default;
        }

        $data = @file_get_contents($file);
        if ($data === false) {
            return $default;
        }

        $cached = @unserialize($data);
        if ($cached === false) {
            return $default;
        }

        // Check if expired
        if ($cached['expires'] < time()) {
            @unlink($file);
            return $default;
        }

        return $cached['value'];
    }

    /**
     * Set a value in cache
     */
    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?? $this->default_ttl;
        $file = $this->getCacheFile($key);

        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];

        return @file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }

    /**
     * Delete a value from cache
     */
    public function delete($key) {
        $file = $this->getCacheFile($key);
        return @unlink($file);
    }

    /**
     * Check if a key exists and is not expired
     */
    public function has($key) {
        return $this->get($key) !== null;
    }

    /**
     * Clear all cache
     */
    public function clear() {
        if (!is_dir($this->cache_dir)) {
            return true;
        }

        $files = glob($this->cache_dir . '/*.cache');
        foreach ($files as $file) {
            @unlink($file);
        }
        return true;
    }

    /**
     * Clean expired cache entries
     */
    public function cleanup() {
        if (!is_dir($this->cache_dir)) {
            return true;
        }

        $files = glob($this->cache_dir . '/*.cache');
        $cleaned = 0;

        foreach ($files as $file) {
            $data = @file_get_contents($file);
            if ($data === false) continue;

            $cached = @unserialize($data);
            if ($cached === false) continue;

            if ($cached['expires'] < time()) {
                @unlink($file);
                $cleaned++;
            }
        }

        return $cleaned;
    }

    /**
     * Get cache file path for a key
     */
    private function getCacheFile($key) {
        $hash = md5($key);
        return $this->cache_dir . '/' . $hash . '.cache';
    }

    /**
     * Remember a value - get from cache or execute callback and cache result
     */
    public function remember($key, $ttl, callable $callback) {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
}

// Global cache instance
global $cache;
$cache = new SimpleCache();

// Cleanup old cache entries occasionally (5% chance)
if (mt_rand(1, 100) <= 5) {
    $cache->cleanup();
}
