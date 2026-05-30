<?php

declare(strict_types=1);

namespace Quantum\Http;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use LogicException;
use Traversable;

final class ValidatedInput implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    public function __construct(
        protected array $data,
    ) {}

    public function all(): array
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return $this->all();
    }

    public function merge(array|self $items): self
    {
        return new self(array_replace_recursive(
            $this->data,
            $items instanceof self ? $items->all() : $items,
        ));
    }

    public function collect(): ArrayIterator
    {
        return new ArrayIterator($this->all());
    }

    public function keys(): array
    {
        return array_keys($this->data);
    }

    public function values(): array
    {
        return array_values($this->data);
    }

    public function contains(mixed $needle): bool
    {
        if (is_callable($needle)) {
            foreach ($this->data as $key => $value) {
                if ($needle($value, $key) === true) {
                    return true;
                }
            }

            return false;
        }

        return in_array($needle, $this->data, true);
    }

    public function pluck(string $value, ?string $key = null): array
    {
        $result = [];

        foreach ($this->data as $item) {
            if (!is_array($item) || !$this->hasArrayValue($item, $value)) {
                continue;
            }

            $pluckedValue = $this->getArrayValue($item, $value);

            if ($key === null || !$this->hasArrayValue($item, $key)) {
                $result[] = $pluckedValue;
                continue;
            }

            $pluckedKey = $this->getArrayValue($item, $key);

            if (is_int($pluckedKey) || is_string($pluckedKey)) {
                $result[$pluckedKey] = $pluckedValue;
                continue;
            }

            $result[] = $pluckedValue;
        }

        return $result;
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        $accumulator = $initial;

        foreach ($this->data as $key => $value) {
            $accumulator = $callback($accumulator, $value, $key);
        }

        return $accumulator;
    }

    public function every(callable $callback): bool
    {
        foreach ($this->data as $key => $value) {
            if ($callback($value, $key) !== true) {
                return false;
            }
        }

        return true;
    }

    public function some(callable $callback): bool
    {
        foreach ($this->data as $key => $value) {
            if ($callback($value, $key) === true) {
                return true;
            }
        }

        return false;
    }

    public function partition(callable $callback): array
    {
        $matches = [];
        $rejects = [];

        foreach ($this->data as $key => $value) {
            if ($callback($value, $key) === true) {
                $matches[$key] = $value;
                continue;
            }

            $rejects[$key] = $value;
        }

        return [new self($matches), new self($rejects)];
    }

    public function sort(?callable $callback = null): self
    {
        $result = $this->data;

        if ($callback === null) {
            asort($result);
        } else {
            uasort($result, $callback);
        }

        return new self($result);
    }

    public function sortByKeys(?callable $callback = null): self
    {
        $result = $this->data;

        if ($callback === null) {
            ksort($result);
        } else {
            uksort($result, $callback);
        }

        return new self($result);
    }

    public function reverse(): self
    {
        return new self(array_reverse($this->data, true));
    }

    public function slice(int $offset, ?int $length = null): self
    {
        return new self(array_slice($this->data, $offset, $length, true));
    }

    public function map(callable $callback): self
    {
        $result = [];

        foreach ($this->data as $key => $value) {
            $result[$key] = $callback($value, $key);
        }

        return new self($result);
    }

    public function filter(?callable $callback = null): self
    {
        $result = [];

        foreach ($this->data as $key => $value) {
            $keep = $callback !== null
                ? (bool) $callback($value, $key)
                : $this->isFilledValue($value);

            if ($keep) {
                $result[$key] = $value;
            }
        }

        return new self($result);
    }

    public function first(?callable $callback = null, mixed $default = null): mixed
    {
        foreach ($this->data as $key => $value) {
            if ($callback === null || $callback($value, $key) === true) {
                return $value;
            }
        }

        return $default;
    }

    public function get(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->all();
        }

        return $this->getArrayValue($this->data, $key, $default);
    }

    public function only(array|string $keys): array
    {
        $keys = $this->normalizeKeys($keys);
        $result = [];

        foreach ($keys as $key) {
            if (!$this->hasArrayValue($this->data, $key)) {
                continue;
            }

            if (array_key_exists($key, $this->data)) {
                $result[$key] = $this->data[$key];
                continue;
            }

            $this->setArrayValue($result, $key, $this->getArrayValue($this->data, $key));
        }

        return $result;
    }

    public function except(array|string $keys): array
    {
        $keys = $this->normalizeKeys($keys);
        $result = $this->data;

        foreach ($keys as $key) {
            $this->forgetArrayValue($result, $key);
        }

        return $result;
    }

    public function has(array|string $keys): bool
    {
        foreach ($this->normalizeKeys($keys) as $key) {
            if (!$this->hasArrayValue($this->data, $key)) {
                return false;
            }
        }

        return true;
    }

    public function missing(array|string $keys): bool
    {
        return !$this->has($keys);
    }

    public function filled(array|string $keys): bool
    {
        foreach ($this->normalizeKeys($keys) as $key) {
            if (!$this->has($key) || !$this->isFilledValue($this->get($key))) {
                return false;
            }
        }

        return true;
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) && $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!is_string($offset)) {
            return null;
        }

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('ValidatedInput is read-only.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('ValidatedInput is read-only.');
    }

    public function getIterator(): Traversable
    {
        return $this->collect();
    }

    public function jsonSerialize(): array
    {
        return $this->all();
    }

    protected function normalizeKeys(array|string $keys): array
    {
        return is_array($keys) ? $keys : [$keys];
    }

    protected function isFilledValue(mixed $value): bool
    {
        return !($value === null || $value === '' || $value === []);
    }

    protected function getArrayValue(array $data, string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        $segments = explode('.', $key);
        $current = $data;

        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $default;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    protected function hasArrayValue(array $data, string $key): bool
    {
        if (array_key_exists($key, $data)) {
            return true;
        }

        $segments = explode('.', $key);
        $current = $data;

        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return false;
            }

            $current = $current[$segment];
        }

        return true;
    }

    protected function setArrayValue(array &$data, string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $current = &$data;

        foreach ($segments as $segment) {
            if (!isset($current[$segment]) || !is_array($current[$segment])) {
                $current[$segment] = [];
            }

            $current = &$current[$segment];
        }

        $current = $value;
    }

    protected function forgetArrayValue(array &$data, string $key): void
    {
        if (array_key_exists($key, $data)) {
            unset($data[$key]);

            return;
        }

        $this->forgetArrayValueRecursive($data, explode('.', $key));
    }

    protected function forgetArrayValueRecursive(array &$data, array $segments): bool
    {
        $segment = array_shift($segments);

        if ($segment === null || !array_key_exists($segment, $data)) {
            return false;
        }

        if ($segments === []) {
            unset($data[$segment]);

            return $data === [];
        }

        if (!is_array($data[$segment])) {
            return false;
        }

        $shouldForgetChild = $this->forgetArrayValueRecursive($data[$segment], $segments);

        if ($shouldForgetChild) {
            unset($data[$segment]);
        }

        return $data === [];
    }
}