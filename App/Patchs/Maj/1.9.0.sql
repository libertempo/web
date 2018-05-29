# Utilisation AD
UPDATE conges_config SET conf_type = 'enum=dbconges/ldap/CAS/SSO' WHERE conf_nom = 'how_to_connect_user';

# Modification sur conges_users
ALTER TABLE conges_users DROP INDEX u_login;
ALTER TABLE conges_users ADD planning_id INT(11) UNSIGNED NOT NULL;
ALTER TABLE conges_users ADD INDEX planning_id (planning_id);

# Création du planning et des créneaux
CREATE TABLE `planning` (
  `planning_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL DEFAULT "",
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `planning_creneau` (
  `creneau_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `planning_id` INT(11) UNSIGNED NOT NULL,
  `jour_id` TINYINT(3) UNSIGNED NOT NULL,
  `type_semaine` TINYINT(3) UNSIGNED NOT NULL,
  `type_periode` TINYINT(3) UNSIGNED NOT NULL,
  `debut` INT(11) UNSIGNED NOT NULL,
  `fin` INT(11) UNSIGNED NOT NULL,
  KEY `planning_id` (`planning_id`,`type_semaine`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# suppression des artt de conges
DROP TABLE conges_artt;

# création tables heure et solde
CREATE TABLE heure_additionnelle (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE heure_repos (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE conges_users
               ADD u_heure_solde INT(11) NOT NULL DEFAULT '0';

# augmentation de la taille des commentaires
ALTER TABLE conges_periode
                CHANGE p_commentaire p_commentaire VARCHAR(250);
ALTER TABLE conges_periode
                CHANGE p_motif_refus p_motif_refus VARCHAR(250);

# modification de la configuration
DELETE FROM conges_config WHERE conf_nom = 'user_affiche_calendrier';
DELETE FROM conges_config WHERE conf_nom = 'resp_affiche_calendrier';
DELETE FROM conges_config WHERE conf_nom = 'affiche_groupe_in_calendrier';
DELETE FROM conges_config WHERE conf_nom = 'calendrier_select_all_groups';
DELETE FROM conges_config WHERE conf_nom = 'consult_calendrier_sans_auth';

# Modification sur see_all : si hr ou admin, oui ; sinon non
UPDATE conges_users SET u_see_all = 'Y' WHERE u_is_admin = 'Y' OR u_is_hr = 'Y';
UPDATE conges_users SET u_see_all = "N" WHERE u_is_admin = 'N' AND u_is_hr = 'N';

# Autorisation pour le responsable dassocier employé <> planning
INSERT IGNORE INTO `conges_config` (`conf_nom`, `conf_valeur`, `conf_groupe`, `conf_type`, `conf_commentaire`) VALUES ("resp_association_planning", "FALSE", "06_Responsable", "boolean", "config_comment_resp_association_planning");


# Booléen qui indique si létablissement a besoin # des fonctionnalités de gestion des heures
# additionnelles et de repos
INSERT IGNORE INTO `conges_config` VALUES ("gestion_heures", "TRUE", "12_Fonctionnement de l\'Etablissement", "boolean", "config_comment_gestion_heures");
