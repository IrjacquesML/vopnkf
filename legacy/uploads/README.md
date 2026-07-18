# Dossier Uploads

Ce dossier contient tous les fichiers téléchargés par les utilisateurs.

## Structure

- `profils/` - Photos de profil des utilisateurs
  - Format: `user_{id}_{timestamp}.{extension}`
  - Extensions autorisées: jpg, jpeg, png, gif, webp
  - Taille maximale: 5 MB

## Sécurité

- Les fichiers PHP sont bloqués via `.htaccess`
- Seules les images sont accessibles
- Chaque sous-dossier contient un `index.php` pour empêcher la navigation
- Les anciennes photos sont automatiquement supprimées lors du téléchargement d'une nouvelle

## Permissions

Assurez-vous que le serveur web a les permissions d'écriture sur ce dossier:
```bash
chmod 755 uploads/
chmod 755 uploads/profils/
```
