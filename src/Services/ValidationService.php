<?php
namespace App\Services;

use stdClass;

class ValidationService
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            $fieldRules = is_string($ruleSet) ? explode('|', $ruleSet) : $fieldRules = $ruleSet;

            foreach ($fieldRules as $rule) {
                $result = $this->applyRule($field, $value, $rule, $data);
                if ($result !== true) {
                    $this->errors[$field][] = $result;
                }
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function applyRule(string $field, $value, string $rule, array $allData): true|array
    {
        $params = [];
        if (strpos($rule, ':') !== false) {
            [$ruleName, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        } else {
            $ruleName = $rule;
        }

        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '') {
                    return ["The {$field} field is required."];
                }
                break;

            case 'string':
                if (!is_string($value)) {
                    return ["The {$field} must be a string."];
                }
                break;

            case 'integer':
            case 'int':
                if (!is_numeric($value) || (int)$value != $value) {
                    return ["The {$field} must be an integer."];
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    return ["The {$field} must be a number."];
                }
                break;

            case 'array':
                if (!is_array($value)) {
                    return ["The {$field} must be an array."];
                }
                break;

            case 'boolean':
            case 'bool':
                if (!in_array($value, [true, false, 0, 1, '0', '1'], true)) {
                    return ["The {$field} must be a boolean."];
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return ["The {$field} must be a valid email address."];
                }
                break;

            case 'min':
                if (is_numeric($value) && $value < (float)$params[0]) {
                    return ["The {$field} must be at least {$params[0]}."];
                } elseif (is_string($value) && strlen($value) < (int)$params[0]) {
                    return ["The {$field} must be at least {$params[0]} characters."];
                }
                break;

            case 'max':
                if (is_numeric($value) && $value > (float)$params[0]) {
                    return ["The {$field} must not exceed {$params[0]}."];
                } elseif (is_string($value) && strlen($value) > (int)$params[0]) {
                    return ["The {$field} must not exceed {$params[0]} characters."];
                }
                break;

            case 'in':
                $allowed = $params;
                if (!in_array($value, $allowed)) {
                    return ["The {$field} must be one of: " . implode(', ', $allowed) . "."];
                }
                break;

            case 'date_format':
                $format = $params[0] ?? 'Y-m-d H:i:s';
                $d = \DateTime::createFromFormat($format, $value);
                if (!$d || $d->format($format) !== $value) {
                    return ["The {$field} must match the format {$format}."];
                }
                break;

            case 'time':
                if (!preg_match('/^([01][0-9]|2[0-3]):([0-5][0-9])$/', $value)) {
                    return ["The {$field} must be a valid time in HH:MM format."];
                }
                break;

            case 'json_array':
                if (!is_array($value) || empty($value)) {
                    return ["The {$field} must be a non-empty array."];
                }
                break;

            case 'exists_in':
                $modelClass = $params[0];
                $checkField = $params[1] ?? 'id';
                try {
                    $model = new $modelClass();
                    $exists = $model->newModelQuery()->where($checkField, $value)->exists();
                    if (!$exists) {
                        return ["The selected {$field} is invalid or does not exist."];
                    }
                } catch (\Exception $e) {
                    return ["Error validating {$field}: " . $e->getMessage()];
                }
                break;

            case 'unique':
                $modelClass = $params[0];
                $checkField = $params[1] ?? $field;
                $ignoreId = $allData['id'] ?? null; // For updates

                try {
                    $model = new $modelClass();
                    $query = $model->newModelQuery()->where($checkField, $value);
                    if ($ignoreId) {
                        $primaryKey = $model->getKeyName();
                        $query->where($primaryKey, '!=', $ignoreId);
                    }
                    if ($query->exists()) {
                        return ["The {$field} has already been taken."];
                    }
                } catch (\Exception $e) {
                    return ["Error checking uniqueness of {$field}."];
                }
                break;

            case 'subject_hours':
                $units = $allData['units'] ?? null;
                $weeklyHours = $value;
                if ($units && $weeklyHours && $weeklyHours < $units) {
                    return ["Weekly hours must be at least equal to units."];
                }
                break;

            case 'override_allowed':
                $isOverride = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                $classId = $allData['id'] ?? null;

                if (!$isOverride && $classId) {
                    // If not override, ensure it's not modifying auto-gen class
                    $existing = \App\Models\ClassModel::find($classId);
                    if ($existing && $existing->is_override) {
                        return ["Auto-generated classes cannot be reverted."];
                    }
                }
                break;

            default:
                // Custom or unknown rule
                return ["Unknown validation rule: {$ruleName}."];
        }

        return true;
    }

    /**
     * Helper to throw or return errors
     */
    public function fail(string $message): array
    {
        $this->errors['general'][] = $message;
        return $this->errors;
    }
}