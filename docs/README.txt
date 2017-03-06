***************************************
****         Libertempo           ****
***************************************

RETROUVEZ LA DOCUMENTATION � JOUR SUR http://libertempo.tuxfamily.org


SOMMAIRE :
----------
-> Descriptif
-> Fonctionnalit�s
-> Pr�requis
-> Licence
-> Installation
-> configuration
-> Contacts



----------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------



-> DESCRIPTIF :
-----------------
	Application web int�ractive de gestion des cong�s du personnels d'un service .
	
	** Libertempo se veut tr�s param�trable afin de fournir ou non diverses fonctionnalit�s aux utilisateurs. **
	** Libertempo est multi-langues. **
	
	Cette application se pr�sente en 5 volets :
	
	1 - volet utilisateur :
	   Les utilisateurs ont acc�s au bilan et � l'historique de leurs cong�s. Ils ont �galement acc�s au calendrier
	   des cong�s de tous les personnels du service.
	   Ce calendrier donne une repr�sentation graphique des absences des personnes (cong�s, artt, temps partiels).
	   Dans sa version par d�faut, les utilisateurs peuvent �galement saisir leurs demandes de cong�s. Chaque demande 
	   est ensuite accept�e ou refus�e par le responsable. L'utilisateur � alors �galement acc�s � l'historique de ces
	   demandes.
	   Cependant, une option de configuration permet de supprimer cette possibilit�. Dans ce cas, c'est le responsable
	   qui saisi les cong�s des personnels.
	
	2 - volet responsable :
	   permet � un ou plusieurs responsables de g�rer les demandes de cong�s des utilisateurs, de remettre les cong�s 
	   � jour en d�but d'ann�e, etc ....
	   L'application peut �galement fonctionner en mode "responsable g�n�rique virtuel", ce qui permet d'avoir plusieurs 
	   responsables r��ls (physiques) qui se connectent avec le m�me login pour g�rer les cong�s des personnels.
	   Choisir ce mode de fonctionnement entraine que tous les utilisateurs d'Libertempo sont trait�s comme des utilisateurs
	   classiques (m�me s'ils sont enregistr�s comme responsable dans la database !!!).
	   (le login du responsable virtuel est "conges" et le mot de passe par d�faut est "conges" ... � changer au + vite)
	
	3 - volet administrateur : 
	   Ce volet ne sert qu'a administrer les utilisateurs ou les groupes dans la base de donn�es. (ajout, suppression, modification, 
	   changement de mot de passe, ...). On peut �galement y trouver des outils pour g�r�er les jours f�ri�s, g�rer les types 
	   de cong�s, configurer l'application.

	
	Le principe de fonctionnement utilisateurs/responsables est simple :
	Chaque utilisateur est rattach� � un responsable (cf structure de la base de donn�es). C'est ce responsable
	qui valide des demandes de cong�s de l'utilisateur, ou saisi les cong�s de ce dernier (en fonction des options de
	configuration choisies).

	4 - volet RH :
	   Permet d'afficher/�diter un �tat des cong�s par utilisateur, de traiter les demandes de cong�s globalement, d'ajouter des cong�s (par utilisateurs, groupe ou globalement) et de cloturer l'exercice.

	5 - volet installation /configuration :
	   En principe, ce volet ne sert qu'une fois, lors de la mise en place de l'application. Il sert � installer (ou mettre 
	   � jour) l'application, et � la configurer selon le mode de fonctionnement voulu par l'�tablissement.


----------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------

-> FONCTIONNALITES :
----------------------------
	Libertempo propose de nombreuses fonctionalit�s. La plupart de celles ci sont param�trables dans la configuration d u logiciel.
	Elles peuvent alors �tre activ�es ou d�sactiv�es, ou autoris�es pour certains utilisateur plut�t que d'autres ....
	
	Les Principales fonctionnalit�s sont les suivantes :
	---------------------------------------------------------------------------------------
		- gestion des cong�s soit par le responsable seul , soit par syst�me de demande par l'utilisateur / validation par le responsable.
		- gestion des absences pour mission, formation, etc ...(absences sans perte de cong�s) par l'utilisateur ou par le responsable.
		- gestion des cong�s et absences par demi-journ�es.
		- calcul automatique du nombre de jours pris (lors d'une demande de conges) 
		- possibilit� de validation des demandes de cong�s par "double validation" (par le responsable direct + un responsable sup�rieur).
		- gestion des rtt et des temps partiels.
		- affichage de l'historiques des cong�s, de l'historiques des absences, et de l'historiques des demandes en cours .
		- param�trage des types de conges/absence : Possibilit� d'ajouter / supprimmer des types de cong�s ou d'absences qui seront g�r�es par l'application.
		- possibilit� de fonctionner avec un responsable virtuel. (Cela permet d'avoir plusieurs responsables r��ls identifi�s avec le m�me login pour g�rer les cong�s des personnels.)
		- possibilit� de fonctionnement par groupes d'utilisateurs.
		- possibilit� pour le responsable de refuser et d'annuler les absences d'un utilisateur.
		- gestion des utilisateurs (ajout, suprpession, modification, ...)
		- possibilit�, pour le responsable, d'ajouter des cong�s par utilisateur, par groupe, ou pour tous (une seule saisie) .
			(avec possibilit� d'indiquer si l'ajout est proportionnel � la quotit� (temps partiel) des utilisateurs ou non)
		- possibilit� d'authentifier les utilisateurs sur un annuaire de type LDAP ou Active-Directory.
		- possibilit� d'exporter les utilisateurs depuis un annuaire LDAP
		- possibilit� d'authentification des utilisateur sur un serveur CAS.
		- module de saisie les jours ch�m�s/jours f�ri�s (n�c�ssaire pour la fonction de calcul automatique)
		- envoi possible de mail d'avertissement (en cas de demande de cong�s par un utilisateur, de validation, de refus ou d'annulation par un responsable)
		- possibilit�, pour les utilisateurs et/ou les responsables, d'afficher le calendrier des cong�s / absences de tous ou partie des utilisateurs..
		- possibilit� pour les utilisateurs d'exporter leurs cong�s dans un fichier au format ics ou vcs (calendriers, agenda, et plannings �lectroniques)
		- Possibilit� de prise en compte des samedis et dimanches ouvr�s.
		- �ditions papier : g�n�ration d'�tat imprimables ou au format PDF.
		- possibilit� d'afficher dans les historiques et les �ditions papier, les dates et heures de demande de cong�s par l'utilisateur, et de traitement de la demande par le responsable.
		- lisibilit� du calendrier accrue : surlignage automatique de la ligne du calendrier/ s�lection coloris� d'une ligne
		- gestion des sessions utilisateurs
		- application multi-langues
		- Module d'installation
		- Module de configuration
		- Possibilit� d'avoir sur la page administrateur, un bouton d'acc�s au formulaire de config de l'appli.
		- possibilit� que certains utilisateurs privil�gi�s puissent voir les conges de tout le monde dans le calendrier.
		- fonctionnalit� de sauvegarde/restauration de la database (dans le module administrateur).
		- fourniture d'un jeu d'utilisateurs de test (pour prise en main du logiciel apr�s installation)
		- mise en page bas�e sur feuille de style (css)


----------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------

-> PREREQUIS :
-----------------
	serveur web + PHP + MySQL
	Libertempo a �t� test� sous apache (v1.3.x et v2) et PHP (v4.2.x , 4.3.x et 5.x) et MySQL (v3.23.x et v4.x)
	(configuration de PHP  : "track_vars" � "enable" et "magic_quotes_gpc" � "on" )
	(Notez que depuis PHP 4.0.3, track_vars est toujours activ�e.)



----------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------

-> LICENCE :
-------------
	(voir fichier license.txt ou http://www.linux-france.org/article/these/gpl.html )
	/*************************************************************************************************
	Libertempo : Gestion Interactive des Cong�s. Fork de PHPCong�s
        Copyright (C) 2015 (Wouldsmina)
        Copyright (C) 2015 (Prytoegrian)
        Copyright (C) 2005 (cedric chauvineau)
	
	Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les 
	termes de la Licence Publique G�n�rale GNU publi�e par la Free Software Foundation.
	Ce programme est distribu� car potentiellement utile, mais SANS AUCUNE GARANTIE, 
	ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation 
	dans un but sp�cifique. Reportez-vous � la Licence Publique G�n�rale GNU pour plus de d�tails.
	Vous devez avoir re�u une copie de la Licence Publique G�n�rale GNU en m�me temps 
	que ce programme ; si ce n'est pas le cas, �crivez � la Free Software Foundation, 
	Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, �tats-Unis.
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




----------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------

-> INSTALLATION :
----------------
voir le fichier INSTALL.txt



----------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------

-> CONFIGURATION :
-------------------
voir le fichier INSTALL.txt



----------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------

-> CONTACT :
---------------
http://Libertempo.tuxfamily.org
mail : Libertempo@tuxfamily.org

.
