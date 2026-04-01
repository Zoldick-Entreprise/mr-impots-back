# 🛠 Conventions de Développement & Git

Pour garantir un code propre, lisible et un historique Git compréhensible, nous appliquons les règles suivantes sur ce projet.

## 1. Stratégie de Branches (Branching)

Nous utilisons une approche basée sur des branches de fonctionnalités (Feature Branching).
- **`main`** : Code en production. Toujours stable.
- **`develop`**: Code en pré-production / staging.

**Création des branches :**
Chaque branche doit être créée à partir de la branche principale (`main` ou `develop`) et suivre ce format de nommage :
`type/ID-du-ticket-description-courte`

**Types autorisés :**
- `feat/` : Nouvelle fonctionnalité (ex: `feat/12-auth-sanctum`)
- `fix/` : Correction d'un bug (ex: `fix/34-category-deletion-error`)
- `chore/` : Tâches de maintenance, mise à jour de dépendances (ex: `chore/bump-laravel-11`)
- `refactor/` : Réécriture de code sans ajout de fonctionnalité (ex: `refactor/category-controller`)
- `docs/` : Mise à jour de la documentation (ex: `docs/update-readme`)

## 2. Messages de Commit (Conventional Commits)

Nous suivons la spécification [Conventional Commits](https://www.conventionalcommits.org/fr/v1.0.0/). Les messages doivent être clairs, écrits à l'impératif et en **anglais** ou **français** (mais l'anglais est recommandé).

**Format :**
```text
type(scope): courte description en minuscules

- Détail 1 (optionnel)
- Détail 2 (optionnel)
```

**Exemples :**
- `feat(auth): install and configure sanctum`
- `fix(search): prevent crash when query is empty`
- `chore(deps): update spatie/laravel-permission`
- `style(lint): run laravel pint`

## 3. Pull Requests (Merge Requests)

Les développements ne sont jamais poussés directement sur `main`. Ils passent par une Pull Request (PR).

- **Titre de la PR** : Doit etre le titre du ticket(issue) qu'il resouds.
- **Description** :
  - Lier le ticket concerné (ex: `Closes #12`).
  - Lister rapidement les changements techniques majeurs.
  - Mentionner s'il y a des commandes à lancer lors du déploiement (ex: `php artisan migrate`...).
- **Validation** : La PR doit être approuvée et le code doit passer les tests (CI) avant d'être fusionnée (Merge).

## 4. Standards de Code (Laravel & PHP)

- **Strict Types** : Chaque fichier PHP doit obligatoirement déclarer les types stricts en tout premier lieu : `declare(strict_types=1);`.
- **Classes Finales** : Par défaut, toutes les classes doivent être déclarées comme `final` (ex: `final class DocumentController`), sauf si elles ont explicitement vocation à être étendues.
- **PHPDoc** : Chaque classe et méthode doit être accompagnée d'un bloc PHPDoc explicatif.
- **Propriétés des Modèles** : Les modèles Eloquent doivent préciser toutes leurs propriétés (attributs de base de données, relations, etc.) via des annotations `@property` au-dessus de la déclaration de classe pour faciliter l'autocomplétion et l'analyse statique.
- **Formatage** : Le projet utilise **Laravel Pint**. Avant chaque commit ou PR, le code doit être formaté (commande : `php artisan pint`).
- **Typage** : Utiliser au maximum le typage fort de PHP (arguments, retours de fonctions, propriétés).
  ```php
  // OUI
  public function getDocument(int $id): Document {}
  
  // NON
  public function getDocument($id) {}
  ```
- **Nommage** :
  - Classes, Modèles, Contrôleurs : `PascalCase` (ex: `DocumentContent`)
  - Méthodes et Variables : `camelCase` (ex: `getTranslations()`, `$documentTitle`)
  - Base de données et clés étrangères : `snake_case` (ex: `document_id`, `published_at`)
- **Langue** : Le code (variables, méthodes, commentaires techniques) doit être en **anglais**. Les traductions (fr/en) sont gérées via les fichiers de lang ou l'interface JSON.
    - **Fat Models, Skinny Controllers** : La logique métier complexe doit être dans les Modèles, des Actions ou des Services, pas dans les Contrôleurs. Le Contrôleur ne doit gérer que la requête (Validation) et la réponse (Resource/JSON).