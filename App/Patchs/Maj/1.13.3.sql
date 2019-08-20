 DELETE FROM `conges_config` WHERE conf_nom = 'double_validation_conges';
 UPDATE `conges_config` SET conf_type='enum=dbconges/ldap/cas/sso' WHERE conf_nom = 'how_to_connect_user';
