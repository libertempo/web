<?php

if (isset($_REQUEST['session'])) {
	session_is_valid($_REQUEST['session']);
}

$lang = 'fr_FR';
putenv('LANG='.$lang); // On modifie la variable d'environnement
$LoadLang = setlocale(LC_ALL, $lang, $lang.".utf8");
$nomDesFichiersDeLangue = 'php-conges'; // Le nom de nos fichiers .mo
bindtextdomain($nomDesFichiersDeLangue, LOCALE_PATH ); // On indique le chemin vers les fichiers .mo
bind_textdomain_codeset($nomDesFichiersDeLangue, 'UTF-8');  // Nos fichiers de langue sont en UTF-8
textdomain($nomDesFichiersDeLangue); // Le nom du domaine par défaut
