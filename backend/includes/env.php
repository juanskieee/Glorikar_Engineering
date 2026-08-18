<?php
/**
 * env.php — lightweight .env loader (no composer dependency required).
 * Loads variables into getenv() and a static array.
 */

class Env
{
    private static array $vars = [];

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            // Support optional inline comments after values
            $line = preg_replace('/\s+#.*$/', '', $line);
            if (!str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (strlen($value) >= 2 && $value[0] === '"' && $value[-1] === '"') {
                $value = substr($value, 1, -1);
            }
            self::$vars[$key] = $value;
            if (getenv($key) === false) {
                putenv($key . '=' . $value);
            }
            $_ENV[$key] = $value;
        }
    }

    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, self::$vars)) {
            return self::$vars[$key];
        }
        $env = getenv($key);
        return $env === false ? $default : $env;
    }
}

// Load .env from the backend root when this file is included from within backend/
$backendRoot = dirname(__DIR__);
if (is_file($backendRoot . '/.env')) {
    Env::load($backendRoot . '/.env');
} else {
    Env::load(__DIR__ . '/../.env');
}