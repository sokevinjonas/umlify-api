<?php

namespace App\Services\Uml;

use App\Services\IA\ClaudeService;
use App\Services\IA\PromptBuilder;
use Illuminate\Support\Facades\Log;

class RepairService
{
    public function __construct(
        private ClaudeService $claudeService,
        private ValidationService $validationService
    ) {}

    public function repair(string $plantUml, string $diagramType, int $maxAttempts = 2): string
    {
        Log::info('UML Repair started', [
            'diagram_type' => $diagramType,
            'max_attempts' => $maxAttempts
        ]);

        $currentPlantUml = $plantUml;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $validationError = $this->validationService->validate($currentPlantUml, $diagramType);

            if (!$validationError->hasErrors()) {
                Log::info('UML Repair successful', [
                    'diagram_type' => $diagramType,
                    'attempts' => $attempt
                ]);
                return $currentPlantUml;
            }

            Log::warning('UML Repair attempt', [
                'diagram_type' => $diagramType,
                'attempt' => $attempt,
                'errors' => $validationError->errors
            ]);

            $errorsString = $validationError->getErrorsAsString();
            $repairPrompt = PromptBuilder::buildRepairPrompt($errorsString, $currentPlantUml);
            $currentPlantUml = trim($this->claudeService->sendMessage($repairPrompt, 30));
        }

        Log::error('UML Repair failed after max attempts', [
            'diagram_type' => $diagramType,
            'max_attempts' => $maxAttempts
        ]);

        throw new \RuntimeException(
            "Impossible de corriger le diagramme $diagramType après $maxAttempts tentatives"
        );
    }
}
