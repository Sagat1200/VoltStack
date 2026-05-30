<?php

declare(strict_types=1);

namespace Quantum\Config;

use Quantum\Config\Contracts\RepositoryInterface;

final class ConfigRepository implements RepositoryInterface
{
    public function __construct(
        protected array $items = [],
    ) {
    }

    public function has(string $key): bool
    {
        $marker = new \stdClass();

        return $this->get($key, $marker) !== $marker;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if ($key === '') {
            return $this->items;
        }

        $segments = explode('.', $key);
        $value = $this->items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $target = &$this->items;

        foreach ($segments as $index => $segment) {
            if ($index === array_key_last($segments)) {
                $target[$segment] = $value;
                return;
            }

            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                $target[$segment] = [];
            }

            $target = &$target[$segment];
        }
    }

    public function all(): array
    {
        return $this->items;
    }
}
