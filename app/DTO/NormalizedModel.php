<?php

namespace App\DTO;

readonly class NormalizedModel
{
    public function __construct(
        public array $actors,
        public array $use_cases,
        public array $entities,
        public array $relations
    ) {}

    public function toJson(): string
    {
        return json_encode([
            'actors' => $this->actors,
            'use_cases' => $this->use_cases,
            'entities' => $this->entities,
            'relations' => $this->relations,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON: ' . json_last_error_msg());
        }

        if (!isset($data['actors'], $data['use_cases'], $data['entities'], $data['relations'])) {
            throw new \RuntimeException('Missing required keys in normalized model');
        }

        return new self(
            actors: $data['actors'],
            use_cases: $data['use_cases'],
            entities: $data['entities'],
            relations: $data['relations']
        );
    }
}
