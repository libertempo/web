<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2015 (Wouldsmina)
Copyright (C) 2015 (Prytoegrian)
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
define('ROOT_PATH', '../');
include ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

/*******************************************************************/
// SCRIPT DE MIGRATION DE LA VERSION 1.8 vers 1.9
/*******************************************************************/
include ROOT_PATH .'fonctions_conges.php' ;
include INCLUDE_PATH .'fonction.php';

$PHP_SELF=$_SERVER['PHP_SELF'];

$version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
$lang = (isset($_GET['lang']) ? $_GET['lang'] : (isset($_POST['lang']) ? $_POST['lang'] : "")) ;

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

//suppression des droits de conges
$del_conges_acl = "DELETE FROM conges_groupe_resp WHERE gr_login = 'conges';";
$res_del_conges_acl = \includes\SQL::query($del_conges_acl);

$del_conges_acl = "DELETE FROM conges_groupe_grd_resp WHERE ggr_login = 'conges';";
$res_del_conges_acl=\includes\SQL::query($del_conges_acl);

//modifications des users ayant comme responsable conges
$upd_user_resp = "UPDATE conges_users SET u_resp_login = NULL WHERE u_login = 'conges';";
$res_upd_user_resp=\includes\SQL::query($upd_user_resp);

//suppression des artt de conges
$del_conges_artt = "DROP TABLE conges_artt";
$res_del_conges_artt = \includes\SQL::query($del_conges_artt);

//suppression du user conges
$del_conges_usr="DELETE FROM conges_users WHERE u_login = 'conges';";
$res_del_conges_usr=\includes\SQL::query($del_conges_usr);

$periodeAdditionnelle = "CREATE TABLE heure_additionnelle (
    id_heure INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    login VARBINARY(99) NOT NULL,
    debut INT(11) NOT NULL,
    fin INT(11) NOT NULL,
    duree INT NOT NULL,
    statut INT NOT NULL DEFAULT 0,
    comment VARCHAR(50) NOT NULL DEFAULT '',
    comment_refus VARCHAR(50) NOT NULL DEFAULT '',
    PRIMARY KEY (`id_heure`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$resPeriodeAdditionnelle = $sql->query($periodeAdditionnelle);

$periodeRepos = "CREATE TABLE heure_repos (
    id_heure INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    login VARBINARY(99) NOT NULL,
    debut INT(11) NOT NULL,
    fin INT(11) NOT NULL,
    duree INT NOT NULL,
    statut INT NOT NULL DEFAULT 0,
    comment VARCHAR(50) NOT NULL DEFAULT '',
    comment_refus VARCHAR(50) NOT NULL DEFAULT '',
    PRIMARY KEY (`id_heure`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$resPeriodeRepos = $sql->query($periodeRepos);

// création du solde d'heure
$soldeheure = "ALTER TABLE conges_users
               ADD u_heure_solde INT(11) NOT NULL DEFAULT '0'";
$ressoldeheure = $sql->query($soldeheure);


/* Modification sur conges_config */
$dropUserAfficheCalendrier = 'ALTER TABLE conges_config DROP user_affiche_calendrier';
$sql->query($dropUserAfficheCalendrier);
$dropRespAfficheCalendrier = 'ALTER TABLE conges_config DROP resp_affiche_calendrier';
$sql->query($dropRespAfficheCalendrier);
$dropAfficheCalendrier = 'ALTER TABLE conges_config DROP affiche_groupe_in_calendrier';
$sql->query($dropAfficheCalendrier);
$dropAllGroupe = 'ALTER TABLE conges_config DROP calendrier_select_all_groups';
$sql->query($dropAllGroupe);
$consultCalendrier = 'ALTER TABLE conges_config DROP consult_calendrier_sans_auth';
$sql->query($consultCalendrier);

/* Modification sur see_all : si hr ou admin, oui ; sinon non */
$reqSeeAll = 'SELECT * FROM conges_users;';
$seeAllYes = [];
$seeAllNo = [];
$res = $sql->query($resSeeAll);
while ($data = $res->fetch_array()) {
    if ($data['u_is_admin'] || $data['u_is_hr']) {
        $seeAllYes[] = $data['u_login'];
    } else {
        $seeAllNo[] = $data['u_login'];
    }
}
if (!empty($seeAllYes)) {
    $reqSeeAllYes = 'UPDATE conges_users SET u_see_all = "Y" WHERE u_login IN (' . implode(', ', $seeAllYes) . ')';
    $sql->query($reqSeeAllYes);
}
if (!empty($seeAllNo)) {
    $reqSeeAllNo = 'UPDATE conges_users SET u_see_all = "N" WHERE u_login IN (' . implode(', ', $seeAllNo) . ')';
    $sql->query($reqSeeAllNo);
}

/* Autorisation pour le responsable d'associer employé <> planning */
$reqInsertAssociation = 'INSERT IGNORE INTO `conges_config` (`conf_nom`, `conf_valeur`, `conf_groupe`, `conf_type`, `conf_commentaire`) VALUES ("resp_association_planning", "FALSE", "06_Responsable", "boolean", "config_comment_resp_association_planning")';
$sql->query($reqInsertAssociation);

$sql->getPdoObj()->commit();

// on renvoit à la page mise_a_jour.php (là d'ou on vient)
echo "<a href=\"mise_a_jour.php?etape=2&version=$version&lang=$lang\">upgrade_from_v1.8  OK</a><br>\n";
