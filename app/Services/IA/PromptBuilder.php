<?php

namespace App\Services\IA;

class PromptBuilder
{
    public static function buildAnalysisPrompt(string $description): string
    {
        return "Tu es un analyste logiciel senior.
Analyse la description suivante et extrais uniquement les concepts métier.
N'inclus aucun diagramme UML.
Réponds exclusivement en JSON valide avec les clés suivantes :
actors, use_cases, entities, business_rules.
Description : $description";
    }

    public static function buildNormalizationPrompt(string $analysisJson): string
    {
        return "Normalise les données UML suivantes.
Corrige les noms, supprime les doublons et assure la cohérence.
Réponds uniquement en JSON valide.
Données : $analysisJson";
    }

    public static function buildUseCasePrompt(string $normalizedJson): string
    {
        return "Génère uniquement un diagramme de cas d'utilisation en PlantUML valide.
Aucun texte explicatif.
Données : $normalizedJson";
    }

    public static function buildClassPrompt(string $normalizedJson): string
    {
        return "Génère uniquement un diagramme de classes en PlantUML valide.
Chaque classe doit avoir au moins un attribut.
Données : $normalizedJson";
    }

    public static function buildSequencePrompt(string $normalizedJson): string
    {
        return "Génère uniquement un diagramme de séquence PlantUML représentant le scénario principal.
Aucun texte hors PlantUML.
Données : $normalizedJson";
    }

    public static function buildRepairPrompt(string $errors, string $plantUml): string
    {
        return "Corrige le diagramme UML suivant selon ces erreurs précises :
$errors
Diagramme : $plantUml";
    }
}
