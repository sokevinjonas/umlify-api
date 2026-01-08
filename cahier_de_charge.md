# Cahier des charges – UMLify (MVP)

## 1. Présentation générale

### 1.1 Objectif du projet
UMLify est une API backend dont l’objectif est de générer automatiquement des diagrammes UML cohérents à partir d’une description textuelle libre d’un projet logiciel.

L’utilisateur fournit une description détaillée de son projet. Le système analyse cette description, en extrait les concepts métier, puis génère automatiquement :
- un diagramme de cas d’utilisation
- un diagramme de classes
- un diagramme de séquence (scénario principal)

Les diagrammes sont générés en **PlantUML** afin d’être exportables, versionnables et rendus en image.

---

## 2. Périmètre du MVP

### 2.1 Fonctionnalités incluses
- Analyse sémantique d’un texte descriptif
- Extraction des concepts métier (acteurs, entités, cas d’utilisation)
- Normalisation et structuration UML
- Génération de diagrammes UML via PlantUML
- Validation automatique des résultats
- Correction itérative automatique (max 2 tentatives)
- API REST JSON

### 2.2 Fonctionnalités exclues
- Authentification utilisateur avancée
- Collaboration multi-utilisateurs
- Éditeur graphique UML
- Historique long terme des projets

---

## 3. Architecture technique

### 3.1 Stack technique
- Backend : Laravel 12
- Architecture : API REST
- IA : Claude (Anthropic)
- UML : PlantUML

### 3.2 Organisation Laravel recommandée
```
app/
 ├── Http/Controllers/UmlController.php
 ├── Services/IA/
 │    ├── ClaudeService.php
 │    ├── PromptBuilder.php
 ├── Services/Uml/
 │    ├── AnalysisService.php
 │    ├── NormalizationService.php
 │    ├── GenerationService.php
 │    ├── ValidationService.php
 │    ├── RepairService.php
 └── DTO/
      ├── AnalysisResult.php
      ├── NormalizedModel.php
```

---

## 4. Pipeline IA détaillé

### 4.1 Étape 1 – Analyse métier

**Objectif** : Comprendre le projet sans générer de diagramme.

**Entrée** :
```json
{ "description": "texte libre" }
```

**Sortie attendue (JSON strict)** :
```json
{
  "actors": [],
  "use_cases": [],
  "entities": [],
  "business_rules": []
}
```

**Prompt Claude – Analyse**
```
Tu es un analyste logiciel senior.
Analyse la description suivante et extrais uniquement les concepts métier.
N’inclus aucun diagramme UML.
Réponds exclusivement en JSON valide avec les clés suivantes :
actors, use_cases, entities, business_rules.
Description : {{description}}
```

---

### 4.2 Étape 2 – Normalisation UML

**Objectif** : Rendre les concepts exploitables pour UML.

Règles :
- Acteurs : noms humains, singulier
- Entités : noms métier singuliers
- Cas d’utilisation : verbe + complément
- Suppression des doublons

**Sortie** :
```json
{
  "actors": [],
  "use_cases": [],
  "entities": [
    {
      "name": "",
      "attributes": []
    }
  ],
  "relations": []
}
```

**Prompt Claude – Normalisation**
```
Normalise les données UML suivantes.
Corrige les noms, supprime les doublons et assure la cohérence.
Réponds uniquement en JSON valide.
Données : {{analysis_json}}
```

---

### 4.3 Étape 3 – Génération UML (PlantUML)

Règle : **un prompt = un diagramme**

#### Cas d’utilisation
```
Génère uniquement un diagramme de cas d’utilisation en PlantUML valide.
Aucun texte explicatif.
Données : {{normalized_json}}
```

#### Diagramme de classes
```
Génère uniquement un diagramme de classes en PlantUML valide.
Chaque classe doit avoir au moins un attribut.
Données : {{normalized_json}}
```

#### Diagramme de séquence
```
Génère uniquement un diagramme de séquence PlantUML représentant le scénario principal.
Aucun texte hors PlantUML.
Données : {{normalized_json}}
```

---

### 4.4 Étape 4 – Validation automatique

Vérifications :
- Syntaxe PlantUML correcte (@startuml / @enduml)
- Aucune classe sans attribut
- Aucune relation orpheline
- Acteurs utilisés

---

### 4.5 Étape 5 – Correction itérative

Si erreur détectée :
```
Corrige le diagramme UML suivant selon ces erreurs précises :
{{errors}}
Diagramme : {{plantuml}}
```

- Maximum 2 tentatives
- Sinon retour erreur API

---

## 5. Endpoints API

### POST /api/uml/generate

**Payload** :
```json
{ "description": "texte du projet" }
```

**Réponse succès** :
```json
{
  "use_case": "@startuml...",
  "class": "@startuml...",
  "sequence": "@startuml..."
}
```

**Erreurs possibles** :
- 422 : description invalide
- 500 : génération échouée

---

## 6. Contraintes techniques
- Temps max par requête IA : 30s
- Taille max description : 5 000 caractères
- Logs obligatoires

---

## 7. Évolutions futures
- RAG UML
- Historique utilisateur
- Fine-tuning

---

## 8. Règle pour implémentation

> Le développeur IA (Claude Code) doit implémenter STRICTEMENT ce cahier des charges.
Aucune fonctionnalité non mentionnée ne doit être ajoutée.
