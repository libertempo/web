#
# Add an int PK on config_appli
#
ALTER TABLE conges_appli MODIFY appli_variable varchar(100) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '';
ALTER TABLE conges_appli DROP PRIMARY KEY;
ALTER TABLE conges_appli ADD appli_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;
ALTER TABLE conges_appli ADD INDEX appli_variable (appli_variable);
#
# Add an int PK on config_appli
#
ALTER TABLE conges_config MODIFY conf_nom varchar(100) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '';
ALTER TABLE conges_config DROP PRIMARY KEY;
ALTER TABLE conges_config ADD config_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;
ALTER TABLE conges_config ADD INDEX conf_nom (conf_nom);
#
# Add an int PK on conges_echange_rtt
#
ALTER TABLE conges_echange_rtt MODIFY e_login varbinary(99) NOT NULL DEFAULT '';
ALTER TABLE conges_echange_rtt DROP PRIMARY KEY;
ALTER TABLE conges_echange_rtt ADD e_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;
ALTER TABLE conges_echange_rtt ADD INDEX login_date (e_login, e_date_jour);
#
# Add an int PK on conges_jours_feries
#
ALTER TABLE conges_jours_feries MODIFY jf_date date NOT NULL DEFAULT '0000-00-00';
ALTER TABLE conges_jours_feries DROP PRIMARY KEY;
ALTER TABLE conges_jours_feries ADD jf_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;
ALTER TABLE conges_jours_feries ADD INDEX jf_date (jf_date);
#
# Add an int PK on conges_periode
#
ALTER TABLE conges_periode MODIFY p_num int(5) unsigned NOT NULL;
ALTER TABLE conges_periode DROP PRIMARY KEY;
ALTER TABLE conges_periode ADD p_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;
ALTER TABLE conges_periode ADD INDEX p_num (p_num);
#
# Add an int PK on conges_jours_fermeture
#
ALTER TABLE conges_jours_fermeture ADD id INT NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;

#
# Add an int PK on conges_solde_edition
#
ALTER TABLE conges_solde_edition ADD id INT NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;

#
# Add an int PK on conges_mail
#
ALTER TABLE conges_mail ADD mail_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;
#
# Add an int PK on conges_solde_user
#
ALTER TABLE conges_solde_user MODIFY su_login varbinary(99) NOT NULL DEFAULT '';
ALTER TABLE conges_solde_user DROP PRIMARY KEY;
ALTER TABLE conges_solde_user ADD su_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;
ALTER TABLE conges_solde_user ADD INDEX login_abs (su_login, su_abs_id);
#
# Add an int PK on conges_users
#
ALTER TABLE conges_users MODIFY u_login varbinary(99) NOT NULL DEFAULT '';
ALTER TABLE conges_users DROP PRIMARY KEY;
ALTER TABLE conges_users ADD u_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;
ALTER TABLE conges_users ADD INDEX u_login (u_login);
