<?php

declare(strict_types=1);

namespace Quantum\Validation;

use Quantum\Validation\Contracts\ValidatorInterface;

final class Validator implements ValidatorInterface
{
    public function validate(
        array $data,
        array $rules,
        array $messages = [],
        array $attributes = []
    ): array {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $fieldRules = $this->normalizeRules($fieldRules);
            $value = $data[$field] ?? null;
            $present = array_key_exists($field, $data);

            foreach ($fieldRules as $rule) {
                [$name, $argument] = $this->parseRule($rule);
                $ruleName = $this->normalizeRuleName($name);

                if ($name === 'nullable' && $value === null) {
                    break;
                }

                if ($name === 'required' && (!$present || $value === null || $value === '' || $value === [])) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                    );
                    continue;
                }

                if (!$present || $value === null) {
                    continue;
                }

                if ($name === 'string' && !is_string($value)) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                    );
                    continue;
                }

                if ($name === 'array' && !is_array($value)) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                    );
                    continue;
                }

                if ($name === 'numeric' && !is_numeric($value)) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                    );
                    continue;
                }

                if (($name === 'int' || $name === 'integer') && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                    );
                    continue;
                }

                if ($name === 'boolean' && !$this->passesBooleanRule($value)) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                    );
                    continue;
                }

                if ($name === 'confirmed' && !$this->passesConfirmedRule($field, $value, $data)) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                    );
                    continue;
                }

                if ($name === 'same' && $argument !== null && !$this->passesSameRule($value, $data[$argument] ?? null)) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                        ['other' => $attributes[$argument] ?? $argument],
                    );
                    continue;
                }

                if ($name === 'in' && $argument !== null && !$this->passesInRule($value, explode(',', $argument))) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                    );
                    continue;
                }

                if ($name === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                    );
                    continue;
                }

                if ($name === 'min' && $argument !== null && !$this->passesMinRule($value, (int) $argument)) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                        ['min' => (string) (int) $argument],
                    );
                    continue;
                }

                if ($name === 'max' && $argument !== null && !$this->passesMaxRule($value, (int) $argument)) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                        ['max' => (string) (int) $argument],
                    );
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

    protected function normalizeRuleName(string $rule): string
    {
        return $rule === 'int' ? 'integer' : $rule;
    }

    protected function messageFor(
        string $field,
        string $rule,
        string $normalizedRule,
        array $messages,
        array $attributes,
        array $replacements = []
    ): string {
        $template = $this->findMessageTemplate($field, $rule, $normalizedRule, $messages)
            ?? $this->defaultMessageTemplate($normalizedRule);

        $replacements = array_replace([
            'attribute' => $attributes[$field] ?? $field,
        ], $replacements);

        foreach ($replacements as $key => $value) {
            $template = str_replace(':' . $key, (string) $value, $template);
        }

        return $template;
    }

    protected function findMessageTemplate(
        string $field,
        string $rule,
        string $normalizedRule,
        array $messages
    ): ?string {
        $keys = [
            $field . '.' . $rule,
            $field . '.' . $normalizedRule,
            $rule,
            $normalizedRule,
        ];

        foreach (array_values(array_unique($keys)) as $key) {
            if (array_key_exists($key, $messages)) {
                return $messages[$key];
            }
        }

        return null;
    }

    protected function defaultMessageTemplate(string $rule): string
    {
        return match ($rule) {
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute field must be a string.',
            'array' => 'The :attribute field must be an array.',
            'boolean' => 'The :attribute field must be true or false.',
            'confirmed' => 'The :attribute field confirmation does not match.',
            'integer' => 'The :attribute field must be an integer.',
            'numeric' => 'The :attribute field must be a number.',
            'email' => 'The :attribute field must be a valid email address.',
            'same' => 'The :attribute field and :other must match.',
            'in' => 'The selected :attribute is invalid.',
            'min' => 'The :attribute field must be at least :min.',
            'max' => 'The :attribute field may not be greater than :max.',
            default => 'The :attribute field is invalid.',
        };
    }

    protected function passesBooleanRule(mixed $value): bool
    {
        return in_array($value, [true, false, 0, 1, '0', '1'], true);
    }

    protected function passesConfirmedRule(string $field, mixed $value, array $data): bool
    {
        $confirmationField = $field . '_confirmation';

        if (!array_key_exists($confirmationField, $data)) {
            return false;
        }

        return $this->passesSameRule($value, $data[$confirmationField]);
    }

    protected function passesSameRule(mixed $value, mixed $other): bool
    {
        return $value === $other;
    }

    protected function passesInRule(mixed $value, array $allowed): bool
    {
        $allowed = array_map(static fn(mixed $item): string => (string) $item, $allowed);

        return in_array((string) $value, $allowed, true);
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

    protected function passesMaxRule(mixed $value, int $maximum): bool
    {
        if (is_numeric($value)) {
            return (float) $value <= $maximum;
        }

        if (is_string($value)) {
            return mb_strlen($value) <= $maximum;
        }

        if (is_array($value)) {
            return count($value) <= $maximum;
        }

        return false;
    }
}
