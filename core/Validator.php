<?php
class Validator
{
    private array $errors = [];
    private array $data   = [];

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    /** Static factory for fluent use: Validator::make($_POST)->validate([...]). */
    public static function make(array $data): self
    {
        return new self($data);
    }

    /**
     * Validate $data against a rules array.
     *
     * Rule syntax: 'required|email|min:8|max:72'
     * Available rules: required, email, min:<n>, max:<n>,
     *                  match:<other_field>, unique:<table,column>,
     *                  date, in:<a,b,c>
     */
    public function validate(array $rules): self
    {
        foreach ($rules as $field => $ruleString) {
            $value = trim((string)($this->data[$field] ?? ''));
            foreach (explode('|', $ruleString) as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $this->apply($field, $value, $name, $param);
                // Stop checking this field after the first error.
                if (isset($this->errors[$field])) break;
            }
        }
        return $this;
    }

    private function apply(string $field, string $value, string $rule, ?string $param): void
    {
        $label = ucfirst(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if ($value === '') {
                    $this->errors[$field] = "{$label} is required.";
                }
                break;

            case 'email':
                if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = "{$label} must be a valid email address.";
                }
                break;

            case 'min':
                if ($value !== '' && mb_strlen($value) < (int)$param) {
                    $this->errors[$field] = "{$label} must be at least {$param} characters.";
                }
                break;

            case 'max':
                if ($value !== '' && mb_strlen($value) > (int)$param) {
                    $this->errors[$field] = "{$label} must not exceed {$param} characters.";
                }
                break;

            case 'match':
                // match:<other_field_name>
                $other = trim((string)($this->data[$param] ?? ''));
                if ($value !== $other) {
                    $this->errors[$field] = "{$label} does not match.";
                }
                break;

            case 'unique':
                // unique:<table>,<column>  — performs a live DB check
                [$table, $column] = explode(',', $param);
                if ($value !== '') {
                    $row = Database::queryOne(
                        "SELECT id FROM `{$table}` WHERE `{$column}` = ?", [$value]
                    );
                    if ($row) {
                        $this->errors[$field] = "{$label} is already registered.";
                    }
                }
                break;

            case 'date':
                if ($value !== '' && !isValidDate($value)) {
                    $this->errors[$field] = "{$label} must be a valid date (YYYY-MM-DD).";
                }
                break;

            case 'in':
                // in:val1,val2,val3
                if ($value !== '' && !in_array($value, explode(',', $param), true)) {
                    $this->errors[$field] = "{$label} is not a valid option.";
                }
                break;
        }
    }

    public function passes(): bool  { return empty($this->errors); }
    public function fails():  bool  { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }

    /** First error message for a specific field, or null. */
    public function error(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Return only the whitelisted fields from $data, trimmed.
     * Strips any unexpected input before writing to the database.
     */
    public function only(array $fields): array
    {
        $out = [];
        foreach ($fields as $f) {
            $out[$f] = trim((string)($this->data[$f] ?? ''));
        }
        return $out;
    }
}
