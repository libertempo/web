<?php
/**
**	@author Benjamin Husson
**
**	-------------------------Fichier de configuration du serveur CAS-------------------------
**
**	CAS pour Système d'Authentification  Centralisé http://www.yale.edu/tp/cas/
** 
**  Pre-requis pour l'utilisation du mode d'authentification CAS (utilisation de la librairie phpcas et de ses dépendances)
**	http://esup-phpcas.sourceforge.net/requirements.html
**
**	Pour utiliser le système d'authentification CAS le paramêtre de configuration de php_conges "how_to_connect_user"
**	doit être positionné a "CAS"
**	
** 	ATTENTION : Un utilisateur ne peut se connecter en utilisant CAS uniquement si le login utilisé par CAS 
**	est identique au champ u_login de la table conges_users de la bdd.
**	REMARQUE : CAS s'appuyant souvent sur un annuaire LDAP, l'utilisation de CAS ne rentre pas en conflit avec l'utilisation de LDAP 
**	pour gerer la création d'utilisateurs en mode Admin.
**	(il est même recommandé d'utiliser l'authentification CAS en parrallele avec la création d'utilisateurs en mode ldap.)
**	De cette façon les logins associés aux utilisateurs de php_conges seront identique à ceux utilisés par CAS.
**
**	Fichier de configuration du système d'authentification CAS. 
**	
**	$config_CAS_host = hostName				adresse du serveur CAS
**	$config_CAS_portNumber = PortNumber		numero de port sur lequel tourne le service (par défaut 443)
**	$config_CAS_URI = "" 					vide par défaut, c'est la sous adresse pour le service CAS
**/

defined( '_PHP_CONGES' ) or die( 'Restricted access' );


$config_CAS_host = "localhost";		//adresse http
$config_CAS_portNumber = 443; 	//entier
$config_CAS_URI = "";		//chemin relatif (peut être vide)
$config_CAS_CACERT = ""; //indispensable en production
