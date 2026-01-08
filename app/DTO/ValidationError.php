<?php

namespace App\DTO;

readonly class ValidationError
{
    public function __construct(
        public string $diagramType,
        public array $errors
    ) {}

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function getErrorsAsString(): string
    {
        return implode("\n", $this->errors);
    }
}
