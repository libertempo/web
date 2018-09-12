INSERT IGNORE INTO `conges_appli` VALUES ("version_last_maj", "");

DELETE FROM conges_config WHERE conf_nom = 'affiche_bouton_config_pour_admin';
DELETE FROM conges_config WHERE conf_nom = 'affiche_bouton_config_absence_pour_admin';
DELETE FROM conges_config WHERE conf_nom = 'affiche_bouton_config_mail_pour_admin';
