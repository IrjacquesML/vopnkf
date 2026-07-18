# Gestion des caractères spéciaux – Questions & Choix multiples (QCM)

## 1. Diagnostic des problèmes

### 1.1 Où les caractères étaient altérés

| Étape | Fichier / Contexte | Problème |
|-------|--------------------|----------|
| **Entrée** | `includes/config.php` → `clean_input()` | `htmlspecialchars()` + `stripslashes()` appliqués à l’entrée, puis données stockées en base. |
| **Stockage** | `admin/lessons/modifier_question.php`, `modifier.php` | Questions et options passées par `clean_input` avant INSERT/UPDATE → texte déjà transformé (ex. `&lt;`, `&quot;`) enregistré en BDD. |
| **Sortie HTML** | `lecon.php`, `resultats.php`, admin | Ré-échappement avec `htmlspecialchars` → **double encodage** (`&amp;lt;` au lieu de `<`). |
| **Affichage** | `lecon.php` (questions) | Questions affichées **sans** échappement avant `traiter_versets` → risque XSS si contenu malveillant. |

### 1.2 Erreurs courantes expliquées

- **`htmlspecialchars` à l’entrée**  
  On stocke du HTML échappé en base. À l’affichage, on échappe encore → double encodage, et caractères comme `{ } [ ] ( ) " ' < > & / \` s’affichent incorrectement ou en « cassé ».

- **`stripslashes`**  
  Peut corrompre les `\` légitimes (ex. `\n`, `C:\path`). Inutile si `magic_quotes` est désactivé (recommandé).

- **`addslashes` pour le SQL**  
  À proscrire. Utiliser **requêtes préparées** (`mysqli_prepare` + `bind_param`) pour éviter injections et problèmes d’échappement.

- **JSON mal formé**  
  Si du texte échappé ou contenant des guillemets non gérés est concaténé dans du JSON (ex. pour du JS), les JSON peuvent devenir invalides. Ici, le QCM n’utilise pas de JSON pour questions/options ; les APIs utilisent `json_encode`, qui gère correctement l’échappement.

- **Encodage**  
  MySQL en `utf8mb4`, PHP en UTF-8, formulaires en `charset="UTF-8"`. En cas de mélange (ISO-8859-1, etc.), les caractères accentués et Unicode peuvent être mal affichés ou stockés.

---

## 2. Stratégie correcte d’échappement

### 2.1 À l’entrée (formulaires, POST)

- **Ne pas** appliquer `htmlspecialchars` ni `stripslashes` aux champs à stocker.
- Utiliser **`prepare_text_for_storage()`** (définie dans `config.php`) :
  - `trim()` ;
  - sanitisation UTF-8 via `iconv('UTF-8', 'UTF-8//IGNORE', …)` si disponible (suppression des séquences invalides) ;
  - aucun échappement HTML ; conservation de `{ } [ ] ( ) " ' < > & / \` et Unicode valide.
- Validation métier (longueur, format) uniquement ; pas de transformation pour « sécuriser » l’affichage.

### 2.2 Au stockage (SQL)

- **Toujours** utiliser des **requêtes préparées** (`mysqli_prepare` + `mysqli_stmt_bind_param`).
- Ne jamais utiliser `addslashes` ni concaténer directement des entrées utilisateur dans une requête SQL.
- Base et connexion en **`utf8mb4`** (`mysqli_set_charset($conn, "utf8mb4")`).

### 2.3 À la sortie (HTML)

- **Toujours** échapper toute donnée issue de l’utilisateur ou de la BDD avant insertion dans du HTML.
- Utiliser **`h($texte)`** (définie dans `config.php`) :  
  `htmlspecialchars($texte, ENT_QUOTES, 'UTF-8')`.
- Règle : **échapper une seule fois, à l’affichage**, jamais à l’entrée pour du contenu stocké.

### 2.4 Cas particuliers

- **Contenu HTML riche (TinyMCE)**  
  Le contenu des leçons est du HTML. Ne pas le passer par `prepare_text_for_storage` ni `h()` pour le corps du contenu ; il est affiché tel quel (sanitization éventuelle à traiter à part si vous autorisez du HTML utilisateur).
- **Questions avec versets bibliques**  
  On applique d’abord `h($question['question'])`, puis `traiter_versets()`, puis `nl2br()`. Ainsi, le texte est protégé XSS tout en gardant le traitement des références.
- **Traductions**  
  Les textes traduits sont aussi échappés à l’affichage avec `h()`.

---

## 3. Code corrigé (résumé)

### 3.1 `includes/config.php`

- **`prepare_text_for_storage($data)`** : `trim` + sanitisation UTF-8 (`iconv` `//IGNORE`) si disponible ; aucun échappement.
- **`h($s)`** : `htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')` pour l’affichage HTML.
- **`clean_input($data)`** : délègue à `prepare_text_for_storage` (plus de `stripslashes` ni `htmlspecialchars`).

### 3.2 Admin – Questions / Options

- **Sauvegarde** : `prepare_text_for_storage($_POST['question'])`, idem pour `texte_option`. Requêtes préparées pour tous les INSERT/UPDATE.
- **Affichage** (formulaires, listes, aperçu) : `h($question['question'])`, `h($option['texte_option'])`, etc.

### 3.3 Front – Leçon & Résultats

- **`lecon.php`** :  
  - Questions : `nl2br(traiter_versets(h($question['question'])))`  
  - Options : `h($texte_option)`
- **`resultats.php`** :  
  - Question, réponse donnée, bonne réponse : `h(...)` (éventuellement avec `nl2br` où pertinent).

---

## 4. Données existantes (migration)

Les questions et options enregistrées **avant** la correction pouvaient contenir du HTML déjà échappé (ancien `clean_input`). Pour les ramener en texte brut :

1. Exécuter **une seule fois** le script de migration :
   ```bash
   php database/migration_decode_entities_qcm.php
   ```
   ou ouvrir `database/migration_decode_entities_qcm.php` dans le navigateur (accès admin recommandé).

2. Le script applique `html_entity_decode` sur `questions.question` et `options_reponse.texte_option`, puis met à jour les lignes modifiées.

3. Après migration, tout affichage utilise `h()` sans double encodage.

---

## 5. Bonnes pratiques pour éviter que le bug réapparaisse

1. **Ne jamais stocker de HTML échappé** pour les champs « texte brut » (questions, options, etc.). Stocker le texte brut, échapper uniquement à l’affichage.
2. **Utiliser systématiquement `h()`** pour toute donnée utilisateur ou BDD insérée dans du HTML (y compris attributs, `value`, `placeholder`, etc.).
3. **Utiliser `prepare_text_for_storage()`** pour tout champ texte venant de formulaires et destiné à être enregistré en BDD (hors HTML riche type TinyMCE).
4. **Toujours utiliser des requêtes préparées** pour les requêtes SQL contenant des données utilisateur.
5. **Conserver UTF-8 partout** : PHP, MySQL `utf8mb4`, balises `meta charset`, envoi des formulaires.
6. **Tests** : vérifier avec des questions/options contenant  
   `{ } [ ] ( ) " ' < > & / \` et des caractères Unicode (emojis, accents, etc.) → sauvegarde fidèle, affichage correct, pas de double encodage.
7. **Révisions** : lors de tout nouveau formulaire ou nouvel affichage de données utilisateur, vérifier qu’on prépare à l’entrée (`prepare_text_for_storage`) et qu’on échappe à la sortie (`h()`).

---

## 6. Résumé du flux de données (QCM)

```
[Formulaire POST]
       ↓
prepare_text_for_storage()  ← trim, pas d’échappement
       ↓
Requêtes préparées (INSERT/UPDATE)
       ↓
[Base de données – texte brut UTF-8]
       ↓
Lecture (SELECT)
       ↓
h() à l’affichage HTML       ← une seule fois, à la sortie
       ↓
[HTML sûr, caractères corrects]
```

Cette séparation claire **entrée → stockage → sortie** garantit une sauvegarde fidèle, un affichage correct et une protection XSS sans altérer les caractères spéciaux.
