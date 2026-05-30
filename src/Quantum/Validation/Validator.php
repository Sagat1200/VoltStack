<?php

declare(strict_types=1);

namespace Quantum\Validation;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\Contracts\ValidatorInterface;

final class Validator implements ValidatorInterface
{
    protected array $afterCallbacks = [];

    protected bool $stopOnFirstFailure = false;

    public function after(callable $callback): static
    {
        $this->afterCallbacks[] = $callback;

        return $this;
    }

    public function stopOnFirstFailure(bool $value = true): static
    {
        $this->stopOnFirstFailure = $value;

        return $this;
    }

    public function validate(
        array $data,
        array $rules,
        array $messages = [],
        array $attributes = []
    ): array {
        $afterCallbacks = $this->afterCallbacks;
        $this->afterCallbacks = [];
        $stopOnFirstFailure = $this->stopOnFirstFailure;
        $this->stopOnFirstFailure = false;
        $errors = [];
        $shouldStopValidation = false;

        try {
            foreach ($rules as $field => $fieldRules) {
                $fieldRules = $this->normalizeRules($fieldRules);
                $bail = in_array('bail', $fieldRules, true);
                $distinctValues = [];

                foreach ($this->resolveFieldTargets($data, $field) as $target) {
                    $value = $target['value'];
                    $present = $target['present'];
                    $concreteField = $target['field'];

                    $fail = function (
                        string $name,
                        string $ruleName,
                        array $replacements = [],
                        ?string $message = null,
                    ) use (
                        &$errors,
                        &$shouldStopValidation,
                        $concreteField,
                        $field,
                        $messages,
                        $attributes,
                        $bail,
                        $stopOnFirstFailure,
                    ): bool {
                        $errors[$concreteField][] = $message !== null
                            ? $this->renderMessageTemplate($message, $concreteField, $field, $attributes, $replacements)
                            : $this->messageFor(
                                $concreteField,
                                $field,
                                $name,
                                $ruleName,
                                $messages,
                                $attributes,
                                $replacements,
                            );

                        if ($stopOnFirstFailure) {
                            $shouldStopValidation = true;
                        }

                        return $bail || $stopOnFirstFailure;
                    };

                    foreach ($fieldRules as $rule) {
                        if ($rule instanceof RuleInterface) {
                            $ruleName = $this->normalizeRuleName($rule->name());
                            $context = new ValidationRuleContext(
                                $field,
                                $concreteField,
                                $value,
                                $present,
                                $data,
                                $ruleName,
                                function (?string $message = null, array $replacements = []) use ($fail, $rule, $ruleName): bool {
                                    return $fail($rule->name(), $ruleName, $replacements, $message);
                                },
                            );

                            $rule->validate($context);

                            if ($context->shouldBreak()) {
                                break;
                            }

                            if ($context->failed()) {
                                continue;
                            }

                            continue;
                        }

                        [$name, $argument] = $this->parseRule($rule);
                        $ruleName = $this->normalizeRuleName($name);

                        if ($name === 'bail') {
                            continue;
                        }

                        if ($name === 'sometimes' && !$present) {
                            break;
                        }

                        if ($name === 'present' && !$present) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if (
                            $name === 'required_with'
                            && $argument !== null
                            && $this->shouldRequireWith($field, $concreteField, $argument, $data)
                            && $this->isEmptyValue($value, $present)
                        ) {
                            $dependentFields = $this->resolvedDependentFields(
                                $field,
                                $concreteField,
                                $this->parseDependentFieldsArgument($argument),
                            );

                            if ($fail($name, $ruleName, [
                                'values' => $this->dependentFieldLabels($dependentFields, $attributes),
                            ])) {
                                break;
                            }
                            continue;
                        }

                        if (
                            $name === 'required_without'
                            && $argument !== null
                            && $this->shouldRequireWithout($field, $concreteField, $argument, $data)
                            && $this->isEmptyValue($value, $present)
                        ) {
                            $dependentFields = $this->resolvedDependentFields(
                                $field,
                                $concreteField,
                                $this->parseDependentFieldsArgument($argument),
                            );

                            if ($fail($name, $ruleName, [
                                'values' => $this->dependentFieldLabels($dependentFields, $attributes),
                            ])) {
                                break;
                            }
                            continue;
                        }

                        if (
                            $name === 'required_if'
                            && $argument !== null
                            && $this->shouldRequireIf($field, $concreteField, $argument, $data)
                            && $this->isEmptyValue($value, $present)
                        ) {
                            [$otherField, $expectedValues] = $this->parseDependentRuleArgument($argument);
                            $resolvedOtherField = $this->resolveDependentField($field, $concreteField, $otherField);

                            if ($fail($name, $ruleName, [
                                'other' => $attributes[$resolvedOtherField] ?? $attributes[$otherField] ?? $resolvedOtherField,
                                'value' => implode(', ', $expectedValues),
                            ])) {
                                break;
                            }
                            continue;
                        }

                        if (
                            $name === 'required_unless'
                            && $argument !== null
                            && $this->shouldRequireUnless($field, $concreteField, $argument, $data)
                            && $this->isEmptyValue($value, $present)
                        ) {
                            [$otherField, $expectedValues] = $this->parseDependentRuleArgument($argument);
                            $resolvedOtherField = $this->resolveDependentField($field, $concreteField, $otherField);

                            if ($fail($name, $ruleName, [
                                'other' => $attributes[$resolvedOtherField] ?? $attributes[$otherField] ?? $resolvedOtherField,
                                'values' => implode(', ', $expectedValues),
                            ])) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'prohibited' && !$this->isEmptyValue($value, $present)) {
                            $fail($name, $ruleName);
                            break;
                        }

                        if (
                            $name === 'accepted_if'
                            && $argument !== null
                            && $this->shouldRequireIf($field, $concreteField, $argument, $data)
                            && !$this->passesAcceptedRule($value)
                        ) {
                            [$otherField, $expectedValues] = $this->parseDependentRuleArgument($argument);
                            $resolvedOtherField = $this->resolveDependentField($field, $concreteField, $otherField);

                            if ($fail($name, $ruleName, [
                                'other' => $attributes[$resolvedOtherField] ?? $attributes[$otherField] ?? $resolvedOtherField,
                                'value' => implode(', ', $expectedValues),
                            ])) {
                                break;
                            }
                            continue;
                        }

                        if (
                            $name === 'prohibited_if'
                            && $argument !== null
                            && $this->shouldRequireIf($field, $concreteField, $argument, $data)
                            && !$this->isEmptyValue($value, $present)
                        ) {
                            [$otherField, $expectedValues] = $this->parseDependentRuleArgument($argument);
                            $resolvedOtherField = $this->resolveDependentField($field, $concreteField, $otherField);

                            $fail($name, $ruleName, [
                                'other' => $attributes[$resolvedOtherField] ?? $attributes[$otherField] ?? $resolvedOtherField,
                                'value' => implode(', ', $expectedValues),
                            ]);
                            break;
                        }

                        if ($name === 'declined' && $present && !$this->passesDeclinedRule($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if (
                            $name === 'declined_if'
                            && $argument !== null
                            && $this->shouldRequireIf($field, $concreteField, $argument, $data)
                            && !$this->passesDeclinedRule($value)
                        ) {
                            [$otherField, $expectedValues] = $this->parseDependentRuleArgument($argument);
                            $resolvedOtherField = $this->resolveDependentField($field, $concreteField, $otherField);

                            if ($fail($name, $ruleName, [
                                'other' => $attributes[$resolvedOtherField] ?? $attributes[$otherField] ?? $resolvedOtherField,
                                'value' => implode(', ', $expectedValues),
                            ])) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'nullable' && $value === null) {
                            break;
                        }

                        if ($name === 'required' && (!$present || $value === null || $value === '' || $value === [])) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if (!$present || $value === null) {
                            continue;
                        }

                        if ($name === 'string' && !is_string($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'array' && !is_array($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'accepted' && !$this->passesAcceptedRule($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'numeric' && !is_numeric($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'alpha_dash' && !$this->passesAlphaDashRule($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if (($name === 'int' || $name === 'integer') && filter_var($value, FILTER_VALIDATE_INT) === false) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'boolean' && !$this->passesBooleanRule($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'confirmed' && !$this->passesConfirmedRule($concreteField, $value, $data)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'same' && $argument !== null) {
                            $otherField = $this->resolveDependentField($field, $concreteField, $argument);

                            if ($this->passesSameRule($value, $this->getValueByPath($data, $otherField))) {
                                continue;
                            }

                            if ($fail($name, $ruleName, [
                                'other' => $attributes[$otherField] ?? $attributes[$argument] ?? $otherField,
                            ])) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'in' && $argument !== null && !$this->passesInRule($value, explode(',', $argument))) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'url' && filter_var($value, FILTER_VALIDATE_URL) === false) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'date' && !$this->passesDateRule($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'ascii' && !$this->passesAsciiRule($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'uuid' && !$this->passesUuidRule($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'ip' && !$this->passesIpRule($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'ipv4' && !$this->passesIpv4Rule($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'ipv6' && !$this->passesIpv6Rule($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'json' && !$this->passesJsonRule($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'regex' && $argument !== null && !$this->passesRegexRule($value, $argument)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'not_regex' && $argument !== null && !$this->passesNotRegexRule($value, $argument)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'starts_with' && $argument !== null && !$this->passesStartsWithRule($value, $this->parseListRuleArgument($argument))) {
                            if ($fail($name, $ruleName, [
                                'values' => $argument,
                            ])) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'ends_with' && $argument !== null && !$this->passesEndsWithRule($value, $this->parseListRuleArgument($argument))) {
                            if ($fail($name, $ruleName, [
                                'values' => $argument,
                            ])) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'lowercase' && !$this->passesLowercaseRule($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'uppercase' && !$this->passesUppercaseRule($value)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'distinct' && !$this->passesDistinctRule($value, $distinctValues)) {
                            if ($fail($name, $ruleName)) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'size' && $argument !== null && !$this->passesSizeRule($value, (int) $argument)) {
                            if ($fail($name, $ruleName, [
                                'size' => (string) (int) $argument,
                            ])) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'between' && $argument !== null) {
                            [$minimum, $maximum] = $this->parseRangeRuleArgument($argument);

                            if ($minimum !== null && $maximum !== null && !$this->passesBetweenRule($value, $minimum, $maximum)) {
                                if ($fail($name, $ruleName, [
                                    'min' => (string) $minimum,
                                    'max' => (string) $maximum,
                                ])) {
                                    break;
                                }
                                continue;
                            }
                        }

                        if ($name === 'digits' && $argument !== null && !$this->passesDigitsRule($value, (int) $argument)) {
                            if ($fail($name, $ruleName, [
                                'digits' => (string) (int) $argument,
                            ])) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'min' && $argument !== null && !$this->passesMinRule($value, (int) $argument)) {
                            if ($fail($name, $ruleName, [
                                'min' => (string) (int) $argument,
                            ])) {
                                break;
                            }
                            continue;
                        }

                        if ($name === 'max' && $argument !== null && !$this->passesMaxRule($value, (int) $argument)) {
                            if ($fail($name, $ruleName, [
                                'max' => (string) (int) $argument,
                            ])) {
                                break;
                            }
                        }
                    }

                    if ($shouldStopValidation) {
                        break;
                    }
                }

                if ($shouldStopValidation) {
                    break;
                }
            }

            $this->runAfterCallbacks($afterCallbacks, $data, $errors);
        } finally {
            $this->afterCallbacks = [];
            $this->stopOnFirstFailure = false;
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $data;
    }

    protected function runAfterCallbacks(array $callbacks, array $data, array &$errors): void
    {
        $context = new ValidationCallbackContext(
            $data,
            static function (string $field, string $message) use (&$errors): void {
                $errors[$field][] = $message;
            },
            static function () use (&$errors): array {
                return $errors;
            },
        );

        foreach ($callbacks as $callback) {
            $callback($context);
        }
    }

    protected function normalizeRules(array|string|RuleInterface $rules): array
    {
        if ($rules instanceof RuleInterface) {
            return [$rules];
        }

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
        string $pattern,
        string $rule,
        string $normalizedRule,
        array $messages,
        array $attributes,
        array $replacements = []
    ): string {
        $template = $this->findMessageTemplate($field, $pattern, $rule, $normalizedRule, $messages)
            ?? $this->defaultMessageTemplate($normalizedRule);

        return $this->renderMessageTemplate($template, $field, $pattern, $attributes, $replacements);
    }

    protected function renderMessageTemplate(
        string $template,
        string $field,
        string $pattern,
        array $attributes,
        array $replacements = []
    ): string {
        $replacements = array_replace([
            'attribute' => $attributes[$field] ?? $attributes[$pattern] ?? $field,
        ], $replacements);

        foreach ($replacements as $key => $value) {
            $template = str_replace(':' . $key, (string) $value, $template);
        }

        return $template;
    }

    protected function findMessageTemplate(
        string $field,
        string $pattern,
        string $rule,
        string $normalizedRule,
        array $messages
    ): ?string {
        $keys = [
            $field . '.' . $rule,
            $field . '.' . $normalizedRule,
            $pattern . '.' . $rule,
            $pattern . '.' . $normalizedRule,
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
            'required_with' => 'The :attribute field is required when any of :values are present.',
            'required_without' => 'The :attribute field is required when any of :values are missing.',
            'required_if' => 'The :attribute field is required when :other is :value.',
            'required_unless' => 'The :attribute field is required unless :other is in :values.',
            'present' => 'The :attribute field must be present.',
            'prohibited' => 'The :attribute field is prohibited.',
            'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
            'accepted' => 'The :attribute field must be accepted.',
            'accepted_if' => 'The :attribute field must be accepted when :other is :value.',
            'declined' => 'The :attribute field must be declined.',
            'declined_if' => 'The :attribute field must be declined when :other is :value.',
            'string' => 'The :attribute field must be a string.',
            'alpha_dash' => 'The :attribute field may only contain letters, numbers, dashes, and underscores.',
            'array' => 'The :attribute field must be an array.',
            'boolean' => 'The :attribute field must be true or false.',
            'confirmed' => 'The :attribute field confirmation does not match.',
            'integer' => 'The :attribute field must be an integer.',
            'numeric' => 'The :attribute field must be a number.',
            'url' => 'The :attribute field must be a valid URL.',
            'date' => 'The :attribute field must be a valid date.',
            'email' => 'The :attribute field must be a valid email address.',
            'ascii' => 'The :attribute field must only contain ASCII characters.',
            'uuid' => 'The :attribute field must be a valid UUID.',
            'ip' => 'The :attribute field must be a valid IP address.',
            'ipv4' => 'The :attribute field must be a valid IPv4 address.',
            'ipv6' => 'The :attribute field must be a valid IPv6 address.',
            'json' => 'The :attribute field must be a valid JSON string.',
            'regex' => 'The :attribute field format is invalid.',
            'not_regex' => 'The :attribute field format is invalid.',
            'starts_with' => 'The :attribute field must start with one of the following: :values.',
            'ends_with' => 'The :attribute field must end with one of the following: :values.',
            'lowercase' => 'The :attribute field must be lowercase.',
            'uppercase' => 'The :attribute field must be uppercase.',
            'distinct' => 'The :attribute field has a duplicate value.',
            'size' => 'The :attribute field must be :size.',
            'between' => 'The :attribute field must be between :min and :max.',
            'digits' => 'The :attribute field must be :digits digits.',
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

    protected function passesAcceptedRule(mixed $value): bool
    {
        return in_array($value, ['yes', 'on', 1, '1', true, 'true'], true);
    }

    protected function passesDeclinedRule(mixed $value): bool
    {
        return in_array($value, ['no', 'off', 0, '0', false, 'false'], true);
    }

    protected function shouldRequireIf(string $pattern, string $field, string $argument, array $data): bool
    {
        [$otherField, $expectedValues] = $this->parseDependentRuleArgument($argument);
        $otherField = $this->resolveDependentField($pattern, $field, $otherField);
        $otherValue = $this->getValueByPath($data, $otherField);

        return $this->matchesDependentValues($otherValue, $expectedValues);
    }

    protected function shouldRequireUnless(string $pattern, string $field, string $argument, array $data): bool
    {
        [$otherField, $expectedValues] = $this->parseDependentRuleArgument($argument);
        $otherField = $this->resolveDependentField($pattern, $field, $otherField);
        $otherValue = $this->getValueByPath($data, $otherField);

        return !$this->matchesDependentValues($otherValue, $expectedValues);
    }

    protected function shouldRequireWith(string $pattern, string $field, string $argument, array $data): bool
    {
        foreach ($this->resolvedDependentFields($pattern, $field, $this->parseDependentFieldsArgument($argument)) as $dependentField) {
            $dependentValue = $this->getValueByPath($data, $dependentField);

            if (!$this->isEmptyValue($dependentValue, $this->pathExists($data, $dependentField))) {
                return true;
            }
        }

        return false;
    }

    protected function shouldRequireWithout(string $pattern, string $field, string $argument, array $data): bool
    {
        foreach ($this->resolvedDependentFields($pattern, $field, $this->parseDependentFieldsArgument($argument)) as $dependentField) {
            $dependentValue = $this->getValueByPath($data, $dependentField);

            if ($this->isEmptyValue($dependentValue, $this->pathExists($data, $dependentField))) {
                return true;
            }
        }

        return false;
    }

    protected function passesConfirmedRule(string $field, mixed $value, array $data): bool
    {
        $confirmationField = $this->confirmationField($field);

        if (!$this->pathExists($data, $confirmationField)) {
            return false;
        }

        return $this->passesSameRule($value, $this->getValueByPath($data, $confirmationField));
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

    protected function passesAlphaDashRule(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[A-Za-z0-9_-]+$/', $value) === 1;
    }

    protected function passesDateRule(mixed $value): bool
    {
        if (is_string($value) === false) {
            return false;
        }

        return strtotime($value) !== false;
    }

    protected function passesAsciiRule(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[\x00-\x7F]*$/', $value) === 1;
    }

    protected function passesUuidRule(mixed $value): bool
    {
        return is_string($value)
            && preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/', $value) === 1;
    }

    protected function passesIpRule(mixed $value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    protected function passesIpv4Rule(mixed $value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    protected function passesIpv6Rule(mixed $value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    protected function passesJsonRule(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function passesRegexRule(mixed $value, string $pattern): bool
    {
        return is_string($value) && @preg_match($pattern, $value) === 1;
    }

    protected function passesNotRegexRule(mixed $value, string $pattern): bool
    {
        return is_string($value) && @preg_match($pattern, $value) === 0;
    }

    protected function passesStartsWithRule(mixed $value, array $needles): bool
    {
        if (!is_string($value)) {
            return false;
        }

        foreach ($needles as $needle) {
            if ($needle !== '' && str_starts_with($value, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function passesEndsWithRule(mixed $value, array $needles): bool
    {
        if (!is_string($value)) {
            return false;
        }

        foreach ($needles as $needle) {
            if ($needle !== '' && str_ends_with($value, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function passesLowercaseRule(mixed $value): bool
    {
        return is_string($value) && mb_strtolower($value) === $value;
    }

    protected function passesUppercaseRule(mixed $value): bool
    {
        return is_string($value) && mb_strtoupper($value) === $value;
    }

    protected function confirmationField(string $field): string
    {
        $segments = explode('.', $field);
        $lastIndex = array_key_last($segments);

        if ($lastIndex !== null) {
            $segments[$lastIndex] .= '_confirmation';
        }

        return implode('.', $segments);
    }

    protected function parseDependentRuleArgument(string $argument): array
    {
        $parts = explode(',', $argument);
        $field = array_shift($parts);

        return [$field ?? '', $parts];
    }

    protected function parseDependentFieldsArgument(string $argument): array
    {
        return array_values(array_filter(explode(',', $argument), static fn(string $field): bool => $field !== ''));
    }

    protected function parseListRuleArgument(string $argument): array
    {
        return array_values(array_filter(
            array_map(static fn(string $item): string => trim($item), explode(',', $argument)),
            static fn(string $item): bool => $item !== ''
        ));
    }

    protected function resolveDependentField(string $pattern, string $field, string $dependentField): string
    {
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
            if ($segment === '*') {
                $replacement = array_shift($wildcards);

                if ($replacement === null) {
                    break;
                }

                $dependentSegments[$index] = $replacement;
            }
        }

        return implode('.', $dependentSegments);
    }

    protected function resolvedDependentFields(string $pattern, string $field, array $dependentFields): array
    {
        return array_map(
            fn(string $dependentField): string => $this->resolveDependentField($pattern, $field, $dependentField),
            $dependentFields,
        );
    }

    protected function resolveFieldTargets(array $data, string $field): array
    {
        if (!str_contains($field, '*')) {
            return [[
                'field' => $field,
                'value' => $this->getValueByPath($data, $field),
                'present' => $this->pathExists($data, $field),
            ]];
        }

        return $this->resolveWildcardTargets($data, explode('.', $field), []);
    }

    protected function resolveWildcardTargets(mixed $data, array $segments, array $path): array
    {
        if ($segments === []) {
            return [[
                'field' => implode('.', $path),
                'value' => $data,
                'present' => true,
            ]];
        }

        $segment = array_shift($segments);

        if ($segment === '*') {
            if (!is_array($data)) {
                return [];
            }

            $targets = [];

            foreach ($data as $key => $value) {
                $targets = array_merge(
                    $targets,
                    $this->resolveWildcardTargets($value, $segments, [...$path, (string) $key]),
                );
            }

            return $targets;
        }

        if (!is_array($data) || !array_key_exists($segment, $data)) {
            return [[
                'field' => implode('.', [...$path, $segment, ...$segments]),
                'value' => null,
                'present' => false,
            ]];
        }

        return $this->resolveWildcardTargets($data[$segment], $segments, [...$path, $segment]);
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

    protected function matchesDependentValues(mixed $value, array $expectedValues): bool
    {
        foreach ($expectedValues as $expectedValue) {
            if ((string) $value === $expectedValue) {
                return true;
            }
        }

        return false;
    }

    protected function isEmptyValue(mixed $value, bool $present): bool
    {
        return !$present || $value === null || $value === '' || $value === [];
    }

    protected function dependentFieldLabels(array $fields, array $attributes): string
    {
        return implode(', ', array_map(
            static fn(string $field): string => $attributes[$field] ?? $field,
            $fields,
        ));
    }

    protected function passesDistinctRule(mixed $value, array &$seen): bool
    {
        $key = $this->distinctValueKey($value);

        if (array_key_exists($key, $seen)) {
            return false;
        }

        $seen[$key] = true;

        return true;
    }

    protected function distinctValueKey(mixed $value): string
    {
        if (is_array($value)) {
            return 'array:' . serialize($value);
        }

        if (is_bool($value)) {
            return 'bool:' . ($value ? '1' : '0');
        }

        if ($value === null) {
            return 'null';
        }

        return get_debug_type($value) . ':' . (string) $value;
    }

    protected function passesSizeRule(mixed $value, int $size): bool
    {
        $measuredSize = $this->valueSize($value);

        return $measuredSize !== null && $measuredSize == $size;
    }

    protected function passesBetweenRule(mixed $value, int $minimum, int $maximum): bool
    {
        $size = $this->valueSize($value);

        return $size !== null && $size >= $minimum && $size <= $maximum;
    }

    protected function passesDigitsRule(mixed $value, int $digits): bool
    {
        $stringValue = (string) $value;

        return preg_match('/^\d+$/', $stringValue) === 1 && strlen($stringValue) === $digits;
    }

    protected function parseRangeRuleArgument(string $argument): array
    {
        $parts = explode(',', $argument, 2);

        if (count($parts) !== 2) {
            return [null, null];
        }

        return [(int) $parts[0], (int) $parts[1]];
    }

    protected function valueSize(mixed $value): int|float|null
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            return mb_strlen($value);
        }

        if (is_array($value)) {
            return count($value);
        }

        return null;
    }

    protected function passesMinRule(mixed $value, int $minimum): bool
    {
        $size = $this->valueSize($value);

        return $size !== null && $size >= $minimum;
    }

    protected function passesMaxRule(mixed $value, int $maximum): bool
    {
        $size = $this->valueSize($value);

        return $size !== null && $size <= $maximum;
    }
}
