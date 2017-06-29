<?php

define('ROOT_PATH', '../');
include ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

/*******************************************************************/
// SCRIPT DE MIGRATION DE LA VERSION 1.7.0 vers 1.8
/*******************************************************************/
include ROOT_PATH .'fonctions_conges.php' ;
include INCLUDE_PATH .'fonction.php';
//include 'fonctions_install.php' ;

$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

$version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
$version = htmlentities($version, ENT_QUOTES | ENT_HTML401);
$lang = (isset($_GET['lang']) ? $_GET['lang'] : (isset($_POST['lang']) ? $_POST['lang'] : "")) ;
$lang = htmlentities($lang, ENT_QUOTES | ENT_HTML401);
if (!in_array($lang, ['fr_FR', 'en_US', 'es_ES'], true)) {
    $lang = '';
}

//supression de conf inutilisées
$del_smtp_from_db="DELETE FROM conges_config WHERE conf_nom = 'serveur_smtp';";
$res_del_smtp_from_db=\includes\SQL::query($del_smtp_from_db);

$del_color_from_db="DELETE FROM conges_config WHERE conf_nom = 'light_grey_bgcolor';";
$res_del_color_from_db=\includes\SQL::query($del_color_from_db);

$del_conf_from_db="DELETE FROM conges_config WHERE conf_nom = 'titre_calendrier';";
$res_del_conf_from_db=\includes\SQL::query($del_conf_from_db);

$del_conf_from_db="DELETE FROM conges_config WHERE conf_nom = 'titre_user_index';";
$res_del_conf_from_db=\includes\SQL::query($del_conf_from_db);

$del_conf_from_db="DELETE FROM conges_config WHERE conf_nom = 'titre_resp_index';";
$res_del_conf_from_db=\includes\SQL::query($del_conf_from_db);

$del_conf_from_db="DELETE FROM conges_config WHERE conf_nom = 'titre_admin_index';";
$res_del_conf_from_db=\includes\SQL::query($del_conf_from_db);

$del_conf_from_db="DELETE FROM conges_config WHERE conf_nom = 'responsable_virtuel';";
$res_del_conf_from_db=\includes\SQL::query($del_conf_from_db);

$del_conf_from_db="DELETE FROM conges_config WHERE conf_nom = 'export_ical_vcal';";
$res_del_conf_from_db=\includes\SQL::query($del_conf_from_db);

$del_conf_from_db="DELETE FROM conges_config WHERE conf_nom = 'stylesheet_file';";
$res_del_conf_from_db=\includes\SQL::query($del_conf_from_db);

// Ajout nouvelle conf
$add_conf="INSERT IGNORE INTO conges_config VALUES ('mail_modif_demande_alerte_resp', 'FALSE', '08_Mail', 'boolean', 'config_comment_mail_modif_demande_alerte_resp');";
$res_add_conf=\includes\SQL::query($add_conf);
$add_conf="INSERT IGNORE INTO conges_config VALUES ('mail_supp_demande_alerte_resp', 'FALSE', '08_Mail', 'boolean', 'config_comment_mail_supp_demande_alerte_resp');";
$res_add_conf=\includes\SQL::query($add_conf);

// modification des sections
$mod_section="UPDATE conges_config SET conf_groupe = '05_Utilisateur' WHERE conf_nom in ('disable_saise_champ_nb_jours_pris','affiche_bouton_calcul_nb_jours_pris','rempli_auto_champ_nb_jours_pris','interdit_saisie_periode_date_passee','interdit_modif_demande');";
$res_mod_section=\includes\SQL::query($mod_section);

//ajout des mails en cas, d'absence non soumise à validation, de modification ou suppression d'une demande.
$ajout_mail="INSERT IGNORE INTO `conges_mail` (`mail_nom`, `mail_subject`, `mail_body`) VALUES ('mail_new_absence_conges', 'APPLI CONGES - Nouvelle absence', ' __SENDER_NAME__ vous informe qu\'il sera absent. Ce type de congés ne necéssite pas de validation. Vous pouvez consulter votre application Libertempo : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique. ');";
$res_ajout_mail=\includes\SQL::query($ajout_mail);

$ajout_mail="INSERT IGNORE INTO `conges_mail` (`mail_nom`, `mail_subject`, `mail_body`) VALUES ('mail_modif_demande_conges', 'APPLI CONGES - Modification demande', ' __SENDER_NAME__ à modifié une demande non traité. Vous pouvez consulter votre application Libertempo : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique. ');";
$res_ajout_mail=\includes\SQL::query($ajout_mail);

$ajout_mail="INSERT IGNORE INTO `conges_mail` (`mail_nom`, `mail_subject`, `mail_body`) VALUES ('mail_supp_demande_conges', 'APPLI CONGES - Suppression demande', ' __SENDER_NAME__ à supprimé une demande non traité. Vous pouvez consulter votre application Libertempo : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique. ');";
$res_ajout_mail=\includes\SQL::query($ajout_mail);

//ical
$ajout_export_ical="INSERT IGNORE INTO conges_config (`conf_nom`, `conf_valeur`, `conf_groupe`, `conf_type`, `conf_commentaire`) VALUES ('export_ical', 'true', '15_ical', 'boolean', 'config_comment_export_ical_vcal');";
$res_ajout_export_ical=\includes\SQL::query($ajout_export_ical);

$ajout_export_ical_salt="INSERT IGNORE INTO conges_config (`conf_nom`, `conf_valeur`, `conf_groupe`, `conf_type`, `conf_commentaire`) VALUES ('export_ical_salt', 'Jao%iT}', '15_ical', 'texte', 'config_comment_export_ical_salt');";
$res_ajout_export_ical_salt=\includes\SQL::query($ajout_export_ical_salt);

//solde conges supérieur à 100
$alter_solde="ALTER TABLE conges_solde_user MODIFY `su_reliquat` DECIMAL(5,2) NOT NULL DEFAULT '0.00', MODIFY `su_solde` DECIMAL(5,2) NOT NULL DEFAULT '0.00', MODIFY `su_nb_an` DECIMAL(5,2) NOT NULL DEFAULT '0.00';";
$res_alter_solde=\includes\SQL::query($alter_solde);

// on renvoit à la page mise_a_jour.php (là d'ou on vient)
echo "Migration depuis v1.7.0 effectuée. <a href=\"mise_a_jour.php?etape=2&version=$version&lang=$lang\">Continuer.</a><br>\n";
