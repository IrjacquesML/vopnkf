<?php
// Empêcher l'accès direct au dossier profils
header('HTTP/1.0 403 Forbidden');
exit('Accès interdit');
?>
