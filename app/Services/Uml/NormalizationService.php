<?php

namespace App\Services\Uml;

use App\DTO\AnalysisResult;
use App\DTO\NormalizedModel;
use App\Services\IA\ClaudeService;
use App\Services\IA\PromptBuilder;
use Illuminate\Support\Facades\Log;

class NormalizationService
{
    public function __construct(
        private ClaudeService $claudeService
    ) {}

    public function normalize(AnalysisResult $analysis): NormalizedModel
    {
        Log::info('UML Normalization started');

        $analysisJson = json_encode($analysis->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $prompt = PromptBuilder::buildNormalizationPrompt($analysisJson);
        $response = $this->claudeService->sendMessage($prompt, 30);

        try {
            $normalizedModel = NormalizedModel::fromJson($response);

            Log::info('UML Normalization completed successfully', [
                'actors_count' => count($normalizedModel->actors),
                'use_cases_count' => count($normalizedModel->use_cases),
                'entities_count' => count($normalizedModel->entities),
                'relations_count' => count($normalizedModel->relations)
            ]);

            return $normalizedModel;

        } catch (\RuntimeException $e) {
            Log::error('Normalization parsing failed', [
                'error' => $e->getMessage(),
                'response' => $response
            ]);

            throw $e;
        }
    }
}
