# Documentation - Détection Automatique des Versets Bibliques

## 📖 Vue d'ensemble

Le système de détection automatique des versets bibliques permet de convertir automatiquement les références bibliques dans le texte des leçons en liens cliquables. Lorsqu'un utilisateur clique sur une référence, le texte complet du verset s'affiche dans une modale élégante sans interrompre la lecture.

## ✨ Fonctionnalités

### Détection automatique
- **Format supporté** : `Livre Chapitre:Verset` (ex: Jean 3:16)
- **Livres numérotés** : `1 Jean 1:9`, `2 Corinthiens 5:17`
- **Caractères accentués** : `Éphésiens 2:8`, `Hébreux 4:12`
- **Plages de versets** : `Romains 3:23-24`
- **Unicode complet** : Support de tous les caractères français

### Interface utilisateur
- **Clic simple** : Un clic sur la référence ouvre la modale
- **Modale élégante** : Design spirituel avec animations fluides
- **Indicateur de chargement** : Spinner animé pendant la récupération
- **Gestion d'erreur** : Messages clairs si le verset n'est pas trouvé
- **Accessibilité** : Support clavier, navigation Tab, touche Échap

## 🔧 Implémentation technique

### 1. Détection PHP (lecon.php)

```php
function traiter_versets($texte) {
    // Pattern regex complet pour détecter les références bibliques
    $pattern = '/(?<![>])(\b(\d?\s*[A-ZÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸ][a-zàâäæçéèêëïîôœùûüÿ]+(?:\s+[A-ZÀÂÄÆÇÉÈÊËÏÎÔŒÙÛÜŸ][a-zàâäæçéèêëïîôœùûüÿ]+)*)\s+(\d+):(\d+(?:-\d+)?)\b)(?![^<]*>)/u';
    
    $texte_traite = preg_replace_callback($pattern, function($matches) {
        $reference = $matches[1];
        $livre = trim($matches[2]);
        $chapitre = $matches[3];
        $verset = $matches[4];
        
        return '<span class="bible-verse" data-reference="' . htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') . '" 
                data-livre="' . htmlspecialchars($livre, ENT_QUOTES, 'UTF-8') . '" 
                data-chapitre="' . htmlspecialchars($chapitre, ENT_QUOTES, 'UTF-8') . '" 
                data-verset="' . htmlspecialchars($verset, ENT_QUOTES, 'UTF-8') . '">' 
                . htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') . '</span>';
    }, $texte);
    
    return $texte_traite;
}
```

### 2. API REST (api/get_verset.php)

```php
// Récupération du verset depuis la base de données
$query = "SELECT texte, version FROM versets WHERE reference = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $reference);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($verset_data = mysqli_fetch_assoc($result)) {
    echo json_encode([
        'success' => true,
        'reference' => $reference,
        'texte' => $verset_data['texte'],
        'version' => $verset_data['version']
    ], JSON_UNESCAPED_UNICODE);
}
```

### 3. Interface JavaScript (assets/js/script.js)

```javascript
// Gestion des clics sur les versets
verseElements.forEach(function(element) {
    element.addEventListener('click', function() {
        const reference = this.getAttribute('data-reference');
        
        // Afficher l'indicateur de chargement
        verseReference.innerHTML = '<span class="verse-loading"></span>' + reference;
        verseText.innerHTML = '<div class="verse-loading"></div>Chargement...';
        modal.style.display = 'block';
        
        // Récupérer le verset via AJAX
        fetch(`../../api/get_verset.php?reference=${encodeURIComponent(reference)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    verseText.innerHTML = `<span class="icon-bible"></span> ${data.texte}`;
                }
            });
    });
});
```

## 🎨 Design et styles

### Modale spirituelle
- **Arrière-plan** : Flou avec transparence
- **Contenu** : Dégradé spirituel blanc-taupe
- **Bordures** : Dorées subtiles
- **Animations** : Fade-in et slide-in fluides
- **Responsive** : Adaptation mobile/tablette

### États interactifs
- **Hover** : Légère augmentation d'échelle
- **Click** : Effet de profondeur avec ombre dorée
- **Loading** : Spinner doré animé
- **Success** : Animation de fondu

## 📱 Accessibilité

### Navigation clavier
- **Tab** : Navigation entre éléments focusables
- **Shift+Tab** : Navigation inverse
- **Échap** : Fermeture de la modale
- **Enter** : Activation des versets

### Support visuel
- **Contraste élevé** : Mode haute visibilité
- **Réduction mouvement** : Respect des préférences utilisateur
- **Focus visible** : Indicateurs clairs de focus

## 🗄️ Base de données

### Table `versets`
```sql
CREATE TABLE versets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(100) NOT NULL,
    livre VARCHAR(50) NOT NULL,
    chapitre INT NOT NULL,
    verset VARCHAR(20) NOT NULL,
    texte TEXT NOT NULL,
    version VARCHAR(20) DEFAULT 'LSG',
    UNIQUE KEY unique_reference (reference)
);
```

### Versets inclus
- Jean 3:16
- Romains 3:23
- Romains 6:23
- Éphésiens 2:8
- 1 Jean 1:9
- Matthieu 11:28
- Psaumes 23:1
- Philippiens 4:13

## 🚀 Utilisation

### Dans les leçons
```php
<div class="content-text">
    <?php echo traiter_versets($lecon['contenu']); ?>
</div>
```

### Dans les questions
```php
<p class="question-text">
    <?php echo nl2br(traiter_versets($question['question'])); ?>
</p>
```

## 🔧 Personnalisation

### Ajouter des versets
```sql
INSERT INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Genèse 1:1', 'Genèse', 1, 1, 'Au commencement, Dieu créa les cieux et la terre.', 'LSG');
```

### Modifier le pattern de détection
Le pattern regex peut être ajusté dans `traiter_versets()` pour supporter des formats supplémentaires.

### Personnaliser le style
Les classes CSS peuvent être modifiées dans `assets/css/styles.css` :
- `.bible-verse` : Style des références cliquables
- `.modal` : Style de la modale
- `.verse-loading` : Style de l'indicateur de chargement

## 🧪 Tests

### Page de démonstration
Visitez `demo_versets.php` pour tester :
- Différents formats de références
- Comportement de la modale
- Gestion des erreurs
- Accessibilité

### Références testées
- Jean 3:16 ✓
- 1 Jean 1:9 ✓
- Éphésiens 2:8 ✓
- Hébreux 4:12 ✓
- Romains 3:23-24 ✓

## 🐛 Dépannage

### Problèmes courants
1. **Verset non trouvé** : Vérifiez la base de données
2. **Détection échouée** : Vérifiez le format de la référence
3. **Modale ne s'ouvre pas** : Vérifiez la console JavaScript
4. **Style incorrect** : Vérifiez le chargement des CSS

### Debug
- Activer les erreurs PHP : `error_reporting(E_ALL);`
- Console navigateur : F12 → Network → XHR
- Vérifier les attributs `data-*` sur les versets

## 📈 Évolutions possibles

### Fonctionnalités futures
- **Recherche de versets** : Barre de recherche intégrée
- **Partage** : Partager des versets sur les réseaux
- **Notes personnelles** : Ajouter des notes aux versets
- **Versions multiples** : Choisir entre différentes traductions
- **Mode sombre** : Thème nocturne pour la lecture

### Améliorations techniques
- **Cache** : Mise en cache des versets fréquents
- **Offline** : Support hors ligne avec Service Worker
- **API externe** : Intégration avec des API bibliques
- **Audio** : Lecture audio des versets

---

**Développé par ML DATA | VOP Études Bibliques par Correspondance**
