<?php

declare(strict_types=1);

namespace Quantum\Validation;

use Quantum\Validation\Contracts\ValidatorInterface;

final class Validator implements ValidatorInterface
{
    public function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $fieldRules = $this->normalizeRules($fieldRules);
            $value = $data[$field] ?? null;
            $present = array_key_exists($field, $data);

            foreach ($fieldRules as $rule) {
                [$name, $argument] = $this->parseRule($rule);

                if ($name === 'required' && (!$present || $value === null || $value === '' || $value === [])) {
                    $errors[$field][] = sprintf('The %s field is required.', $field);
                    continue;
                }

                if (!$present || $value === null) {
                    continue;
                }

                if ($name === 'string' && !is_string($value)) {
                    $errors[$field][] = sprintf('The %s field must be a string.', $field);
                    continue;
                }

                if (($name === 'int' || $name === 'integer') && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $errors[$field][] = sprintf('The %s field must be an integer.', $field);
                    continue;
                }

                if ($name === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                    $errors[$field][] = sprintf('The %s field must be a valid email address.', $field);
                    continue;
                }

                if ($name === 'min' && $argument !== null && !$this->passesMinRule($value, (int) $argument)) {
                    $errors[$field][] = sprintf('The %s field must be at least %d.', $field, (int) $argument);
                }
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $data;
    }

    protected function normalizeRules(array|string $rules): array
    {
        if (is_string($rules)) {
            return array_values(array_filter(explode('|', $rules), static fn(string $rule): bool => $rule !== ''));
        }

        return $rules;
    }

    protected function parseRule(string $rule): array
    {
        if (!str_contains($rule, ':')) {
            return [$rule, null];
        }

        [$name, $argument] = explode(':', $rule, 2);

        return [$name, $argument];
    }

    protected function passesMinRule(mixed $value, int $minimum): bool
    {
        if (is_numeric($value)) {
            return (float) $value >= $minimum;
        }

        if (is_string($value)) {
            return mb_strlen($value) >= $minimum;
        }

        if (is_array($value)) {
            return count($value) >= $minimum;
        }

        return false;
    }
}
