<?php

namespace App\Services\Uml;

use App\DTO\NormalizedModel;
use App\DTO\UmlDiagrams;
use App\Services\IA\ClaudeService;
use App\Services\IA\PromptBuilder;
use Illuminate\Support\Facades\Log;

class GenerationService
{
    public function __construct(
        private ClaudeService $claudeService
    ) {}

    public function generate(NormalizedModel $model): UmlDiagrams
    {
        Log::info('UML Generation started');

        $normalizedJson = $model->toJson();

        // Génération du diagramme de cas d'utilisation
        Log::info('Generating use case diagram');
        $useCasePrompt = PromptBuilder::buildUseCasePrompt($normalizedJson);
        $useCase = trim($this->claudeService->sendMessage($useCasePrompt, 30));

        // Génération du diagramme de classes
        Log::info('Generating class diagram');
        $classPrompt = PromptBuilder::buildClassPrompt($normalizedJson);
        $class = trim($this->claudeService->sendMessage($classPrompt, 30));

        // Génération du diagramme de séquence
        Log::info('Generating sequence diagram');
        $sequencePrompt = PromptBuilder::buildSequencePrompt($normalizedJson);
        $sequence = trim($this->claudeService->sendMessage($sequencePrompt, 30));

        Log::info('UML Generation completed successfully');

        return new UmlDiagrams($useCase, $class, $sequence);
    }
}
