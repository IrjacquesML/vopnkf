# VOP Étude Biblique — Laravel

Application web Laravel pour la gestion des leçons bibliques avec progression, quiz et demandes de prière.

## Prérequis

- PHP 8.3+ (extensions: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, fileinfo)
- Composer 2
- Node.js 20+ / npm
- MySQL (XAMPP recommandé)

## Installation

```bash
composer install
npm install
copy .env.example .env   # Windows
php artisan key:generate
```

Créez la base MySQL `vop_etude`, puis :

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

L’application sera accessible sur http://localhost:8000

### Comptes de test (après seed)

| Rôle        | Email             | Mot de passe |
|-------------|-------------------|--------------|
| Admin       | admin@vop.local   | password     |
| Utilisateur | test@example.com  | password     |

## Structure

- `app/Models` — modèles Eloquent (User, Categorie, Lecon, Question, …)
- `database/migrations` — schéma VOP
- `legacy/` — ancien code PHP procédural (référence)
- `public/css`, `public/js` — assets migrés depuis l’ancien projet
- `config/bible.php` — configuration API Bible

## Ancien projet

Le code PHP procédural d’origine est conservé dans `legacy/` pour référence pendant la migration progressive des écrans vers Blade / contrôleurs Laravel.
