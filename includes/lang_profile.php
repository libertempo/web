<?php
/************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2005 (cedric chauvineau)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les 
termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE, 
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation 
dans un but spécifique. Reportez-vous à la Licence Publique Générale GNU pour plus de détails.
Vous devez avoir reçu une copie de la Licence Publique Générale GNU en même temps 
que ce programme ; si ce n'est pas le cas, écrivez à la Free Software Foundation, 
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*************************************************************************************************
This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either 
version 2 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*************************************************************************************************/

if(isset($_REQUEST['session']))
	session_is_valid($_REQUEST['session']);

if(isset($_REQUEST['lang']))
	$lang = $_REQUEST['lang'];
elseif(isset($_SESSION['lang']))
	$lang = $_SESSION['lang'];
else {
    /* Retrieve lang informations from config database */
    $lang_query = "SELECT conf_valeur FROM conges_config WHERE conf_nom='lang';";
    $ReqLang = \includes\SQL::query($lang_query);
    $lang = $ReqLang->fetch_row();
    if ($lang != NULL)
        $lang = $lang[0];
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
