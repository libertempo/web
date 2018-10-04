INSERT IGNORE INTO `conges_appli` VALUES ("version_last_maj", "");

DELETE FROM conges_config WHERE conf_nom = 'affiche_bouton_config_pour_admin';
DELETE FROM conges_config WHERE conf_nom = 'affiche_bouton_config_absence_pour_admin';
DELETE FROM conges_config WHERE conf_nom = 'affiche_bouton_config_mail_pour_admin';

INSERT INTO `conges_appli` VALUES ("version_last_maj", "");

DELETE FROM `conges_users` WHERE u_login = 'admin'; 
UPDATE `conges_users` SET `u_is_admin`='Y' WHERE u_is_hr = 'Y'; 