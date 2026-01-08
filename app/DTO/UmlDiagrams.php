<?php

namespace App\DTO;

class UmlDiagrams
{
    public function __construct(
        public string $useCase,
        public string $class,
        public string $sequence
    ) {}

    public function toArray(): array
    {
        return [
            'use_case' => $this->useCase,
            'class' => $this->class,
            'sequence' => $this->sequence,
        ];
    }
}
