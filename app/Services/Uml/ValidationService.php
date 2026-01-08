<?php

namespace App\Services\Uml;

use App\DTO\ValidationError;

class ValidationService
{
    public function validate(string $plantUml, string $diagramType): ValidationError
    {
        $errors = [];

        // Vérification de la syntaxe de base PlantUML
        if (!preg_match('/^@startuml/m', $plantUml)) {
            $errors[] = 'Le diagramme doit commencer par @startuml';
        }

        if (!preg_match('/@enduml$/m', $plantUml)) {
            $errors[] = 'Le diagramme doit se terminer par @enduml';
        }

        // Validations spécifiques par type de diagramme
        match ($diagramType) {
            'use_case' => $this->validateUseCase($plantUml, $errors),
            'class' => $this->validateClass($plantUml, $errors),
            'sequence' => $this->validateSequence($plantUml, $errors),
            default => $errors[] = "Type de diagramme invalide: $diagramType"
        };

        return new ValidationError($diagramType, $errors);
    }

    private function validateUseCase(string $plantUml, array &$errors): void
    {
        // Détecter les acteurs définis
        preg_match_all('/actor\s+(\w+)/i', $plantUml, $actorDefs);
        $definedActors = $actorDefs[1] ?? [];

        // Détecter les acteurs utilisés dans les relations
        preg_match_all('/(\w+)\s*-->/i', $plantUml, $actorRefs);
        $usedActors = $actorRefs[1] ?? [];

        if (empty($definedActors)) {
            $errors[] = 'Le diagramme de cas d\'utilisation doit contenir au moins un acteur';
        }

        // Vérifier que les acteurs définis sont utilisés
        $unusedActors = array_diff($definedActors, $usedActors);
        if (!empty($unusedActors)) {
            $errors[] = 'Acteurs non utilisés: ' . implode(', ', $unusedActors);
        }
    }

    private function validateClass(string $plantUml, array &$errors): void
    {
        // Détecter les classes avec leur contenu
        preg_match_all('/class\s+(\w+)\s*\{([^}]*)\}/s', $plantUml, $matches);

        if (empty($matches[0])) {
            $errors[] = 'Le diagramme de classes doit contenir au moins une classe';
            return;
        }

        $classNames = $matches[1] ?? [];
        $classBodies = $matches[2] ?? [];

        // Vérifier que chaque classe a au moins un attribut
        foreach ($classBodies as $index => $classBody) {
            $attributes = array_filter(array_map('trim', explode("\n", $classBody)));
            if (count($attributes) === 0) {
                $className = $classNames[$index] ?? 'Unknown';
                $errors[] = "La classe $className doit avoir au moins un attribut";
            }
        }
    }

    private function validateSequence(string $plantUml, array &$errors): void
    {
        // Vérifier la présence de participants ou acteurs
        $hasParticipants = preg_match('/(participant|actor)\s+\w+/i', $plantUml);

        // Vérifier la présence de messages
        $hasMessages = preg_match('/(->|<-|-->|<--)/i', $plantUml);

        if (!$hasParticipants && !$hasMessages) {
            $errors[] = 'Le diagramme de séquence doit contenir des participants et des messages';
        }

        if (!$hasMessages) {
            $errors[] = 'Le diagramme de séquence doit contenir au moins un message';
        }
    }
}
