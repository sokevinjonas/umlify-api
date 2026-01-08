# UMLify – Backend API (MVP)

UMLify est une API backend permettant de générer automatiquement des diagrammes UML cohérents à partir d’une description textuelle libre d’un projet logiciel.

L’objectif du projet est de transformer une idée exprimée en langage naturel en une architecture UML exploitable (PlantUML), sans dessin manuel.

---

## 🚀 Fonctionnalités (MVP)

- Analyse sémantique d’une description de projet
- Extraction automatique des concepts métier :
  - acteurs
  - cas d’utilisation
  - entités
  - règles métier
- Normalisation UML
- Génération automatique de :
  - diagramme de cas d’utilisation
  - diagramme de classes
  - diagramme de séquence (scénario principal)
- Génération en **PlantUML**
- Validation automatique des diagrammes
- Correction itérative automatique (max 2 tentatives)
- API REST JSON

---

## 🧱 Stack technique

- **Backend** : Laravel 10+
- **Architecture** : API REST
- **IA** : Claude (Anthropic)
- **Diagrammes** : PlantUML
- **Format de sortie** : Texte PlantUML (exportable / rendable en image)

---

## 🏗️ Architecture du projet

app/
├── Http/
│ └── Controllers/
│ └── UmlController.php
├── Services/
│ ├── IA/
│ │ ├── ClaudeService.php
│ │ └── PromptBuilder.php
│ └── Uml/
│ ├── AnalysisService.php
│ ├── NormalizationService.php
│ ├── GenerationService.php
│ ├── ValidationService.php
│ └── RepairService.php
└── DTO/
├── AnalysisResult.php
└── NormalizedModel.php


---

## 🔁 Pipeline IA

1. **Analyse métier**  
   Compréhension du projet et extraction des concepts (sans UML)

2. **Normalisation UML**  
   Nettoyage, cohérence et structuration des données

3. **Génération UML**  
   Génération séparée de chaque diagramme en PlantUML

4. **Validation automatique**  
   Vérification de la syntaxe et des règles UML

5. **Correction itérative**  
   Correction automatique si erreurs détectées (max 2 tentatives)

---

## 🔌 Endpoint principal

### POST `/api/uml/generate`

#### Requête
```json
{
  "description": "Description détaillée du projet logiciel"
}
```
#### Réponse (succès)
```
{
  "use_case": "@startuml ... @enduml",
  "class": "@startuml ... @enduml",
  "sequence": "@startuml ... @enduml"
}
```

#### Erreurs possibles

422 : description invalide ou vide

500 : génération UML échouée après corrections automatiques

### ⚙️ Contraintes techniques

Taille maximale de la description : 5 000 caractères
Temps maximum par appel IA : 30 secondes
Maximum 2 tentatives de correction automatique
Réponses IA strictement contrôlées (JSON / PlantUML uniquement)
Logs obligatoires pour chaque génération

### 🧪 Règles de validation UML

Présence de @startuml et @enduml
Aucune classe sans attribut
Aucune relation orpheline
Acteurs utilisés dans au moins un cas d’utilisation
Syntaxe PlantUML valide

### 🧠 Philosophie du projet

Simplicité avant tout
Pas d’édition graphique manuelle
Pas de fonctionnalités hors MVP
L’IA est utilisée comme moteur d’analyse, pas comme boîte noire

### 🔮 Évolutions prévues (hors MVP)

RAG UML (base de projets types)
Historique des générations
Authentification utilisateur
Export PNG automatique
Amélioration continue des prompts

### 📜 Règle d’implémentation

Toute implémentation doit respecter strictement le cahier des charges.
Aucune fonctionnalité non mentionnée ne doit être ajoutée au MVP.