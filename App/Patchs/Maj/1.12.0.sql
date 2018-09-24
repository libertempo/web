INSERT INTO `conges_appli` VALUES ("version_last_maj", "");
DELETE FROM `conges_users` WHERE u_login = 'admin'; 
UPDATE `conges_users` SET `u_is_admin`='Y' WHERE u_is_hr = 'Y'; 