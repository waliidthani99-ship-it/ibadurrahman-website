<?php
/**
 * Tiny .env loader (no Composer dependency).
 * Real environment variables take precedence over values in the .env file.
 */

if (!function_exists('load_env')) {
    function load_env($path = null) {
        $path = $path ?: dirname(__DIR__) . '/.env';

        static $loaded = [];
        if (isset($loaded[$path])) return;
        $loaded[$path] = true;

        if (!is_readable($path)) return;

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) return;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') === false) continue;

            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            if ($key === '' || getenv($key) !== false) continue;

            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false || $value === '') return $default;
        return $value;
    }
}
