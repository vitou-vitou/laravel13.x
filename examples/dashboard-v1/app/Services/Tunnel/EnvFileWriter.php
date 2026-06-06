<?php

namespace App\Services\Tunnel;

use RuntimeException;

class EnvFileWriter
{
    public function __construct(
        private readonly string $path,
    ) {}

    public function path(): string
    {
        return $this->path;
    }

    public function set(string $key, string $value): void
    {
        if (! is_file($this->path)) {
            throw new RuntimeException("Env file not found: {$this->path}");
        }

        $contents = file_get_contents($this->path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read env file: {$this->path}");
        }

        $line = "{$key}={$value}";
        $pattern = '/^'.preg_quote($key, '/').'=.*/m';

        if (preg_match($pattern, $contents)) {
            $contents = preg_replace($pattern, $line, $contents) ?? $contents;
        } else {
            $contents = rtrim($contents, "\r\n").PHP_EOL.$line.PHP_EOL;
        }

        $handle = fopen($this->path, 'c+');

        if ($handle === false) {
            throw new RuntimeException("Unable to open env file: {$this->path}");
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                throw new RuntimeException("Unable to lock env file: {$this->path}");
            }

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, $contents);
            fflush($handle);
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }
    }
}
