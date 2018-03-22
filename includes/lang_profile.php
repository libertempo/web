<?php

if(isset($_REQUEST['session']))
	session_is_valid($_REQUEST['session']);

if(isset($_REQUEST['lang']))
	$lang = $_REQUEST['lang'];
elseif(isset($_SESSION['lang']))
	$lang = $_SESSION['lang'];
else {
    $sql = \includes\SQL::singleton();
    $config = new \App\Libraries\Configuration($sql);
    $lang = $config->getLangue();
}

putenv('LANG='.$lang); // On modifie la variable d'environnement
$LoadLang = setlocale(LC_ALL, $lang, $lang.".utf8");

if(!$LoadLang)
    {
    $pattern = "/".$lang."/i";
    /* Retrieve lang informations from system */
    $originalLocales = explode(";", setlocale(LC_ALL, 0));
    foreach ($originalLocales as $localeSetting) {
        if (preg_match($pattern, $localeSetting))
            {$LoadLang = setlocale(LC_ALL, $localeSetting);}
        }
    }

/* If we can not find the correct language, load fr... */
if(!$LoadLang)
    {
    /* load default language */
    $LoadLang = setlocale(LC_ALL, 'fr_FR', 'fr_FR.utf8');
    }
/* try another language... */
if(!$LoadLang)
    {$LoadLang = setlocale(LC_ALL, 'en_US', 'en_US.utf8');}
if(!$LoadLang)
    {$LoadLang = setlocale(LC_ALL, 'es_ES', 'es_ES.utf8');}


$nomDesFichiersDeLangue = 'php-conges'; // Le nom de nos fichiers .mo
bindtextdomain($nomDesFichiersDeLangue, LOCALE_PATH ); // On indique le chemin vers les fichiers .mo
    bind_textdomain_codeset($nomDesFichiersDeLangue, 'UTF-8');  // Nos fichiers de langue sont en UTF-8 
textdomain($nomDesFichiersDeLangue); // Le nom du domaine par défaut


?>
