<?php

define('ROOT_PATH', '../');
include ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

/*******************************************************************/
// SCRIPT DE MIGRATION DE LA VERSION 1.9 vers 1.10
/*******************************************************************/
include ROOT_PATH .'fonctions_conges.php' ;
include INCLUDE_PATH .'fonction.php';

$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

$version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
$version = htmlentities($version, ENT_QUOTES | ENT_HTML401);
$lang = (isset($_GET['lang']) ? $_GET['lang'] : (isset($_POST['lang']) ? $_POST['lang'] : "")) ;
$lang = htmlentities($lang, ENT_QUOTES | ENT_HTML401);
if (!in_array($lang, ['fr_FR', 'en_US', 'es_ES'], true)) {
    $lang = '';
}

$sql = \includes\SQL::singleton();
$sql->getPdoObj()->begin_transaction();

//suppression de la variable print_disable_users de la bdd
$del_conf_from_db="DELETE FROM conges_config WHERE conf_nom = 'print_disable_users';";
$res_del_conf_from_db=\includes\SQL::query($del_conf_from_db);

//suppression de l'option calcul de nombre de jours, par défaut c'est automatique
$del_config_db="DELETE FROM conges_config WHERE conf_nom = 'affiche_bouton_calcul_nb_jours_pris';";
$res_del_config_from_db=\includes\SQL::query($del_config_db);

$del_config_db="DELETE FROM conges_config WHERE conf_nom = 'rempli_auto_champ_nb_jours_pris';";
$res_del_config_from_db=\includes\SQL::query($del_config_db);
/* Modification sur conges_users */
$dropIndexUser = 'ALTER TABLE conges_users DROP INDEX u_login;';
$sql->query($dropIndexUser);
$addPlanningUser = 'ALTER TABLE conges_users ADD planning_id INT(11) UNSIGNED NOT NULL';
$sql->query($addPlanningUser);
$addIndexPlanningUser = 'ALTER TABLE conges_users ADD INDEX planning_id (planning_id);';
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

$soldeheure = "ALTER TABLE conges_users
               ADD u_heure_solde INT(11) NOT NULL DEFAULT '0'";
//augmentation taille commentaires
$tailleComm = "ALTER TABLE conges_periode
                CHANGE p_commentaire p_commentaire VARCHAR(250)";
$restailleComm = $sql->query($tailleComm);

$tailleCommRefus = "ALTER TABLE conges_periode
                CHANGE p_motif_refus p_motif_refus VARCHAR(250)";
$restailleCommRefus = $sql->query($tailleCommRefus);

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
    $sql->query($reqSeeAllYes);
}
if (!empty($seeAllNo)) {
    $reqSeeAllNo = 'UPDATE conges_users SET u_see_all = "N" WHERE u_login IN (' . implode(', ', $seeAllNo) . ')';
    $sql->query($reqSeeAllNo);
}

$reqInsertAssociation = 'INSERT IGNORE INTO `conges_config` (`conf_nom`, `conf_valeur`, `conf_groupe`, `conf_type`, `conf_commentaire`) VALUES ("resp_association_planning", "FALSE", "06_Responsable", "boolean", "config_comment_resp_association_planning")';
$sql->query($reqInsertAssociation);
/* Booléen qui indique si l'établissement a besoin des fonctionnalités de gestion des heures additionnelles et de repos */
$reqInsert = 'INSERT IGNORE INTO `conges_config` VALUES ("gestion_heures", "TRUE", "12_Fonctionnement de l\'Etablissement", "boolean", "config_comment_gestion_heures")';
$sql->query($reqInsert);

/* Ajout des champs de l'utilisateur requis pour l'API */
$alterApiUser = 'ALTER TABLE `conges_users`
    ADD `date_inscription` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD `token` VARCHAR(100) NOT NULL DEFAULT "",
    ADD INDEX `token` (`token`)';
$sql->query($alterApiUser);

/* Ajout du token d'instance */
$addApiToken = 'INSERT IGNORE INTO `conges_appli` VALUES ("token_instance", "")';
$sql->query($addApiToken);

$sql->getPdoObj()->commit();

$del_config_db="DELETE FROM conges_config WHERE conf_nom = 'disable_saise_champ_nb_jours_pris';";
$res_del_config_from_db=\includes\SQL::query($del_config_db);
