<?php

declare(strict_types=1);

namespace App\Core\Validation;

use App\Core\Exceptions\ValidationException;

final class Validator
{
    /** @var array<string, string[]> */
    private array $errors = [];

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     */
    public static function make(array $data, array $rules): self
    {
        $validator = new self();
        $validator->validate($data, $rules);

        return $validator;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     */
    private function validate(array $data, array $rules): void
    {
        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;

            foreach (explode('|', $ruleString) as $rule) {
                [$ruleName, $ruleParam] = array_pad(explode(':', $rule, 2), 2, null);
                $this->applyRule($field, $value, (string) $ruleName, $ruleParam);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule, ?string $param): void
    {
        match ($rule) {
            'required' => $this->ruleRequired($field, $value),
            'email' => $this->ruleEmail($field, $value),
            'min' => $this->ruleMin($field, $value, (int) $param),
            'max' => $this->ruleMax($field, $value, (int) $param),
            'string' => $this->ruleString($field, $value),
            'integer' => $this->ruleInteger($field, $value),
            default => null,
        };
    }

    private function ruleRequired(string $field, mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->addError($field, "El campo {$field} es obligatorio");
        }
    }

    private function ruleEmail(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "El campo {$field} debe ser un email valido");
        }
    }

    private function ruleMin(string $field, mixed $value, int $min): void
    {
        if (is_string($value) && mb_strlen($value) < $min) {
            $this->addError($field, "El campo {$field} debe tener al menos {$min} caracteres");
        }
    }

    private function ruleMax(string $field, mixed $value, int $max): void
    {
        if (is_string($value) && mb_strlen($value) > $max) {
            $this->addError($field, "El campo {$field} no debe superar {$max} caracteres");
        }
    }

    private function ruleString(string $field, mixed $value): void
    {
        if ($value !== null && !is_string($value)) {
            $this->addError($field, "El campo {$field} debe ser texto");
        }
    }

    private function ruleInteger(string $field, mixed $value): void
    {
        if ($value !== null && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, "El campo {$field} debe ser un numero entero");
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /** @return array<string, string[]> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @throws ValidationException */
    public function validateOrFail(): void
    {
        if ($this->fails()) {
            throw new ValidationException($this->errors);
        }
    }
}