<?php

declare(strict_types=1);

namespace Quantum\Config;

final class ConfigLoader
{
    public function loadFromPath(string $configPath): array
    {
        if (!is_dir($configPath)) {
            return [];
        }

        $items = [];
        $pattern = rtrim($configPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php';
        $files = glob($pattern) ?: [];

        sort($files);

        foreach ($files as $file) {
            $key = pathinfo($file, PATHINFO_FILENAME);
            $loaded = require $file;
            $items[$key] = is_array($loaded) ? $loaded : [];
        }

        return $items;
    }
}
