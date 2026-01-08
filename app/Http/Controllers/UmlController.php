<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Uml\RepairService;
use Illuminate\Support\Facades\Log;
use App\Services\Uml\AnalysisService;
use App\Services\Uml\GenerationService;
use App\Services\Uml\ValidationService;
use App\Services\Uml\NormalizationService;

class UmlController extends Controller
{
    public function __construct(
        private AnalysisService $analysisService,
        private NormalizationService $normalizationService,
        private GenerationService $generationService,
        private ValidationService $validationService,
        private RepairService $repairService
    ) {}

    public function generate(Request $request): JsonResponse
    {
        try {
            // Étape 1: Validation de l'input
            $validated = $request->validate([
                'description' => 'required|string|max:5000',
            ]);

            $description = $validated['description'];

            Log::info('UML generation pipeline started', [
                'description_length' => strlen($description)
            ]);

            // Étape 2: Analyse métier
            $analysis = $this->analysisService->analyze($description);

            // Étape 3: Normalisation UML
            $normalized = $this->normalizationService->normalize($analysis);

            // Étape 4: Génération des 3 diagrammes PlantUML
            $diagrams = $this->generationService->generate($normalized);

            // Étape 5: Validation + Réparation pour chaque diagramme
            $diagramTypes = [
                'use_case' => 'useCase',
                'class' => 'class',
                'sequence' => 'sequence'
            ];

            foreach ($diagramTypes as $type => $property) {
                $validationError = $this->validationService->validate($diagrams->$property, $type);

                if ($validationError->hasErrors()) {
                    Log::warning("Validation errors detected for $type diagram, attempting repair", [
                        'errors' => $validationError->errors
                    ]);

                    $diagrams->$property = $this->repairService->repair($diagrams->$property, $type, 2);
                }
            }

            Log::info('UML generation pipeline completed successfully');

            // Étape 6: Retour de la réponse
            return response()->json($diagrams->toArray(), 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Laravel gère automatiquement les erreurs de validation avec un 422
            throw $e;

        } catch (\Exception $e) {
            Log::error('UML generation pipeline failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'La génération UML a échoué',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
