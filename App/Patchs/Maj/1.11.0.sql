DELETE FROM conges_config WHERE conf_nom = 'duree_session' LIMIT 1;
DELETE FROM conges_config WHERE conf_nom = 'where_to_find_user_email' LIMIT 1;
DELETE FROM conges_config WHERE conf_nom = 'affiche_soldes_calendrier' LIMIT 1;
ALTER TABLE `conges_users` DROP `u_see_all`;
ALTER TABLE `conges_users` DROP `u_resp_login`;
UPDATE conges_config SET conf_groupe = '00_libertempo' WHERE conf_nom = 'installed_version' OR conf_nom = 'lang';
