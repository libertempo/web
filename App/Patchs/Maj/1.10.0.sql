# suppression de la variable print_disable_users de la bdd
DELETE FROM conges_config WHERE conf_nom = 'print_disable_users';

# suppression de loption calcul de nombre de jours, par défaut cest automatique
DELETE FROM conges_config WHERE conf_nom = 'affiche_bouton_calcul_nb_jours_pris';
DELETE FROM conges_config WHERE conf_nom = 'rempli_auto_champ_nb_jours_pris';

# suppression de loption de gestion des goupes
DELETE FROM conges_config WHERE conf_nom = 'gestion_groupes';

# Ajout des champs de lutilisateur requis pour lAPI
ALTER TABLE `conges_users`
    ADD `date_inscription` DATETIME NOT NULL DEFAULT NOW(),
    ADD `token` VARCHAR(100) NOT NULL DEFAULT "",
    ADD `date_last_access` DATETIME NOT NULL,
    ADD INDEX `token` (`token`);

# Ajout du token dinstance
INSERT IGNORE INTO `conges_appli` VALUES ("token_instance", "");

# Modification de tous les mots de passe dutilisateurs non db_conges (md5(none))
UPDATE `conges_users` SET u_passwd = "334c4a4c42fdb79d7ebc3e73b517e6f8" where u_passwd = "none";

DELETE FROM conges_config WHERE conf_nom = 'disable_saise_champ_nb_jours_pris';
