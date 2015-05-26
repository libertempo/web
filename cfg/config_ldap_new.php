<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2005 (cedric chauvineau)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les 
termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE, 
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation 
dans un but spécifique. Reportez-vous à la Licence Publique Générale GNU pour plus de détails.
Vous devez avoir reçu une copie de la Licence Publique Générale GNU en même temps 
que ce programme ; si ce n'est pas le cas, écrivez à la Free Software Foundation, 
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*************************************************************************************************
This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either 
version 2 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*************************************************************************************************/



//  CONFIG ACCES AU SERVEUR LDAP (optionnelle)
//----------------------------------------------

/*****************************************************************/
/*			PARAMATRAGE LDAP
Ce fichier est utilisisé par PHP_CONGES SEULEMENT SI vous avez activé les options 
concernants le ldap dans la configuration de php_conges :
	where_to_find_user_email="ldap" ;
	how_to_connect_user="ldap";

=> vous devez configurer ce fichier pour que les requêtes LDAP 
s'effectuent sans problème.

/!\ quelques notions de LDAP sont nécessaires à la bonne compréhension
de la chose. Pour dégrossir ces notions, le web sera votre ami.
Notions de base : http://www.commentcamarche.net/ldap/ldapintro.php3
http://www-sop.inria.fr/semir/personnel/Laurent.Mirtain/ldap-livre.html

Je vous conseille également afin d'avoir une bonne visilité de votre
arborescence ldap d'utiliser "LDAP Browser", qui va vous permettre de vous
aider à renseigner les champs de ce fichier...

Nous n'utiliserons pas tous les champs de l'annuaire. Voici la liste des champs
utilisés (et pour vous aider 2 exemples de paramétrage pour ActiveDirectory2003 et OpenLDAP)

- $config_ldap_server : nom de votre serveur ayant le ldap (ou adresse IP).
	Pour info, fonctionne aussi en ldaps.
	ex : 	   $config_ldap_server = "http://nom_de_mon_serveur";
	(en ldaps) $config_ldap_server = "https://nom_de_mon_serveur";

- Vous pouvez également définir un serveur de "backup" (serveur secondaire de domaine) :
	$config_ldap_bupsvr = "http://serveur2"; 
	Si vous n'en avez pas, laisser le champ vide.

- $config_ldap_protocol_version : numéro de version du protocole LDAP utilisé par votre serveur.
	Les serveur LDAP récents (notamment OpenLDAP 2.x.x) utilisent la version 3 du protocole.
	Pour les serveur utilisant une version antérieure du protocole, cette option doit rester à 0;
	$config_ldap_protocol_version = 0 ;

---------------------------------------------------------------------------------------
- $config_basedn : quelle est la racine de votre domaine ?
	ex : $config_basedn = "dc=mon_domaine,dc=fr";
	Si vous avez un domaine toto.com, vous avez configuré sous un AD2003 un
	sous-domaine (uniquement pour vos besoins internes) 'administration',
	$config_basedn sera alors "dc=administration,dc=toto,dc=com"


- $config_ldap_user : s'il faut s'identifier pour accéder au ldap, rentrer un login ici.
	Pour un AD2003, il me semble que c'est obligatoire. Il est vivement conseillé 
	de créer un utilisateur qui ne servira qu'à cela (et ainsi évitera de mettre en clair
	le mot de passe d'un utilisateur existant), et interdire l'accès aux PC de cet utilisateur.
	(cf votre documentation (ou votre bible) ;-) Windows Server...)
	Ex (AD, utilisateur "ldap") : $config_ldap_user   = "CN=ldap,dc=administration,dc=toto,dc=com" ;

- $config_ldap_pass : le mot de passe associé au compte ci-dessus...

On peut laisser ces deux champ vides si la connexion au ldap anonyme est autorisée.


- $config_searchdn : permet d'indiquer le point d'entrée dans l'arborescence du LDAP.
	En effet, sous un AD, par exemple, vous avez "MesOrdinateurs", "MesUtilisateurs", "MesGroupes"... 
	Il est inutile de rechercher dans tout l'arbre.
	Ex (AD) : $config_searchdn = "ou=MesUtilisateurs,dc=administration,dc=toto,dc=com";
	(pour un OpenLdap 	   = "ou=people,dc=mon_domaine,dc=fr"		) 


Les champs suivants vont nous permettre d'extraire du ldap les données qui nous intéresse : 
En effet, même standardisé, d'un ldap à l'autre le nommage des champs vont être différents.
Nous prendrons 2 exemples "les + courants", Active Directory et OpenLDAP.
Pour Novell, IBM, ... : cf doc de votre ldap !

- $config_ldap_prenom : dans quel champ est indiqué le prénom de la personne ?
	AD ou OpenLDAP : "givenname"

- $config_ldap_nom : dans quel champ est indiqué le nom ?
	AD ou OpenLDAP : "sn"

- $config_ldap_mail : quel champ possède le mail de la personne ?
	AD ou OpenLDAP : "mail"

- $config_ldap_login : quel champ possède l'identifiant de l'utilisateur ?
	AD : "samaccountname", OpenLdap : "uid"

- $config_ldap_nomaff : quel champ du ldap affiche le nom et le prénom (dans le même champ) ?
	AD ou OpenLDAP : "displayName"

---------------------------------------------------------------------------------------
Ce qui suit sert uniquement dans le mode Administrateur pour lister les utilisateurs de
votre LDAP dans une zone de liste déroulante (et ainsi éviter la saisie du login, nom,
prénom, mot de passe, mail, ...).

On va devoir définir des critères de recherche :
- $config_ldap_filtre : sur quel filtre (quel champ du ldap) ?
- $config_ldap_filrec : critère de recherche.
	Ex : si on veut les "users" (permet de lister que les personnes) d'AD, il faut :
	$config_ldap_filtre = "objectclass";
	$config_ldap_filrech= "user";

	Vous pouvez aller plus loin suivant la façon dont vous avez renseigné le LDAP.
	Ex pour une université, nous avons le champ "supannAffectation" qui permet
	d'affecter les personnes aux composantes, on renseignera donc :
	$config_ldap_filtre = "supannAffectation";
	$config_ldap_filrech= "Scienc*";

	On recherche donc toutes les personnes dont l'affectation commencent par 'Scienc'
	(noter ici l'utilisation du caractère joker *)

Vous pouvez aller ENCORE plus loin en définissant vous-même le filtre complet de recherche :
$config_ldap_filtre_complet

/!\ Laisser ce champ vide si vous ne souhaitez pas l'utiliser !

	Et vous pouvez définir vos propres requêtes ldap (en utilisant le langage associé).
	Reprenons l'exemple de notre université qui veut que tous les congés personnels de scienc*
	et de droit soient gérés par l'application.
	$config_ldap_filtre_complet = "(&(displayName=*)(|(supannAffectation=Scienc*)(supannAffectation=DROIT)))"; 

	Je recherche tous les (utilisateurs) displayName=* ET
			(dont l'affectation 'supannAffectation' commence par Scien*
			   OU l'affectation 'supannAffectation' est DROIT)


**********************************************************************

INFORMATION IMPORTANTE :

	Je ne répondrai pas aux questions concernant le ldap, sa configuration,
	son utilisation. 
	Si vous êtes le responsable de la mise en place des congés, veuillez
	voir votre responsable informatique / administrateur réseau.
	Si vous êtes administrateur réseau, chercher sur le net pour configurer
	un ldap.
	
	Pourquoi ? Tout simplement parce que je n'ai pas mis en place de ldap,
	et que j'utilise celui qui a été mis à ma disposition.


					Didier CHABAUD
					Université d'Auvergne
					Décembre 2005


********************************************************************/

/********************************************************************/

// Voici ci-dessous 2 configs "pré-remplies" qui peuvent vous aider.
// (décommenter celle qui vous intéresse)


/**********  config Active Directory  ********/
/*-------------------------------------------*/


defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$config_ldap_server = "ldap://mon_serveur";
$config_ldap_protocol_version = 0 ;   // 3 si version 3 , 0 sinon !
$config_ldap_bupsvr = "";
$config_basedn      = "dc=mon_domaine,dc=fr";
$config_ldap_user   = "CN=user_ldap,dc=mon_domaine,dc=com" ;
$config_ldap_pass   = "user_ldap_pass";
$config_searchdn    = "ou=MesUtilisateurs,dc=mon_domaine,dc=com";

$config_ldap_prenom = "givenname"; 
$config_ldap_nom    = "sn";
$config_ldap_mail   = "mail";
$config_ldap_login  = "samaccountname";
$config_ldap_nomaff = "displayName";
$config_ldap_filtre = "objectclass";
$config_ldap_filrech= "user";

$config_ldap_filtre_complet = "";   



/********** Config OpenLDAP (en ldaps, anonyme autorisé) **********/
/*----------------------------------------------------------------*/
/*
$config_ldap_server = "ldaps://mon_serveur";
$config_ldap_protocol_version = 0 ;   // 3 si version 3 , 0 sinon !
$config_ldap_bupsvr = "";
$config_basedn      = "dc=mon_domaine,dc=com";
$config_ldap_user   = "";
$config_ldap_pass   = "";
$config_searchdn    = "ou=people,dc=mon_domaine,dc=com";

$config_ldap_prenom = "givenname"; 
$config_ldap_nom    = "sn";
$config_ldap_mail   = "mail";
$config_ldap_login  = "uid";
$config_ldap_nomaff = "displayName";
$config_ldap_filtre = "mon_filtre_de_recherche";
$config_ldap_filrech= "mon_critère_de_recherche";

$config_ldap_filtre_complet = "";   
*/


