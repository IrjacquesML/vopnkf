# VOP Etude Biblique par Correspondance

Application web PHP procédurale pour la gestion des leçons bibliques avec système de progression et d'interrogation.

## Fonctionnalités

- **Page d'accueil inspirante** avec message d'espoir en Jésus-Christ
- **Système d'authentification** (inscription/connexion)
- **Catégories de leçons** organisées par thème
- **Système de verrouillage progressif** - les leçons se déverrouillent après avoir terminé la précédente
- **Interrogations interactives** avec questions à choix multiples
- **Résumé détaillé des résultats** montrant les bonnes et mauvaises réponses
- **Reconnaissance automatique des versets bibliques** - cliquez sur un verset pour voir son contenu
- **Suivi de progression** avec scores et statuts

## Installation

### Prérequis
- XAMPP (Apache + MySQL + PHP)
- Navigateur web moderne

### Étapes d'installation

1. **Démarrer XAMPP**
   - Lancez XAMPP Control Panel
   - Démarrez Apache et MySQL

2. **Créer la base de données**
   - Ouvrez phpMyAdmin (http://localhost/phpmyadmin)
   - Importez le fichier `database.sql` ou exécutez-le dans l'onglet SQL
   - Cela créera la base de données `vop_etude` avec toutes les tables et données de test

3. **Configuration**
   - Les fichiers sont déjà dans `c:\xampp\htdocs\vop\`
   - La configuration de la base de données est dans `config.php`
   - Paramètres par défaut :
     - Host: localhost
     - User: root
     - Password: (vide)
     - Database: vop_etude

4. **Accéder à l'application**
   - Ouvrez votre navigateur
   - Allez sur : http://localhost/vop/

## Structure de la base de données

- **utilisateurs** - Informations des utilisateurs
- **categories** - Catégories de leçons
- **lecons** - Contenu des leçons
- **questions** - Questions pour chaque leçon
- **options_reponse** - Options de réponse pour chaque question
- **progression_lecons** - Suivi de la progression des utilisateurs
- **reponses_utilisateurs** - Réponses soumises par les utilisateurs
- **versets** - Base de données des versets bibliques

## Utilisation

1. **Inscription**
   - Créez un compte avec nom, prénom, email et mot de passe
   - Minimum 6 caractères pour le mot de passe

2. **Connexion**
   - Connectez-vous avec votre email et mot de passe

3. **Étudier les leçons**
   - Parcourez les catégories sur le tableau de bord
   - La première leçon de chaque catégorie est déverrouillée
   - Cliquez sur "Commencer" pour débuter une leçon

4. **Lire le contenu**
   - Lisez attentivement le contenu de la leçon
   - Cliquez sur les références bibliques (ex: Jean 3:16) pour voir le verset complet

5. **Répondre aux questions**
   - Répondez à toutes les questions de l'interrogation
   - Cliquez sur "Soumettre mes réponses"

6. **Voir les résultats**
   - Consultez votre score et les détails de vos réponses
   - Les bonnes et mauvaises réponses sont clairement indiquées
   - La leçon suivante se déverrouille automatiquement

## Fichiers principaux

- `index.php` - Page d'accueil
- `inscription.php` - Formulaire d'inscription
- `connexion.php` - Formulaire de connexion
- `dashboard.php` - Tableau de bord avec toutes les leçons
- `lecon.php` - Affichage d'une leçon et son interrogation
- `soumettre_quiz.php` - Traitement des réponses
- `resultats.php` - Affichage des résultats
- `get_verset.php` - API pour récupérer les versets bibliques
- `config.php` - Configuration et fonctions utilitaires
- `styles.css` - Styles CSS
- `script.js` - JavaScript pour l'interactivité
- `database.sql` - Structure et données de la base de données

## Données de test

La base de données inclut :
- 3 catégories de leçons
- 5 leçons avec contenu
- Questions et réponses pour la première leçon
- 8 versets bibliques populaires

## Personnalisation

### Ajouter des leçons
Utilisez phpMyAdmin pour insérer de nouvelles leçons dans les tables appropriées, ou créez une interface d'administration.

### Ajouter des versets
Ajoutez des versets dans la table `versets` pour enrichir la base de données biblique.

### Modifier les styles
Éditez `styles.css` pour personnaliser l'apparence de l'application.

## Sécurité

- Mots de passe hashés avec `password_hash()`
- Protection contre les injections SQL avec requêtes préparées
- Validation et nettoyage des données utilisateur
- Sessions sécurisées pour l'authentification

## Support

Pour toute question ou problème, vérifiez que :
- XAMPP est bien démarré
- La base de données est correctement créée
- Les paramètres de connexion dans `config.php` sont corrects

## Licence

Application créée pour l'étude biblique et l'évangélisation.
