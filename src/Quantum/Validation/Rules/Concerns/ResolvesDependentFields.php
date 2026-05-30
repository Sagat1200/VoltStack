<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules\Concerns;

use Quantum\Validation\ValidationRuleContext;

trait ResolvesDependentFields
{
    protected function resolveDependentField(ValidationRuleContext $context, string $dependentField): string
    {
        $pattern = $context->pattern();
        $field = $context->field();

        if (!str_contains($pattern, '*') || !str_contains($dependentField, '*')) {
            return $dependentField;
        }

        $wildcards = [];
        $patternSegments = explode('.', $pattern);
        $fieldSegments = explode('.', $field);

        foreach ($patternSegments as $index => $segment) {
            if ($segment === '*' && array_key_exists($index, $fieldSegments)) {
                $wildcards[] = $fieldSegments[$index];
            }
        }

        $dependentSegments = explode('.', $dependentField);

        foreach ($dependentSegments as $index => $segment) {
            if ($segment !== '*') {
                continue;
            }

            $replacement = array_shift($wildcards);

            if ($replacement === null) {
                break;
            }

            $dependentSegments[$index] = $replacement;
        }

        return implode('.', $dependentSegments);
    }

    /**
     * @param array<int, string> $dependentFields
     * @return array<int, string>
     */
    protected function resolveDependentFields(ValidationRuleContext $context, array $dependentFields): array
    {
        return array_map(
            fn(string $dependentField): string => $this->resolveDependentField($context, $dependentField),
            $dependentFields,
        );
    }

    protected function getValueByPath(array $data, string $path): mixed
    {
        $segments = explode('.', $path);
        $current = $data;

        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    protected function pathExists(array $data, string $path): bool
    {
        $segments = explode('.', $path);
        $current = $data;

        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return false;
            }

            $current = $current[$segment];
        }

        return true;
    }

    protected function attributeLabel(ValidationRuleContext $context, string $field): string
    {
        $attributes = $context->attributes();

        return $attributes[$field] ?? $field;
    }

    /**
     * @param array<int, string> $fields
     */
    protected function attributeLabels(ValidationRuleContext $context, array $fields): string
    {
        return implode(', ', array_map(
            fn(string $field): string => $this->attributeLabel($context, $field),
            $fields,
        ));
    }
}
