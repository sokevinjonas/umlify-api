<?php

namespace App\Services\Uml;

use App\DTO\AnalysisResult;
use App\Services\IA\ClaudeService;
use App\Services\IA\PromptBuilder;
use Illuminate\Support\Facades\Log;

class AnalysisService
{
    public function __construct(
        private ClaudeService $claudeService
    ) {}

    public function analyze(string $description): AnalysisResult
    {
        Log::info('UML Analysis started', [
            'description_length' => strlen($description)
        ]);

        $prompt = PromptBuilder::buildAnalysisPrompt($description);
        $response = $this->claudeService->sendMessage($prompt, 30);

        try {
            $analysisResult = AnalysisResult::fromJson($response);

            Log::info('UML Analysis completed successfully', [
                'actors_count' => count($analysisResult->actors),
                'use_cases_count' => count($analysisResult->use_cases),
                'entities_count' => count($analysisResult->entities)
            ]);

            return $analysisResult;

        } catch (\RuntimeException $e) {
            Log::error('Analysis parsing failed', [
                'error' => $e->getMessage(),
                'response' => $response
            ]);

            throw $e;
        }
    }
}
