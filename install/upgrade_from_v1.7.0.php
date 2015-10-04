<?php
/*************************************************************************************************
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

define('_PHP_CONGES', 1);
define('ROOT_PATH', '../');
include ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

/*******************************************************************/
// SCRIPT DE MIGRATION DE LA VERSION 1.7.0 vers 1.7.1
/*******************************************************************/
include ROOT_PATH .'fonctions_conges.php' ;
include INCLUDE_PATH .'fonction.php';
include 'fonctions_install.php' ;

$PHP_SELF=$_SERVER['PHP_SELF'];

//$DEBUG=TRUE;

$version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
$lang = (isset($_GET['lang']) ? $_GET['lang'] : (isset($_POST['lang']) ? $_POST['lang'] : "")) ;

//retrait de la conf SMTP
$del_smtp_from_db="DELETE FROM conges_config WHERE conf_nom = 'serveur_smtp';";
$res_del_smtp_from_db=SQL::query($del_smtp_from_db);

//retrait de la conf couleur 
$del_color_from_db="DELETE FROM conges_config WHERE conf_nom = 'light_grey_bgcolor';";
$res_del_color_from_db=SQL::query($del_color_from_db);

//supression de conf inutilisées
$del_conf_from_db="DELETE FROM conges_config WHERE conf_nom = 'titre_calendrier';";
$res_del_conf_from_db=SQL::query($del_conf_from_db);

$del_conf_from_db="DELETE FROM conges_config WHERE conf_nom = 'titre_user_index';";
$res_del_conf_from_db=SQL::query($del_conf_from_db);

$del_conf_from_db="DELETE FROM conges_config WHERE conf_nom = 'titre_resp_index';";
$res_del_conf_from_db=SQL::query($del_conf_from_db);

$del_conf_from_db="DELETE FROM conges_config WHERE conf_nom = 'titre_admin_index';";
$res_del_conf_from_db=SQL::query($del_conf_from_db);

$del_conf_from_db="DELETE FROM conges_config WHERE conf_nom = 'responsable_virtuel';";
$res_del_conf_from_db=SQL::query($del_conf_from_db);

$del_conf_from_db="DELETE FROM conges_config WHERE conf_nom = 'export_ical_vcal';";
$res_del_conf_from_db=SQL::query($del_conf_from_db);

$del_conf_from_db="DELETE FROM conges_config WHERE conf_nom = 'stylesheet_file';";
$res_del_conf_from_db=SQL::query($del_conf_from_db);


// modification des sections

$mod_section="UPDATE conges_config SET conf_groupe = '05_Utilisateur' WHERE conf_nom in ('disable_saise_champ_nb_jours_pris','affiche_bouton_calcul_nb_jours_pris','rempli_auto_champ_nb_jours_pris','interdit_saisie_periode_date_passee','interdit_modif_demande');";
$res_mod_section=SQL::query($mod_section);

//ajout du type de mail en cas d'absence non soumise à validation.
$ajout_mail_new_absence="INSERT IGNORE INTO `conges_mail` (`mail_nom`, `mail_subject`, `mail_body`) VALUES ('mail_prem_valid_conges', 'APPLI CONGES - Nouvelle absence', ' __SENDER_NAME__ vous informe qu\'il sera absent. Ce type de congés ne necéssite pas de validation. Vous pouvez consulter votre application Libertempo : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique. ');";
$res_ajout_mail_new_absence=SQL::query($ajout_mail_new_absence);

$ajout_export_ical="INSERT IGNORE INTO conges_config (`conf_nom`, `conf_valeur`, `conf_groupe`, `conf_type`, `conf_commentaire`) VALUES ('export_ical', 'true', '15_ical', 'boolean', 'config_comment_export_ical_vcal');";
$res_ajout_export_ical=SQL::query($ajout_export_ical);

$ajout_export_ical_salt="INSERT IGNORE INTO conges_config (`conf_nom`, `conf_valeur`, `conf_groupe`, `conf_type`, `conf_commentaire`) VALUES ('export_ical_salt', 'Jao%iT}', '15_ical', 'texte', 'config_comment_export_ical_salt');";
$res_ajout_export_ical_salt=SQL::query($ajout_export_ical_salt);

// on renvoit à la page mise_a_jour.php (là d'ou on vient)
echo "<a href=\"mise_a_jour.php?etape=3&version=$version&lang=$lang\">upgrade_from_v1.7.0  OK</a><br>\n";
