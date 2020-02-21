INSERT INTO conges_config (conf_nom, conf_valeur, conf_groupe, conf_type, conf_commentaire) VALUES ('NOM_APPLI_CONGES', 'Libertempo', '01_Serveur Web', 'texte', 'config_comment_NOM_APPLI_CONGES');
update conges_mail set mail_body = REPLACE(mail_body, 'Libertempo', '__NOM_APPLI_CONGES__');
