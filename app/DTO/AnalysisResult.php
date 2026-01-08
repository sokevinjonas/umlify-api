<?php

namespace App\DTO;

readonly class AnalysisResult
{
    public function __construct(
        public array $actors,
        public array $use_cases,
        public array $entities,
        public array $business_rules
    ) {}

    public function toArray(): array
    {
        return [
            'actors' => $this->actors,
            'use_cases' => $this->use_cases,
            'entities' => $this->entities,
            'business_rules' => $this->business_rules,
        ];
    }

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON: ' . json_last_error_msg());
        }

        if (!isset($data['actors'], $data['use_cases'], $data['entities'], $data['business_rules'])) {
            throw new \RuntimeException('Missing required keys in analysis result');
        }

        return new self(
            actors: $data['actors'],
            use_cases: $data['use_cases'],
            entities: $data['entities'],
            business_rules: $data['business_rules']
        );
    }
}
