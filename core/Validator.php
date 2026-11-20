<?php
namespace Core;

class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private bool $ran = false;

    private function __construct(array $data, array $rules)
    {
        $this->data  = $data;
        $this->rules = $rules;
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function fails(): bool
    {
        $this->run();
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return !$this->fails();
    }

    public function errors(): array
    {
        $this->run();
        return $this->errors;
    }

    public function validated(): array
    {
        $clean = [];
        foreach (array_keys($this->rules) as $field) {
            $value = $this->data[$field] ?? null;
            $clean[$field] = is_string($value) ? trim($value) : $value;
        }
        return $clean;
    }

    private function run(): void
    {
        if ($this->ran) {
            return;
        }
        $this->ran = true;

        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;
            if (is_string($value)) {
                $value = trim($value);
            }
            $isEmpty = ($value === null || $value === '');

            if ($isEmpty && !in_array('required', $rules, true)) {
                continue;
            }

            foreach ($rules as $rule) {
                $message = $this->check($field, $value, $rule, $rules);
                if ($message !== null) {
                    $this->errors[$field] = $message;
                    break;
                }
            }
        }
    }

    private function check(string $field, $value, string $rule, array $allRules): ?string
    {
        $parts = explode(':', $rule, 2);
        $name  = $parts[0];
        $arg   = $parts[1] ?? null;
        $label = ucfirst(str_replace('_', ' ', $field));
        $isEmpty = ($value === null || $value === '');

        switch ($name) {
            case 'required':
                return $isEmpty ? "$label is required." : null;

            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) ? null : "$label must be a valid email address.";

            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) ? null : "$label must be a valid URL.";

            case 'numeric':
                return is_numeric($value) ? null : "$label must be a number.";

            case 'integer':
                return filter_var($value, FILTER_VALIDATE_INT) !== false ? null : "$label must be a whole number.";

            case 'min':
                if (in_array('numeric', $allRules, true) || in_array('integer', $allRules, true)) {
                    return (is_numeric($value) && $value >= $arg) ? null : "$label must be at least $arg.";
                }
                return mb_strlen((string) $value) >= (int) $arg ? null : "$label must be at least $arg characters.";

            case 'max':
                if (in_array('numeric', $allRules, true) || in_array('integer', $allRules, true)) {
                    return (is_numeric($value) && $value <= $arg) ? null : "$label must not be greater than $arg.";
                }
                return mb_strlen((string) $value) <= (int) $arg ? null : "$label must not be longer than $arg characters.";

            case 'in':
                return in_array((string) $value, explode(',', (string) $arg), true) ? null : "$label has an invalid value.";

            case 'confirmed':
                return ($value === ($this->data[$field . '_confirmation'] ?? null)) ? null : "$label confirmation does not match.";

            case 'unique':
                return $this->isUnique((string) $arg, $value) ? null : "This $label is already taken.";
        }

        return null;
    }

    private function isUnique(string $arg, $value): bool
    {
        $parts   = explode(',', $arg);
        $table   = $parts[0];
        $column  = $parts[1] ?? 'id';
        $ignoreId = $parts[2] ?? null;

        $sql    = "SELECT COUNT(*) FROM $table WHERE $column = ?";
        $params = [$value];
        if ($ignoreId !== null && $ignoreId !== '') {
            $sql    .= ' AND id <> ?';
            $params[] = $ignoreId;
        }
        return (int) Database::getInstance()->value($sql, $params) === 0;
    }
}
