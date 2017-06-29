<?php

define('ROOT_PATH', '../');
include ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

/*******************************************************************/
// SCRIPT DE MIGRATION DE LA VERSION 1.8.1 vers 1.9
/*******************************************************************/
include ROOT_PATH .'fonctions_conges.php' ;
include INCLUDE_PATH .'fonction.php';

$PHP_SELF=$_SERVER['PHP_SELF'];

$version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
$lang = (isset($_GET['lang']) ? $_GET['lang'] : (isset($_POST['lang']) ? $_POST['lang'] : "")) ;
$lang = htmlentities($lang, ENT_QUOTES | ENT_HTML401);
if (!in_array($lang, ['fr_FR', 'en_US', 'es_ES'], true)) {
    $lang = '';
}

$sql = \includes\SQL::singleton();
$sql->getPdoObj()->begin_transaction();

$ssoad="UPDATE conges_config SET conf_type = 'enum=dbconges/ldap/CAS/SSO' WHERE conf_nom = 'how_to_connect_user';";
$res_ssoad = $sql->query($ssoad);

/* Modification sur conges_users */
$dropIndexUser = 'ALTER TABLE conges_users DROP INDEX u_login;';
$sql->query($dropIndexUser);
$addPlanningUser = 'ALTER TABLE conges_users ADD planning_id INT(11) UNSIGNED NOT NULL';
$sql->query($addPlanningUser);
$addIndexPlanningUser = 'ALTER TABLE conges_users ADD INDEX planning_id (planning_id);';
$sql->query($addIndexPlanningUser);

/* Création du planning et des créneaux */
$addPlanning = 'CREATE TABLE `planning` (
  `planning_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL DEFAULT "",
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
$sql->query($addPlanning);

$addPlanningCreneau = 'CREATE TABLE `planning_creneau` (
  `creneau_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `planning_id` INT(11) UNSIGNED NOT NULL,
  `jour_id` TINYINT(3) UNSIGNED NOT NULL,
  `type_semaine` TINYINT(3) UNSIGNED NOT NULL,
  `type_periode` TINYINT(3) UNSIGNED NOT NULL,
  `debut` INT(11) UNSIGNED NOT NULL,
  `fin` INT(11) UNSIGNED NOT NULL,
  KEY `planning_id` (`planning_id`,`type_semaine`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8';
$sql->query($addPlanningCreneau);

//suppression des artt de conges
$del_conges_artt = "DROP TABLE conges_artt";
$res_del_conges_artt = \includes\SQL::query($del_conges_artt);

$periodeAdditionnelle = "CREATE TABLE heure_additionnelle (
    id_heure INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    login VARBINARY(99) NOT NULL,
    debut INT(11) NOT NULL,
    fin INT(11) NOT NULL,
    duree INT NOT NULL,
    type_periode int(3) NOT NULL,
    statut INT NOT NULL DEFAULT 0,
    comment VARCHAR(250) NOT NULL DEFAULT '',
    comment_refus VARCHAR(250) NOT NULL DEFAULT '',
    PRIMARY KEY (`id_heure`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$resPeriodeAdditionnelle = $sql->query($periodeAdditionnelle);

$periodeRepos = "CREATE TABLE heure_repos (
    id_heure INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    login VARBINARY(99) NOT NULL,
    debut INT(11) NOT NULL,
    fin INT(11) NOT NULL,
    duree INT NOT NULL,
    type_periode int(3) NOT NULL,
    statut INT NOT NULL DEFAULT 0,
    comment VARCHAR(250) NOT NULL DEFAULT '',
    comment_refus VARCHAR(250) NOT NULL DEFAULT '',
    PRIMARY KEY (`id_heure`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$resPeriodeRepos = $sql->query($periodeRepos);

// création du solde d'heure
$soldeheure = "ALTER TABLE conges_users
               ADD u_heure_solde INT(11) NOT NULL DEFAULT '0'";
$ressoldeheure = $sql->query($soldeheure);

//augmentation taille commentaires
$tailleComm = "ALTER TABLE conges_periode
                CHANGE p_commentaire p_commentaire VARCHAR(250)";
$restailleComm = $sql->query($tailleComm);

$tailleCommRefus = "ALTER TABLE conges_periode
                CHANGE p_motif_refus p_motif_refus VARCHAR(250)";
$restailleCommRefus = $sql->query($tailleCommRefus);

/* Modification sur conges_config */
$dropUserAfficheCalendrier = "DELETE FROM conges_config WHERE conf_nom = 'user_affiche_calendrier'";
$sql->query($dropUserAfficheCalendrier);
$dropRespAfficheCalendrier = "DELETE FROM conges_config WHERE conf_nom = 'resp_affiche_calendrier'";
$sql->query($dropRespAfficheCalendrier);
$dropAfficheCalendrier = "DELETE FROM conges_config WHERE conf_nom = 'affiche_groupe_in_calendrier'";
$sql->query($dropAfficheCalendrier);
$dropAllGroupe = "DELETE FROM conges_config WHERE conf_nom = 'calendrier_select_all_groups'";
$sql->query($dropAllGroupe);
$consultCalendrier = "DELETE FROM conges_config WHERE conf_nom = 'consult_calendrier_sans_auth'";
$sql->query($consultCalendrier);

/* Modification sur see_all : si hr ou admin, oui ; sinon non */
$reqSeeAll = 'SELECT * FROM conges_users;';
$seeAllYes = [];
$seeAllNo = [];
$res = $sql->query($reqSeeAll);
while ($data = $res->fetch_array()) {
    if ($data['u_is_admin'] || $data['u_is_hr']) {
        $seeAllYes[] = $data['u_login'];
    } else {
        $seeAllNo[] = $data['u_login'];
    }
}
if (!empty($seeAllYes)) {
    $reqSeeAllYes = "UPDATE conges_users SET u_see_all = 'Y' WHERE u_login IN ('" . implode('\',\' ', $seeAllYes) . "')";
    $sql->query($reqSeeAllYes);
}
if (!empty($seeAllNo)) {
    $reqSeeAllNo = 'UPDATE conges_users SET u_see_all = "N" WHERE u_login IN (' . implode(', ', $seeAllNo) . ')';
    $sql->query($reqSeeAllNo);
}

/* Autorisation pour le responsable d'associer employé <> planning */
$reqInsertAssociation = 'INSERT IGNORE INTO `conges_config` (`conf_nom`, `conf_valeur`, `conf_groupe`, `conf_type`, `conf_commentaire`) VALUES ("resp_association_planning", "FALSE", "06_Responsable", "boolean", "config_comment_resp_association_planning")';
$sql->query($reqInsertAssociation);
/* Booléen qui indique si l'établissement a besoin des fonctionnalités de gestion des heures additionnelles et de repos */
$reqInsert = 'INSERT IGNORE INTO `conges_config` VALUES ("gestion_heures", "TRUE", "12_Fonctionnement de l\'Etablissement", "boolean", "config_comment_gestion_heures")';
$sql->query($reqInsert);

$sql->getPdoObj()->commit();

// on renvoit à la page mise_a_jour.php (là d'ou on vient)
echo "Migration depuis v1.8.1 effectuée. <a href=\"mise_a_jour.php?etape=2&version=$version&lang=$lang\">Continuer.</a><br>\n";
