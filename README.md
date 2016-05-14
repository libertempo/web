Libertempo Fork de php_conges

	Application web interactive de gestion des congés du personnel d'un service.

	** Libertempo se veut très paramètrable afin de fournir ou non diverses fonctionnalités aux utilisateurs. **
	** Libertempo est multi-langues. **


# INSTALL

Create a database for this application with a user who can access it and modify informations on it (grant access).

Copy and edits proprely this files :
	cfg/dconfig_CAS_new.php to cfg/config_CAS.php
	cfg/config_ldap_new.php to cfg/config_ldap.php
	cfg/env_new.php         to cfg/env.php


Then copy your Libertempo directory to your Apache DocumentRoot
Open a browser and go to http://[server address]/[Libertempo directory]/install

Default user : "admin" / password : "responsable"
Default manager : "conges" / password : "conges"

----
# UPGRADE TO 1.9
Simply copy `cfg/env_new.php` to `cfg/env.php`

----
# UPGRADE FROM v1.5

Copy (and edit):
	dbconnect.php 		to 	cfg/dbconnect.php
	define_new.php		to	define.php


Go to your [Libertempo] URL, in /install subdirectory to upgrade your application.

---
# After installation

 - About security

Do not forget to place .htaccess (deny from all) file into your install subdirectory !
	http://httpd.apache.org/docs/2.2/howto/htaccess.html

----
CONFIGURE
Change the template if you want in this file :
	Copy the original files in template/reboot directory to a new one and change the value in define.php.
