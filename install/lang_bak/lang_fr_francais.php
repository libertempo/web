<?php
/************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2005 (cedric chauvineau)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les
termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE,
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation
dans un but spécifique. Reportez-vous à la Licence Publique Générale GNU pour plus de détails.
Vous devez avoir reçu une copie de la Licence Publique Générale GNU en m&ecirc;me temps
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

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

// Fichier de Langue FR (FRANCAIS )
$LANG=array();
/*************************************************************************************************/
// VARIABLES A RENSEIGNER :



/***********************/
// MOIS ET JOURS
$LANG['janvier']	= "Janvier";
$LANG['fevrier']	= "Février";
$LANG['mars']		= "Mars";
$LANG['avril']		= "Avril";
$LANG['mai']		= "Mai";
$LANG['juin']		= "Juin";
$LANG['juillet']	= "Juillet";
$LANG['aout']		= "Août";
$LANG['septembre']	= "Septembre";
$LANG['octobre']	= "Octobre";
$LANG['novembre']	= "Novembre";
$LANG['decembre']	= "Décembre";

$LANG['lundi']		= "lundi";
$LANG['mardi']		= "mardi";
$LANG['mercredi']	= "mercredi";
$LANG['jeudi']		= "jeudi";
$LANG['vendredi']	= "vendredi";
$LANG['samedi']		= "samedi";
$LANG['dimanche']	= "dimanche";

$LANG['lundi_short']		= "lun";
$LANG['mardi_short']		= "mar";
$LANG['mercredi_short']		= "mer";
$LANG['jeudi_short']		= "jeu";
$LANG['vendredi_short']		= "ven";
$LANG['samedi_short']		= "sam";
$LANG['dimanche_short']		= "dim";

$LANG['lundi_2c']		= "lu";
$LANG['mardi_2c']		= "ma";
$LANG['mercredi_2c']	= "me";
$LANG['jeudi_2c']		= "je";
$LANG['vendredi_2c']	= "ve";
$LANG['samedi_2c']		= "sa";
$LANG['dimanche_2c']	= "di";

$LANG['lundi_1c']		= "L";
$LANG['mardi_1c']		= "M";
$LANG['mercredi_1c']	= "M";
$LANG['jeudi_1c']		= "J";
$LANG['vendredi_1c']	= "V";
$LANG['samedi_1c']		= "S";
$LANG['dimanche_1c']	= "D";



/***********************/
// BOUTONS COMMUNS
$LANG['button_deconnect']	= "Deconnexion";
$LANG['button_refresh']		= "Actualiser";
$LANG['button_editions']	= "Editions Papier";
$LANG['button_responsable_mode']	= "Responsable";
$LANG['button_admin_mode']	= "Administrateur";
$LANG['button_calendar']	= "Calendrier";



/***********************/
// FORMULAIRES
$LANG['form_ok']			= "OK";
$LANG['form_submit']		= "Valider";
$LANG['form_cancel']		= "Abandonner";
$LANG['form_retour']		= "Retour";
$LANG['form_ajout']			= "Ajouter";
$LANG['form_supprim']		= "Supprimer";
$LANG['form_modif']			= "Modifier";
$LANG['form_annul']			= "Annuler";
$LANG['form_redo']			= "Recommencer";
$LANG['form_am']			= "matin";
$LANG['form_pm']			= "après midi";
$LANG['form_day']			= "journée complète";
$LANG['form_close_window']	= "Fermer cette Fen&ecirc;tre";
$LANG['form_save_modif']	= "Enregistrer les modifications";
$LANG['form_modif_ok']		= "Modifications enregistrées avec succés !";
$LANG['form_modif_not_ok']	= "ERREUR ! Modifications NON enregistrées !";
$LANG['form_valid_global']	= "Valider la saisie globale";
$LANG['form_valid_groupe']	= "Valider la saisie pour le Groupe";
$LANG['form_password']		= "Password";
$LANG['form_start']			= "Commencer";
$LANG['form_continuer']		= "Continuer";



/***********************/
// DIVERS
$LANG['divers_quotite']			= "quotité";
$LANG['divers_quotite_maj_1']	= "Quotité";
$LANG['divers_an']				= "an";
$LANG['divers_an_maj']			= "AN";
$LANG['divers_solde']			= "solde";
$LANG['divers_solde_maj']		= "SOLDE";
$LANG['divers_solde_maj_1']		= "Solde";
$LANG['divers_debut_maj']		= "DEBUT";
$LANG['divers_debut_maj_1']		= "Début";
$LANG['divers_fin_maj']			= "FIN";
$LANG['divers_fin_maj_1']		= "Fin";
$LANG['divers_type']			= "type";
$LANG['divers_type_maj_1']		= "Type";
$LANG['divers_comment_maj_1']	= "Commentaire";
$LANG['divers_etat_maj_1']		= "Etat";
$LANG['divers_comment_refus_maj_1']		= "Etat";
$LANG['divers_nb_jours_pris_maj_1']	= "nb Jours Pris";
$LANG['divers_nb_jours_maj_1']	= "nb Jours";
$LANG['divers_inconnu']			= "inconnu";
$LANG['divers_motif_refus']		= "Motif du refus";
$LANG['divers_motif_annul']		= "Motif de l'annulation";
$LANG['divers_refuse']			= "refusé";
$LANG['divers_annule']			= "annulé";
$LANG['divers_login']			= "login";
$LANG['divers_login_maj_1']		= "Login";
$LANG['divers_personne_maj_1']	= "Personne";
$LANG['divers_responsable_maj_1']	= "Responsable";
$LANG['divers_nom_maj']			= "NOM";
$LANG['divers_nom_maj_1']		= "Nom";
$LANG['divers_prenom_maj']		= "PRENOM";
$LANG['divers_prenom_maj_1']	= "Prénom";
$LANG['divers_accepter_maj_1']	= "Accepter";
$LANG['divers_refuser_maj_1']	= "Refuser";
$LANG['divers_fermer_maj_1']	= "Fermer";
$LANG['divers_am_short']		= "am";
$LANG['divers_pm_short']		= "pm";
$LANG['divers_conges']			= "congés";
$LANG['divers_conges_maj_1']	= "Congés";
$LANG['divers_absences']		= "absences";
$LANG['divers_absences_maj_1']	= "Absences";
$LANG['divers_nouvelle_absence']	    = "Nouvelle Absence";
$LANG['divers_mois_precedent']			= "mois précédent";
$LANG['divers_mois_precedent_maj_1']	= "Mois Précédent";
$LANG['divers_mois_suivant']			= "mois suivant";
$LANG['divers_mois_suivant_maj_1']		= "Mois Suivant";




/***********************/
// PARTIE UTILISATEUR
//divers
$LANG['user']				= "Utilisateur";

//onglets
$LANG['user_onglet_echange_abs']		= "Echange jour absence";
$LANG['user_onglet_demandes']			= "Demandes en cours";
$LANG['user_onglet_historique_conges']	= "Historique des congés";
$LANG['user_onglet_historique_abs']		= "Historique autres absences";
$LANG['user_onglet_change_passwd']		= "Changer mot de passe";

//titres des pages
$LANG['user_echange_rtt']				= "Echange jour rtt,temps partiel / jour travaillé";
$LANG['user_etat_demandes']				= "Etat des demandes en cours";
$LANG['user_historique_conges']			= "Historique des congés";
$LANG['user_historique_abs']			= "Historique des absences pour mission, formation, etc ...";
$LANG['user_change_password']			= "Changer votre mot de passe";

//page etat des demandes
$LANG['user_demandes_aucune_demande']	= "Aucune demande en cours ...";

//page historique des conges
$LANG['user_conges_aucun_conges']		= "Aucun congés dans la base de données ...";

//page historique des absences
$LANG['user_abs_aucune_abs']			= "Aucune absences dans la base de données ...";
$LANG['user_abs_type']				= "Absence";

//page changer password
$LANG['user_passwd_saisie_1']		= "1iere saisie";
$LANG['user_passwd_saisie_2']		= "2eme saisie";
$LANG['user_passwd_error']			= "ERREUR ! les 2 saisies sont différentes ou vides !!";


//page modification demande / absence
$LANG['user_modif_demande_titre']		= "Modification d'une demande/absence.";

//page suppression demande / absence
$LANG['user_suppr_demande_titre']		= "Suppression demande de conges .";





/***********************/
// PARTIE RESPONSABLE
//menu
$LANG['resp_menu_titre']					= "MODE RESPONSABLE :";
$LANG['resp_menu_button_retour_main']		= "Page Principale";
$LANG['resp_menu_button_traite_demande']	= "Traiter toutes les Demandes";
$LANG['resp_menu_button_affiche_user']		= "Afficher personne";
$LANG['resp_menu_button_ajout_jours']		= "Ajout Jours Conges";
$LANG['resp_menu_button_mode_user']			= "Utilisateur";
$LANG['resp_menu_button_mode_responsable']			= "Responsable";
$LANG['resp_menu_button_mode_admin']		= "Administrateur";

//page etat des conges des users
$LANG['resp_etat_users_afficher']	= "Afficher";
$LANG['resp_etat_users_imprim']		= "Edition Papier";
//page traite toutes les demandes
$LANG['resp_traite_demandes_titre']				= "Traitement des demandes de congés :";
$LANG['resp_traite_demandes_aucune_demande']	= "Aucune demande de congés en cours dans la base de données ...";
$LANG['resp_traite_demandes_nb_jours']			= "nb Jours<br>Pris";
$LANG['resp_traite_demandes_attente']			= "Attente";
$LANG['resp_traite_demandes_motif_refus']		= "Motif<br>de refus";
//page ajout conges
$LANG['resp_ajout_conges_titre']				= "Ajout de congés";
$LANG['resp_ajout_conges_nb_jours_ajout']		= "NB jours à ajouter";
$LANG['resp_ajout_conges_ajout_all']			= "Ajout global pour Tous :";
$LANG['resp_ajout_conges_nb_jours_all_1']		= "Nombre de jours de";
$LANG['resp_ajout_conges_nb_jours_all_2']		= "à ajouter à tous :";
$LANG['resp_ajout_conges_calcul_prop']			= "Calcul proportionnel à la quotité de chaque personne :";
$LANG['resp_ajout_conges_oui']					= "OUI";
$LANG['resp_ajout_conges_calcul_prop_arondi']	= "le calcul proportionnel est arrondi au 1/2 le plus proche";
$LANG['resp_ajout_conges_ajout_groupe']			= "Ajout par Groupe : (ajout à tous les membres d'un groupe)";
$LANG['resp_ajout_conges_choix_groupe']			= "choix du groupe";
$LANG['resp_ajout_conges_nb_jours_groupe_1']	= "Nombre de jours de";
$LANG['resp_ajout_conges_nb_jours_groupe_2']	= "à ajouter au groupe :";
$LANG['resp_ajout_conges_comment_periode_user']	= "ajout jour";
$LANG['resp_ajout_conges_comment_periode_all']	= "ajout pour tous les personnels";
$LANG['resp_ajout_conges_comment_periode_groupe']	= "ajout pour le groupe";
//page traite user
$LANG['resp_traite_user_titre']				= "Traitement de :";
$LANG['resp_traite_user_new_conges']		= "Nouveau congés/Absence :";
$LANG['resp_traite_user_etat_demandes']		= "Etat des demandes :";
$LANG['resp_traite_user_etat_conges']		= "Historique des congés :";
$LANG['resp_traite_user_aucune_demande']	= "Aucune demande de congés pour cette personne dans la base de données ...";
$LANG['resp_traite_user_motif_refus']		= "motif refus";
$LANG['resp_traite_user_aucun_conges']		= "Aucun congés pour cette personne dans la base de données ...";
$LANG['resp_traite_user_motif_possible']	= "motif refus ou annulation éventuel";
$LANG['resp_traite_user_annul']				= "Annuler";
$LANG['resp_traite_user_motif_annul']		= "motif annulation";
$LANG['resp_traite_user_motif']				= "motif";
$LANG['resp_traite_user_valeurs_not_ok']	= "ERREUR ! Les valeurs saisies sont invalides ou manquantes  !!!";





/***********************/
// PARTIE ADMINISTRATEUR
//divers
$LANG['admin_titre']					= "Administration des utilisateurs";
$LANG['admin_button_close_window_1']	= "Fermeture du mode Administrateur";
$LANG['admin_button_config_1']			= "Configuration de php_conges";
$LANG['admin_button_config_2']			= "Configuration";
$LANG['admin_button_config_abs_1']		= "Configuration des types d'absence gérées par php_conges";
$LANG['admin_button_config_abs_2']		= "Config Absences";
$LANG['admin_button_jours_chomes_1']	= "Jours chômés";
$LANG['admin_button_jours_chomes_2']	= "Jours chômés";
$LANG['admin_button_save_db_1']			= "Backup";
$LANG['admin_button_save_db_2']			= "Backup";
//
$LANG['admin_onglet_gestion_user']		= "Gestion des Utilisateurs";
$LANG['admin_onglet_add_user']			= "Ajout d'un Utilisateur";
$LANG['admin_onglet_gestion_groupe']	= "Gestion des Groupes";
$LANG['admin_onglet_groupe_user']		= "Gestion Groupes <-> Utilisateurs";
$LANG['admin_onglet_user_groupe']		= "Gestion Utilisateurs <-> Groupes";
$LANG['admin_onglet_groupe_resp']		= "Gestion Groupes <-> Responsables";
$LANG['admin_onglet_resp_groupe']		= "Gestion Responsables <-> Groupes";
//
$LANG['admin_verif_param_invalides']	= "ATTENTION : certain champs saisis ne sont pas valides ......";
$LANG['admin_verif_login_exist']		= "ATTENTION : login déjà utilisé, veuillez en changer ......";
$LANG['admin_verif_bad_mail']			= "ATTENTION : adresse mail éronnée ......";
$LANG['admin_verif_groupe_invalide']	= "ATTENTION : nom de groupe déjà utilisé, veuillez en changer ......";
// page gestion utilisateurs
$LANG['admin_users_titre']				= "Etat des Utilisateurs";
$LANG['admin_users_is_resp']			= "is_resp";
$LANG['admin_users_resp_login']			= "resp_login";
$LANG['admin_users_is_admin']			= "is_admin";
$LANG['admin_users_see_all']			= "see_all";
$LANG['admin_users_mail']				= "email";
$LANG['admin_users_password_1']			= "password1";
$LANG['admin_users_password_2']			= "password2";
// page ajout utilisateur
$LANG['admin_new_users_titre']			= "Nouvel Utilisateur :";
$LANG['admin_new_users_is_resp']		= "Droits Responsable ?";
$LANG['admin_new_users_is_admin']		= "Droits Administrateur ?";
$LANG['admin_new_users_see_all']		= "Voir Tous";
$LANG['admin_new_users_password']		= "password";
$LANG['admin_new_users_nb_par_an']		= "nb / an";
// page ajout utilisateur
$LANG['admin_groupes_groupe']			= "Groupe";
$LANG['admin_groupes_libelle']			= "libellé";
$LANG['admin_groupes_new_groupe']		= "Nouveau Groupe :";
// page gestion groupes
$LANG['admin_gestion_groupe_etat']		= "Etat des Groupes";
//
$LANG['admin_aff_choix_groupe_titre']	= "Choix d'un Groupe";
$LANG['admin_aff_choix_user_titre']		= "Choix d'un Utilisateur";
$LANG['admin_aff_choix_resp_titre']		= "Choix d'un Responsable";
// page gestion groupe <-> users
$LANG['admin_gestion_groupe_users_membres']	= "Membres du Groupe";
$LANG['admin_gestion_groupe_users_group_of_user']	= "Groupes auxquels appartient";
// page gestion groupe <-> users
$LANG['admin_gestion_groupe_resp_groupes']		= "Groupes du Responsable";
$LANG['admin_gestion_groupe_resp_responsables']	= "Responsables du Groupe";
// page change password user
$LANG['admin_chg_passwd_titre']		= "Modification Password utilisateur";
// page admin_suppr_user
$LANG['admin_suppr_user_titre']		= "Suppression Utilisateur";
// page admin_modif_user
$LANG['admin_modif_user_titre']		= "Modification utilisateur";
$LANG['admin_modif_nb_jours_an']	= "nb jours / an";
// grille saisie temps partiel et RTT
$LANG['admin_temps_partiel_titre']			= "saisie des jours d'abscence pour ARTT ou temps partiel";
$LANG['admin_temps_partiel_sem_impaires']	= "semaines Impaires";
$LANG['admin_temps_partiel_sem_paires']		= "semaines Paires";
$LANG['admin_temps_partiel_am']				= "matin";
$LANG['admin_temps_partiel_pm']				= "apres-midi";
$LANG['admin_temps_partiel_date_valid']		= "Date de début de validité de cette grille";
// page admin_suppr_groupe
$LANG['admin_suppr_groupe_titre']		= "Suppression de Groupe.";
// page admin_suppr_groupe
$LANG['admin_modif_groupe_titre']		= "Modification de Groupe.";
// page admin_sauve_restaure_db
$LANG['admin_sauve_db_titre']			= "Sauvegarde / Restauration de la Base de données";
$LANG['admin_sauve_db_choisissez']		= "Choisissez";
$LANG['admin_sauve_db_sauve']			= "Sauvegarder";
$LANG['admin_sauve_db_restaure']		= "Restaurer";
$LANG['admin_sauve_db_do_sauve']		= "Démarrer la sauvegarde";
$LANG['admin_sauve_db_options']			= "Options de Sauvegarde";
$LANG['admin_sauve_db_complete']		= "Sauvegarde complète";
$LANG['admin_sauve_db_data_only']		= "Sauvegarde des données seules";
$LANG['admin_sauve_db_save_ok']			= "Sauvegarde effectuée";
$LANG['admin_sauve_db_restaure']		= "Restauration de la base de données";
$LANG['admin_sauve_db_file_to_restore']	= "Fichier à restaurer";
$LANG['admin_sauve_db_warning']			= "ATTENTION : toutes les données de la database php_conges vont &ecirc;tre écrasées avant la restauration";
$LANG['admin_sauve_db_do_restaure']		= "Lancer la Restauration";
$LANG['admin_sauve_db_bad_file']		= "Fichier indiqué inexistant";
$LANG['admin_sauve_db_restaure_ok']		= "Restauration effectuée avec succés";
// page admin_jours_chomes
$LANG['admin_jours_chomes_titre']				= "Jours chômés";
$LANG['admin_jours_chomes_annee_precedente']	= "année précédente";
$LANG['admin_jours_chomes_annee_suivante']		= "année suivante";
$LANG['admin_jours_chomes_confirm']				= "Confirmer cette Saisie";




/***********************/
// EDITIONS PAPIER
$LANG['editions_titre']			= "Editions Conges";
$LANG['editions_last_edition']	= "Prochaine Edition";
$LANG['editions_aucun_conges']	= "Aucun congés à éditer dans la base de données ...";
$LANG['editions_lance_edition']		= "Lancer l'édition";
$LANG['editions_pdf_edition']		= "Edition en PDF";
$LANG['editions_hitorique_edit']		= "Historique des éditions";
$LANG['editions_aucun_hitorique']	= "Aucune édition enregistrée pour cet utilisateur ...";
$LANG['editions_numero']			= "Numero";
$LANG['editions_date']				= "Date";
$LANG['editions_edit_again']		= "Editer à nouveau";
$LANG['editions_edit_again_pdf']	= "Editer à nouveau en PDF";
//
$LANG['editions_bilan_au']			= "bilan au";
$LANG['editions_historique']		= "Historique";
$LANG['editions_soldes_precedents_inconnus']	= "soldes précédents inconnus";
$LANG['editions_solde_precedent']	= "solde précédent";
$LANG['editions_nouveau_solde']		= "nouveau solde";
$LANG['editions_signature_1']		= "Signature du titulaire";
$LANG['editions_signature_2']		= "Signature du responsable";
$LANG['editions_cachet_etab']		= "et cachet de l'établissement";
$LANG['editions_jours_an']			= "jours / an";



/***********************/
// SAISIE CONGES
$LANG['saisie_conges_compter_jours']		= "Compter les jours";
$LANG['saisie_conges_nb_jours']				= "NB_Jours_Pris";



/***********************/
// SAISIE ECHANGE ABSENCE
$LANG['saisie_echange_titre_calendrier_1']		= "Jour d'absence ordinaire";
$LANG['saisie_echange_titre_calendrier_2']		= "Jour d'absence souhaité";



/***********************/
// CALENDRIER
$LANG['calendrier_titre']			= "CALENDRIER des CONGES";
$LANG['calendrier_imprimable']		= "version imprimable";
$LANG['calendrier_jour_precedent']	= "Jour Precedent";
$LANG['calendrier_jour_suivant']	= "Jour Suivant";
$LANG['calendrier_legende_we']			= "week-end ou jour férié";
$LANG['calendrier_legende_conges']		= "congés pris ou a prendre";
$LANG['calendrier_legende_demande']		= "congés demandé (non encore accordé)";
$LANG['calendrier_legende_part_time']	= "absence hebdomadaire (temps partiel , RTT)";
$LANG['calendrier_legende_abs']			= "absence autre (mission, formation, maladie, ...)";



/***********************/
// CALCUL NB JOURS
$LANG['calcul_nb_jours_nb_jours']	= "Nombre de jours à prendre :";
$LANG['calcul_nb_jours_reportez']	= "reportez ce nombre dans la case";
$LANG['calcul_nb_jours_form']		= "du formulaire";



/***********************/
// ERREUR
$LANG['erreur_user']			= "Impossible d'identifier le user";
$LANG['erreur_login_password']	= "couple login/mot de passe non valide ou login absent";
$LANG['erreur_session']			= "session invalide ou expirée";



/***********************/
// INCLUDE_PHP
$LANG['mysql_srv_connect_failed']	= "Impossible de se connecter au serveur ";
$LANG['mysql_db_connect_failed']		= "Impossible de se connecter à la base de données";

// page d'authentification / login screen
$LANG['cookies_obligatoires']		= "Il est nécessaire que votre navigateur accepte les <b>cookies</b> pour pouvoir vous connecter à PHP_CONGES.";
$LANG['javascript_obligatoires']		= "Il est conseillé que votre navigateur accepte le <b>Javascript</b> pour utiliser PHP_CONGES.";
$LANG['login_passwd_incorrect']		= "ERREUR : Nom d'utilisateur et/ou mot de passe incorrect !!!";
$LANG['login_non_connu']				= "ERREUR : Utilisateur non enregistré pour la gestion des congés !!!";
//
$LANG['login_fieldset']			= "Identification";
$LANG['password']					= "Mot de Passe";
$LANG['msie_alert']				= "Remarque : Certains affichages peuvent ne pas &ecirc;tre pris en charge par Microsoft IE. Utilisez plutôt Mozilla Firefox.";


// verif saisie
$LANG['verif_saisie_erreur_valeur_manque']		= "ERREUR : mauvaise saisie : valeurs <b>manquantes !!!</b>";
$LANG['verif_saisie_erreur_nb_jours_bad']		= "ERREUR : mauvaise saisie : <b>le nombre de jours est invalide</b>";
$LANG['verif_saisie_erreur_fin_avant_debut']	= "ERREUR : mauvaise saisie : <b>la date de fin est anterieure à la date de début !!!</b>";
$LANG['verif_saisie_erreur_debut_apres_fin']	= "ERREUR : mauvaise saisie : <b>la date de début est postérieure à la date de fin !!!</b>";
$LANG['verif_saisie_erreur_nb_bad']				= "ERREUR : mauvaise saisie : <b>le nombre saisi est invalide</b>";


/***********************/
// CONFIG TYPES ABSENCES
$LANG['config_abs_titre']				= "Configuration des types d'absence gérées par PHP_CONGES";
$LANG['config_abs_comment_conges']		= "Les types d'absences listés ici sont des congés divers, décomptés chacuns sur des comptes séparés." ;
$LANG['config_abs_comment_absences']	= "Les types d'absences listés ici ne sont pas décomptés (ce sont des absences autorisées)." ;
$LANG['config_abs_libelle']				= "libellé";
$LANG['config_abs_libelle_short']		= "libellé court";
$LANG['config_abs_add_type_abs']			= "ajouter un type d'absence :";
$LANG['config_abs_add_type_abs_comment']	= "Saisissez le type d'absence que vous voulez ajouter :";
$LANG['config_abs_saisie_not_ok']			= "saisie incorrecte :";
$LANG['config_abs_bad_caracteres']			= "les caractères suivants sont interdits:";
$LANG['config_abs_champs_vides']			= "des champs sont vides !";
$LANG['config_abs_suppr_impossible']		= "Suppression IMPOSSIBLE !";
$LANG['config_abs_already_used']			= "Des congés/absences de ce type sont en cours !";
$LANG['config_abs_confirm_suppr_of']		= "Veuillez confirmer la supression de";



/***************************/
// CONFIGURATION PHP_CONGES
$LANG['config_appli_titre_1']		= "Configuration de l'Application PHP_CONGES";
$LANG['config_appli_titre_2']		= "Configuration de php_conges";
//groupes de paramètres
$LANG['00_php_conges']				= "00 php_conges";
$LANG['01_Serveur Web']				= "01 Serveur Web";
$LANG["02_PAGE D'AUTENTIFICATION"]	= "02 PAGE D'AUTENTIFICATION";
$LANG['03_TITRES']					= "03 TITRES";
$LANG['04_Authentification']		= "04 Authentification";
$LANG['05_Utilisateur']				= "05 Utilisateur";
$LANG['06_Responsable']				= "06 Responsable";
$LANG['07_Administrateur']			= "07 Administrateur";
$LANG['08_Mail']					= "08 Mail";
$LANG['09_jours ouvrables']			= "09 jours ouvrés";
$LANG['10_Gestion par groupes']		= "10 Gestion par groupes";
$LANG['11_Editions papier']			= "11 Editions papier";
$LANG["12_Fonctionnement de l'Etablissement"]	= " 12 Fonctionnement de l'Etablissement";
$LANG['13_Divers']					= "13 Divers";
$LANG['14_Présentation']			= "14 Présentation";
$LANG['15_Modules Externes']		= "15 Modules Externes";
// parametres de config
$LANG['config_comment_installed_version']	= "numéro de version installée";
$LANG['config_comment_lang']				= "// LANGUE / LANGUAGE<br>\n//---------------------------<br>\n// fr = français<br>\n// test = seulement pour les developpeurs de php_conges (only for php_conges developpers)";
$LANG['config_comment_URL_ACCUEIL_CONGES']	= "// URL DE BASE DE VOTRE INSTALLATION DE PHP_CONGES<br>\n//-------------------------------------------------<br>\n// URL de base de php_conges sur votre serveur (ce que vous devez taper pour obtenir la page d'authentification.<br>\n// (PAS terminé par un / et sans le index.php à la fin)<br>\n// URL_ACCUEIL_CONGES = \"http://monserveurweb.mondomaine/php_conges\"";
$LANG['config_comment_img_login']			= "// IMAGE DE LA PAGE DE LOGIN<br>\n//---------------------------<br>\n// image qui apparait en haut de la page d'authentification de php_conges";
$LANG['config_comment_texte_img_login']		= "// TEXTE DE L'IMAGE<br>\n//-------------------<br>\n// texte de l'image";
$LANG['config_comment_lien_img_login']		= "// LIEN DE L'IMAGE<br>\n//------------------<br>\n// URL où renvoit l'image de la page de login";
$LANG['config_comment_titre_calendrier']	= "Titre de la page calendrier de php_conges";
$LANG['config_comment_titre_user_index']	= "Titre des pages Utilisateur (sera suivi du login de l'utilisateur)";
$LANG['config_comment_titre_resp_index']	= "Titre des pages Responsable";
$LANG['config_comment_titre_admin_index']	= "Titre des pages Administrateur";
$LANG['config_comment_auth']				= "// Autentification :<br>\n//---------------------<br>\n// si = FALSE : pas d'authetification au démarrage , il faut passer le parametre login à l'appel de php_conges<br>\n// si = TRUE  : la page d'autentification apparait à l'appel de php_conges (TRUE est la valeur par defaut)";
$LANG['config_comment_how_to_connect_user']	= "// Comment vérifier le login et mot de passe des utilisateurs au démarrage :<br>\n//--------------------------------------------------------------------------<br>\n// si à \"dbconges\" : l'authentification des user se fait dans la table users de la database db_conges<br>\n// si à \"ldap\"     : l'authentification des user se fait dans un annuaire LDAP que l'on va intérroger (cf config_ldap.php)<br>\n// si à \"CAS\"      : l'authentification des user se fait sur un serveur CAS que l'on va intérroger (cf config_CAS.php)<br>\n// attention : toute autre valeur que \"dbconges\" ou \"ldap\" ou \"CAS\" entrainera une érreur !!!";
$LANG['config_comment_export_users_from_ldap']	= "// Export des Users depuis LDAP :<br>\n//--------------------------------<br>\n// si = FALSE : les users sont créés \"à la main\" directement dans php_conges (FALSE est la valeur par defaut)<br>\n// si = TRUE  : les user sont importés du serveur LDAP (graceà une iste déroulante) (cf config_ldap.php)";
$LANG['config_comment_user_saisie_demande']		= "//  DEMANDES DE CONGES<br>\n//---------------------------------------<br>\n// si à FALSE : pas de saisie de demande par l'utilisateur, pas de gestion des demandes par le responsable<br>\n// si à TRUE : saisie de demande par l'utilisateur, et gestion des demandes par le responsable (TRUE est la valeur par defaut)";
$LANG['config_comment_user_affiche_calendrier']	= "//  AFFICHAGE DU BOUTON DE CALENDRIER POUR L'UTILISATEUR<br>\n//--------------------------------------------------------------------------------------<br>\n// si à FALSE : les utilisateurs n'ont pas la possibilité d'afficher le calendrier des congés<br>\n// si à TRUE : les utilisateurs ont la possibilité d'afficher le calendrier des congés (TRUE est la valeur par defaut)";
$LANG['config_comment_user_saisie_mission']		= "//  SAISIE  DES ABSENCES POUR MISSIONS, FORMATIONS, CONGRES, ETC .... PAR L'UTILISATEUR<br>\n//--------------------------------------------------------------------------------------<br>\n// ( les absences de ce type n'enlèvent pas de jours de congés ! )<br>\n// si à FALSE : pas de saisie par l'utilisateur des absences pour mission, formation, congrés, etc ....<br>\n// si à TRUE : saisie par l'utilisateur des absences pour mission, formation, congrés, etc .... (TRUE est la valeur par defaut)";
$LANG['config_comment_user_ch_passwd']			= "//  CHANGER SON PASSWORD<br>\n//---------------------------------------<br>\n// si à FALSE : l'utilisateur ne peut pas changer son password<br>\n// si à TRUE : l'utilisateur peut changer son password (TRUE est la valeur par defaut)";
$LANG['config_comment_responsable_virtuel']		= "//  RESPONSABLE GENERIQUE VIRTUEL OU NON<br>\n//-------------------------------------------<br>\n// si à FALSE : le responsable qui traite les congés des personnels est une personne reelle (utilisateur de php_conges) (FALSE est la valeur par defaut)<br>\n// si à TRUE : le responsable qui traite les congés des personnels est un utilisateur generique virtuel (login=conges)";
$LANG['config_comment_resp_affiche_calendrier']	= "//  AFFICHAGE DU BOUTON DE CALENDRIER POUR LE RESPONSABLE<br>\n//--------------------------------------------------------------------------------------<br>\n// si à FALSE : les responsables n'ont pas la possibilité d'afficher le calendrier des congés<br>\n// si à TRUE : les responsables ont la possibilité d'afficher le calendrier des congés (TRUE est la valeur par defaut)";
$LANG['config_comment_resp_saisie_mission']		= "//  SAISIE  DES ABSENCES POUR MISSIONS, FORMATIONS, CONGRES, ETC .... PAR LE RESPONSABLE<br>\n//---------------------------------------------------------------------------------------<br>\n// ( les absences de ce type n'enlèvent pas de jours de congés ! )<br>\n// si à FALSE : pas de saisie par le responsable des absences pour mission, formation, congrés, etc ....(FALSE est la valeur par defaut)<br>\n// si à TRUE : saisie par le responsable des absences pour mission, formation, congrés, etc ....";
$LANG['config_comment_resp_vertical_menu']		= "//  CONFIG  DU MENU DU RESPONSABLE<br>\n//---------------------------------------<br>\n// si à TRUE : dans la fenetre responsable, le menu est vertical (à gauche) (TRUE est la valeur par defaut)<br>\n// si à FALSE : dans la fenetre responsable, le menu est horizontal (en haut)";
$LANG['config_comment_admin_see_all']			= "//  CONFIG  DU MODE ADMINISTRATEUR<br>\n//---------------------------------------<br>\n// si à FALSE : l'admin ne gere que les users dont il est responsable (FALSE est la valeur par defaut)<br>\n// si à TRUE : l'admin gere tous les users";
$LANG['config_comment_admin_change_passwd']		= "//  CHANGER LE PASSWORD D'UN UTILSATEUR<br>\n//-----------------------------------------<br>\n// si à FALSE : l'administrateur ne peut pas changer le password des utilisateurs<br>\n// si à TRUE : l'administrateur peut changer le password des utilisateurs (TRUE est la valeur par defaut)";
$LANG['config_comment_affiche_bouton_config_pour_admin']			= "// ACCES A LA CONFIG DE L'APPLI POUR LES ADMINS<br>\n//-------------------------------------------------------<br>\n// si à FALSE : le bouton d'acces à la configuration de php_conges n'apparait pas sur la page administrateur (FALSE est la valeur par defaut)<br>\n// si à TRUE : le bouton d'acces à la configuration de php_conges apparait sur la page administrateur";
$LANG['config_comment_affiche_bouton_config_absence_pour_admin']	= "// ACCES A LA CONFIG DES TYPES D'ABSENCES DE L'APPLI POUR LES ADMINS<br>\n//---------------------------------------------------------------------<br>\n// si à FALSE : le bouton d'acces à la configuration des types d'absences gérées par php_conges n'apparait pas sur la page administrateur (FALSE est la valeur par defaut)<br>\n// si à TRUE : le bouton d'acces à la configuration des types d'absences gérées par php_conges apparait sur la page administrateur";
$LANG['config_comment_mail_new_demande_alerte_resp']	= "// ENVOI DE MAIL AU RESPONSABLE POUR UNE NOUVELLE DEMANDE :<br>\n//----------------------------------------------------------<br>\n// si à FALSE : le responsable ne reçoit pas de mail lors d'une nouvelle demande de congés par un utilisateur (FALSE est la valeur par defaut)<br>\n// si à TRUE : le responsable reçoit un mail d'avertissement à chaque nouvelle demande de congés d'un utilisateur\n";
$LANG['config_comment_mail_valid_conges_alerte_user']	= "// ENVOI DE MAIL AU USER POUR UN NOUVEAU CONGES SAISI OU VALIDE :<br>\n//----------------------------------------------------------------<br>\n// si à FALSE : le user ne reçoit pas de mail lorsque le responsable lui saisi ou accepte un nouveau conges (FALSE est la valeur par defaut)<br>\n// si à TRUE : le user reçoit un mail d'avertissement à chaque fois que le responsable saisi un nouveau congés ou accepte une demande pour lui\n";
$LANG['config_comment_mail_refus_conges_alerte_user']	= "// ENVOI DE MAIL AU USER POUR LE REFUS D'UNE DEMANDE DE CONGES :<br>\n//----------------------------------------------------------------<br>\n// si à FALSE : le user ne reçoit pas de mail lorsque le responsable refuse une de ses demandes de conges (FALSE est la valeur par defaut)<br>\n// si à TRUE : le user reçoit un mail d'avertissement à chaque fois que le responsable refuse une de ses demandes de congés.\n";
$LANG['config_comment_mail_annul_conges_alerte_user']	= "// ENVOI DE MAIL AU USER POUR L'ANNULATION PAR LE RESP D'UN CONGES DEJA VALIDE :<br>\n//---------------------------------------------------------------------------------<br>\n// si à FALSE : le user ne reçoit pas de mail lorsque le responsable lui annule un conges (FALSE est la valeur par defaut)<br>\n// si à TRUE : le user reçoit un mail d'avertissement à chaque fois que le responsable annule un de ses congés.\n";
$LANG['config_comment_serveur_smtp']					= "//  SERVEUR SMTP A UTILSER<br>\n//---------------------------------------<br>\n// adresse ip  ou  nom du serveur smpt à utiliser pour envoyer les mails<br>\n// Si vous ne maîtriser pas le serveur SMTP ou si, à l'utilisation, vous avez une érreur de connexion au serveur, laissez cette variable vide (\"\")";
$LANG['config_comment_where_to_find_user_email']		= "//  OU TROUVER LES ADRESSES MAIL DES UTILISATEURS<br>\n//-------------------------------------------------<br>\n// plusieurs possibilité pour retrouver les adresses mail des users :<br>\n// si à \"dbconges\" : le mail des user se trouve dans la table users de la database db_conges<br>\n// si à \"ldap\"     : le mail des user se trouve dans un annuaire LDAP que l'on va intérroger (cf fichier config_ldap.php)<br>\n// ATTENTION : toute autre valeur que \"dbconges\" ou \"ldap\" entrainera une érreur !!!";
$LANG['config_comment_samedi_travail']		= "//  GESTION DES SAMEDI COMME TRAVAILLES OU NON<br>\n//--------------------------------------------------------------------------------------<br>\n// on définit ici si les samedis sont travaillés ou pas.<br>\n// si à TRUE : le jour considéré est travaillé ....<br>\n// si à FALSE : le jour considéré n'est pas travaillé (weekend).... (FALSE est la valeur par defaut)";
$LANG['config_comment_dimanche_travail']	= "//  GESTION DES DIMANCHES COMME TRAVAILLES OU NON<br>\n//--------------------------------------------------------------------------------------<br>\n// on définit ici si les dimanches sont travaillés ou pas.<br>\n// si à TRUE : le jour considéré est travaillé ....<br>\n// si à FALSE : le jour considéré n'est pas travaillé (weekend).... (FALSE est la valeur par defaut)";
$LANG['config_comment_gestion_groupes']		= "//  GESTION DES GROUPES D'UTILISATEURS<br>\n//--------------------------------------<br>\n// on définit ici si l'on veut pouvoir gèrer les utilisateurs par groupe ou pas.<br>\n// si à TRUE : les groupes d'utilisateurs sont gèrés dans l'application ....<br>\n// si à FALSE : les groupes d'utilisateurs ne sont PAS gèrés dans l'application .... (FALSE est la valeur par defaut)";
$LANG['config_comment_affiche_groupe_in_calendrier']	= "//  AFFICHAGE DU CALENDRIER : tous les utilisateurs ou les utilisateurs d'un groupe seulement<br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : tous les personnes apparaissent sur le calendrier des congés (FALSE est la valeur par defaut)<br>\n// si à TRUE : seuls les personnes du m&ecirc;me  groupe que l'utilisateur apparaissent sur le calendrier des congés";
$LANG['config_comment_editions_papier']				= "//  EDITIONS PAPIER<br>\n//--------------------------------------<br>\n// on définit ici si le responsable peut générer des états papier des congés d'un user.<br>\n// si à TRUE : les éditions papier sont disponibles ....(TRUE est la valeur par defaut)<br>\n// si à FALSE : les éditions papier ne sont pas disponibles dans l'application ....";
$LANG['config_comment_texte_haut_edition_papier']	= "//  Texte en haut des EDITIONS PAPIER<br>\n//--------------------------------------<br>\n// on définit ici le texte événtuel qui figurera en haut de page des états papier des congés d'un user.";
$LANG['config_comment_texte_bas_edition_papier']	= "//  Texte au bas des EDITIONS PAPIER<br>\n//--------------------------------------<br>\n// on définit ici le texte événtuel qui figurera en bas de page des états papier des congés d'un user.";
$LANG['config_comment_user_echange_rtt']			= "//  ECHANGE RTT OU TEMPS PARTIEL AUTORISé POUR LES UTILISATEURS<br>\n//---------------------------------------------------------------------------------------<br>\n// on autorise ou non l'utilisateur à inverser ponctuellement une jour travaillé et un jour d'absence (de rtt ou temps partiel)<br>\n// si à FALSE : pas d'échange autorisé pour l'utilisateur (FALSE est la valeur par defaut)<br>\n// si à TRUE : échange autorisé pour l'utilisateur";
$LANG['config_comment_affiche_bouton_calcul_nb_jours_pris']	= "//  BOUTON DE CALCUL DU NB DE JOURS PRIS<br>\n//--------------------------------------------------------------------------------------<br>\n// si à FALSE : on n'affiche pas le bouton du calcul du nb de jours pris lors de la saisie d'une nouvelle abscence<br>\n// si à TRUE : affiche le bouton du calcul du nb de jours pris lors de la saisie d'une nouvelle abscence (TRUE est la valeur par defaut)<br>\n// ATTENTION : si est à TRUE : les jours chaumés doivent &ecirc;tre saisis (voir le module d'administration)";
$LANG['config_comment_rempli_auto_champ_nb_jours_pris']		= "//  REMPLISSAGE AUTOMATIQUE DU CHAMP LORS DE L'APPEL AU CALCUL DU NB DE JOURS PRIS<br>\n//--------------------------------------------------------------------------------------<br>\n// si à FALSE : l'appel au bouton de calcul du nb de jours pris ne rempli pas automatiquement le champ du formulaire (saisi à la main)<br>\n// si à TRUE : l'appel au bouton de calcul du nb de jours pris rempli automatiquement le champ du formulaire (TRUE est la valeur par defaut)";
$LANG['config_comment_duree_session']	= "// Durée max d'inactivité d'une session avant expiration (en secondes)";
$LANG['config_comment_stylesheet_file']	= "// FEUILLE DE STYLE<br>\n//--------------------------<br>\n// nom du fichier de la feuille de style à utiliser (avec chemin relatif depuis la racine de php_conges)";
$LANG['config_comment_bgcolor']			= "// couleur de fond des pages";
$LANG['config_comment_bgimage']			= "// image de fond des pages (PAS de / au début !!)";
$LANG['config_comment_light_grey_bgcolor']					= "// couleurs diverses (gris clair)";
$LANG['config_comment_php_conges_fpdf_include_path']		= "// CHEMIN VERS LE REPERTOIRE DE fpdf<br>\n//-------------------------------------------------------<br>\n// On défini ici le chemin pour accéder au répertoire de la librairie PHP \"fpdf\".<br>\n// Le chemin doit etre relatif depuis la racine de l'application php_conges.";
$LANG['config_comment_php_conges_phpmailer_include_path']	= "// CHEMIN VERS LE REPERTOIRE DE phpmailer<br>\n//-------------------------------------------------------<br>\n// On défini ici le chemin pour accéder au répertoire de la librairie PHP \"phpmailer\".<br>\n// Le chemin doit etre relatif depuis la racine de l'application php_conges.";
$LANG['config_comment_php_conges_cas_include_path']			= "// CHEMIN VERS LE REPERTOIRE DE cas<br>\n//-------------------------------------------------------<br>\n// On défini ici le chemin pour accéder au répertoire de la librairie PHP \"CAS\".<br>\n// Le chemin doit etre relatif depuis la racine de l'application php_conges.";
$LANG['config_comment_php_conges_authldap_include_path']	= "// CHEMIN VERS LE fichier authLDAP.php<br>\n//-------------------------------------------------------<br>\n// On défini ici le chemin pour accéder au répertoire de la librairie PHP \"authLDAP.php\".<br>\n// Le chemin doit etre relatif depuis la racine de l'application php_conges.";



/***************************/
// INSTALLATION PHP_CONGES
//page index
$LANG['install_le_fichier']		= "Le fichier";
$LANG['install_bad_fichier']	= "est introuvable dans le répertoire racine du nouveau php_conges, ou n'a pas des droits en lecture suffisants";
$LANG['install_read_the_file']	= "reportez vous au fichier";
$LANG['install_reload_page']	= "puis rechargez cette page";
$LANG['install_db_inaccessible']		= "la database n'est pas accessible";
$LANG['install_verifiez_param_file']	= "Veuillez vérifier les paramètres du fichier";
$LANG['install_verifiez_priv_mysql']	= "Assurez vous que la database, l'utilisateur et les privilèges MySql ont bien été créés.";
$LANG['install_install_phpconges']		= "Installation de php_conges";
$LANG['install_index_titre']			= "Application PHP_CONGES";
$LANG['install_no_prev_version_found']	= "Aucune version antèrieure n'a pu &ecirc;tre détermineé";
$LANG['install_indiquez']				= "Veuillez indiquer  s'il s'agit";
$LANG['install_nouvelle_install']		= "d'une Nouvelle Installation";
$LANG['install_mise_a_jour']			= "d'une Mise à Jour";
$LANG['install_indiquez_pre_version']	= "veuillez indiquer la version déjà installée";
$LANG['install_installed_version']		= "version déjà installée";
$LANG['install_configuration']			= "Configuration";
$LANG['install_config_appli']			= "configurer l'application";
$LANG['install_config_types_abs']		= "configurer les types de congés à gérer";
//page install
$LANG['install_install_titre']			= "Installation de l'application PHP_CONGES";
$LANG['install_impossible_sur_db']		= "impossible sur la database";
$LANG['install_verif_droits_mysql']		= "verifier les droits mysql de";
$LANG['install_puis']					= "puis";
$LANG['install_ok']						= "Installation effectuée avec succès";
$LANG['install_vous_pouvez_maintenant']	= "Vous pouvez maintenant";
$LANG['install_acceder_appli']			= "accéder à l'application";
//page mise_a_jour
$LANG['install_version_non_choisie']	= "la version à mettre à jour n'a pas été choisie";
$LANG['install_maj_titre_1']			= "Mise a jour";
$LANG['install_maj_titre_2']			= "Mise à jour de l'application PHP_CONGES";
$LANG['install_maj_passer_de']			= "vous &ecirc;tes sur le point de passer de la version";
$LANG['install_maj_a_version']			= "à la version";
$LANG['install_maj_sauvegardez']		= "Avant de continuer, prenez soin de faire une sauvegarde de votre base de données";
$LANG['install_etape']					= "etape";
$LANG['install_inaccessible']			= "n'est pas accessible";
$LANG['install_maj_conserv_config']		= "Afin d'assurer la conservation de votre configuration,";
$LANG['install_maj_copy_config_file']	= "veuillez copier votre ancien fichier config.php dans le nouveau répertoire";
$LANG['install_maj_whith_name']			= "sous le nom";
$LANG['install_maj_and']				= "et";
$LANG['install_maj_verif_droit_fichier']	= "verifier les droits de lecture sur ce fichier.";



/***********************/
/***********************/
/***********************/
// NEW : V1.2
$LANG['divers_date_traitement']	= "date-heure demande/traitement";
$LANG['divers_demande']		= "demande";
$LANG['divers_traitement']	= "traitement";
$LANG['divers_mois']		= "mois";
$LANG['divers_annee']		= "année";

$LANG['phpmailer_not_valid']	= "ERREUR MAIL : Le fichier class.phpmailer.php ne peut pas &ecirc;tre lu ou est introuvable. Le mail d'alerte ne pourra pas &ecirc;tre envoyé !";
$LANG['fpdf_not_valid']		= "ERREUR : Le fichier fpdf.php ne peut pas &ecirc;tre lu ou est introuvable. Le document PDF ne peut &ecirc;tre généré !";

$LANG['install_remove_fichier']		= "est désormais inutile... Veuillez le supprimer !";
$LANG['install_config_mail']	= "configurer les mails envoyés par php_conges";

/***********************/
// EXPORT ICAL / VCAL
$LANG['button_export_1']			= "Exporter ical / vcal";
$LANG['button_export_2']			= "Exporter les évenements au format ical / vcal";
$LANG['config_comment_disable_saise_champ_nb_jours_pris']	= "//  SAISIE INTERDITE DANS LE CHAMP TEXTE DU NB DE JOURS PRIS<br>\n//--------------------------------------------------------------------------------------<br>\n// si à FALSE : le champ texte du nb de jours pris est actif (saisi à la main possible)(FALSE est la valeur par defaut)<br>\n// si à TRUE : le champ texte du nb de jours pris et inactif (saisi à la main impossible)";
$LANG['calcul_nb_jours_commentaire']			= "attention: cette période chevauche d'autres congés !";
$LANG['calcul_nb_jours_commentaire_impossible']	= "CALCUL IMPOSSIBLE : cette période chevauche une demande de congés en cours !";
$LANG['config_comment_export_ical_vcal']	= "//  EXPORTER DES CONGES AU FORMAT ICS OU VCS<br>\n//----------------------------------------------------------------------<br>\n// si à FALSE : les uitilisateurs ne peuvent pas exporter leurs conges/absences au format ics ou vcs.<br>\n// si à TRUE : les uitilisateurs peuvent exporter leurs conges/absences au format ics ou vcs (pour importer dans dans agenda electroniques ou logiciel de planning). (TRUE est la valeur par defaut)";
$LANG['export_cal_titre']		= "Exporter au format ical / vcal";
$LANG['export_cal_from_date']	= "du";
$LANG['export_cal_to_date']		= "au";
$LANG['export_cal_saisir_debut']	= "sélection de la date de début";
$LANG['export_cal_saisir_fin']		= "sélection de la date de fin";
$LANG['export_cal_format']		= "choix du format";

/***************************/
// CONFIG
/*
$LANG['config_comment_semaine_bgcolor']			= "couleur de fond des jours de semaine dans le calendrier";
$LANG['config_comment_week_end_bgcolor']		= "couleur de fond des jours de week end dans le calendrier";
$LANG['config_comment_temps_partiel_bgcolor']	= "couleur de fond des jours de temps partiel ou de rtt dans le calendrier";
$LANG['config_comment_conges_bgcolor']			= "couleur de fond des jours de conges dans le calendrier (congés acceptés par le responsable)";
$LANG['config_comment_demande_conges_bgcolor']	= "couleur de fond des jours de conges demandés dans le calendrier (pas encore accordés par le responsable)";
$LANG['config_comment_absence_autre_bgcolor']	= "couleur de fond des jours d'absence pour mission, etc ... dans le calendrier";
*/

/***************************/
// MAIL DE PHP_CONGES
$LANG['mail_new_demande_comment']			= "message d'alerte au responsable en cas de nouvelle demande de conges.";
$LANG['mail_new_demande_resp_absent_comment']			= "message d'alerte aux autres responsables en cas d'absence du responsable titulaire.";
$LANG['mail_valid_conges_comment']			= "message d'alerte au user en cas de validation d'une demande de congés ou de saisie d'un nouveau conges par le responsable";
$LANG['mail_refus_conges_comment']			= "message d'alerte au user en cas de refus d'une demande de conges par le responsable";
$LANG['mail_annul_conges_comment']			= "message d'alerte au user en cas d'annulation de conges par le responsable";
$LANG['mail_prem_valid_conges_comment']		= "message d'alerte au user en cas de première validation d'une demande de congés par le responsable (si on utilise la double validation).";

$LANG['mail_remplace_url_accueil_comment']		= "__URL_ACCUEIL_CONGES__ sera automatiquement remplacé par un lien vers votre application php_conges.";
$LANG['mail_remplace_sender_name_comment']		= "__SENDER_NAME__ sera automatiquement remplacé par le nom et prénom de l'expediteur.";
$LANG['mail_remplace_destination_name_comment']	= "__DESTINATION_NAME__ sera automatiquement remplacé par le nom et le prénom du (des) destinataire(s).";
$LANG['mail_remplace_retour_ligne_comment']		= "__RETOUR_LIGNE__ sera automatiquement remplacé par passage à la ligne.";

/***********************/
// CONFIG MAILS
$LANG['config_mail_titre']		= "Configuration des mails envoyés par PHP_CONGES";
$LANG['config_mail_alerte_config']	= "Cette page ne sert que si l'envoie de mail par php_conges est validé (voir configuration de l'application).";
$LANG['config_mail_subject']	= "sujet du mail";
$LANG['config_mail_body']		= "corps du mail";

$LANG['admin_button_config_mail_1']		= "Configuration des mails envoyés par par php_conges";
$LANG['admin_button_config_mail_2']		= "Config Mails";
$LANG['config_comment_affiche_bouton_config_mail_pour_admin']	= "// ACCES A LA CONFIG DES MAILS DE L'APPLI POUR LES ADMINS<br>\n//---------------------------------------------------------------------<br>\n// si à FALSE : le bouton d'acces à la configuration des mails envoyés par php_conges n'apparait pas sur la page administrateur (FALSE est la valeur par defaut)<br>\n// si à TRUE : le bouton d'acces à la configuration des mails envoyés par php_conges apparait sur la page administrateur";
$LANG['config_comment_mail_prem_valid_conges_alerte_user']	= "// ENVOI DE MAIL AU USER ET GRAND REPONSABLE APRES LA PREMIERE VALIDATION D'UNE DEMANDE (cas d'une double validation de demande) :<br>\n//----------------------------------------------------------------<br>\n// si à FALSE : le user et le grand responsables ne reçoivent pas de mail lorsque le responsable valide une demande (première validation). (FALSE est la valeur par defaut)<br>\n// si à TRUE : le user et le grand responsables reçoivent un mail d'alerte lorsque le responsable valide une demande (première validation).\n";
$LANG['config_comment_affiche_date_traitement']	= "// AFFICHAGE DES DATES ET HEURE DE TRAITEMENT DANS LES HISTORIQUES DES CONGES<br>\n//---------------------------------------------------------------------<br>\n// si à FALSE : les dates et heures de demande de congeés ou de validation de demande ne sont pas affichés dans les tableaux (FALSE est la valeur par defaut)<br>\n// si à TRUE : les dates et heures de demande de congeés ou de validation de demande sont affichés dans les tableaux et éditions (attention, cela augmente considérablement la largeur de la page affichée).";

/***********************/
// DOUBLE VALIDATION
$LANG['config_comment_double_validation_conges']	= "//  DOUBLE VALIDATION DES DEMANDES DE CONGES<br>\n//----------------------------------------------------------------------<br>\n// si à FALSE : la demande de congés d'un utilisateur est acceptée ou non par le responsable (une seule validation)(FALSE est la valeur par defaut).<br>\n// si à TRUE : la demande de congés d'un utilisateur doit &ecirc;tre validée par le responsable, puis acceptée par le responsable du responsable (double validation).<br>\nATTENTION, ce paramètre n'est valable QUE si dans le cas d'une gestion par groupe de utlisateurs !)";
$LANG['admin_groupes_double_valid']		= "double validation";
$LANG['admin_gestion_groupe_grand_resp_responsables']	= "Grands Responsables du Groupe";
$LANG['divers_grand_responsable_maj_1']	= "Grand_Responsable";
$LANG['resp_traite_demandes_titre_tableau_1']	= "Demandes à valider";
$LANG['resp_traite_demandes_titre_tableau_2']	= "Demandes en deuxième validation";
$LANG['resp_traite_user_etat_demandes_2_valid']		= "Etat des demandes en deuxième validation :";
$LANG['resp_etat_users_titre_double_valid']		= "Utilisateurs avec double validation des demandes";
$LANG['resp_etat_aucun_user']	= "Aucun utilisateur pour ce responsable !!!";

/***********************/
// IMPRIM CALENDRIER
$LANG['button_imprim_calendar']	= "Imprimer calendrier";
$LANG['imprim_calendrier_titre']	= "Choisissez le mois à imprimer";



/***********************/
/***********************/
/***********************/
// NEW : V1.2.1

$LANG['install_test_mail']	= "tester l'envoi de mails de php_conges";

$LANG['session_pas_session_ouverte']	= "Pas de session ouverte." ;

$LANG['divers_acces_page_interdit']	= "ACCES A CETTE PAGE INTERDIT !!" ;
$LANG['divers_user_disconnected']	= "Vous avez été déconnecté de l'application !" ;
$LANG['divers_veuillez']			= "Veuillez" ;
$LANG['divers_vous_authentifier']	= "Vous authentifier" ;

/***************************/
// CONFIGURATION PHP_CONGES
$LANG['14_Presentation']			= "14 Présentation";
$LANG['config_comment_affiche_soldes_calendrier']	= "//  AFFICHAGE DES SOLDES UTILISATEURS DANS LE CALENDRIER : <br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : les soldes de congés des utilisateurs n'apparaissent pas sur le calendrier des congés<br>\n// si à TRUE : les soldes de congés des utilisateurs apparaissent sur le calendrier des congés (TRUE est la valeur par defaut)";
$LANG['mail_remplace_nb_jours']			= "__NB_OF_DAY__ sera automatiquement remplacé par le nombre des jours de l'absence concernée.";



/***********************/
/***********************/
/***********************/
// NEW : V1.3.0

/***********************/
$LANG['divers_traitement_ok']		= "accepté";
$LANG['divers_traitement_refus']	= "refusé";
$LANG['divers_traitement_annul']	= "annulé";

// GESTION CONGES EXCEPTIONNELS
$LANG['config_comment_gestion_conges_exceptionnels']    = "//  CONGES EXCEPTIONNELS<br>\n//----------------------------------------------------------------------<br>\n// congés avec un  nombre de jours annuel toujours à 0<br>\n// si à FALSE : les congés de type 'congés exceptionnels' ne sont pas gérés.(FALSE est la valeur par defaut)<br>\n// si à TRUE : il est possible de définir des congés de type 'conges exceptionnels' pour les utilisateurs (congés sans solde annuel).";
$LANG['config_abs_comment_conges_exceptionnels']		= "Les types de conges listés ici n'ont pas de taux annuel." ;
$LANG['config_abs_desactive_cong_excep_impossible']		= "IMPOSSIBLE de désactiver les congés exceptionnels! (aucun congés exceptionnel ne doit &ecirc;tre enregistré pour effectuer cette opération.)";

$LANG['divers_conges_exceptionnels']		= "congés exceptionnels";
$LANG['divers_conges_exceptionnels_maj_1']	= "Congés Exceptionnels";
$LANG['divers_semaine']	= "semaine";

// CONFIG
$LANG['config_comment_grand_resp_ajout_conges']    = "//  AJOUT DE CONGES PAR LE GRAND RESPONSABLE<br>\n//----------------------------------------------------------------------<br>\n// ne fonctionne QUE si la double validation est activée !<br>\n// si à FALSE : un responsable ne peut ajouter (créditer) de congés qu'à ses utilisateur direct (dont il est 'responsable').(FALSE est la valeur par defaut)<br>\n// si à TRUE : un responsable peut ajouter (créditer) de congés aux utilisateur dont il est 'responsable' ET 'grand responsable' !";
$LANG['config_comment_interdit_saisie_periode_date_passee']    = "//  INTERDIRE LES SAISIES DE CONGES POUR LES DATES PASSEES<br>\n//----------------------------------------------------------------------<br>\n//si à FALSE : il est possible de saisir une demande de congés pour une date passée.(FALSE est la valeur par defaut)<br>\n// si à TRUE : il est interdit (et impossible) de saisir une demande de congés pour une date passée. ";



/***********************/
/***********************/
/***********************/
// NEW : V1.3.1

/***********************/
$LANG['calcul_nb_jours_commentaire_bad_date']	= "attention: date de fin antérieure à date de début !";
$LANG['calcul_impossible']						= "attention: Calcul Impossible !";
$LANG['jours_feries_non_saisis']				= "les jours fériés ne sont pas enregistrés pour l'année voulue.";
$LANG['contacter_admin']						= "Contactez votre administrateur.";

// CONFIG
$LANG['config_comment_interdit_modif_demande']    = "//  INTERDIRE LA MODIFICATION D'UNE DEMANDE DE CONGES PAR UN UTILISATEUR<br>\n//----------------------------------------------------------------------<br>\n//si à FALSE : l'utilisateur peut modifier une demande de congés existante.(FALSE est la valeur par defaut)<br>\n// si à TRUE : il est interdit (et impossible) pour l'utilisateur de modifier une demande de congés existante. ";





/***********************/
/***********************/
/***********************/
// NEW : V1.3.2

/***********************/
$LANG['calendrier_afficher_groupe']	= "groupe à afficher";
$LANG['divers_groupe']				= "groupe";
$LANG['config_logs']			= "voir les logs";
$LANG['config_logs_titre_1']	= "Gestion des logs de PHP_CONGES";
$LANG['config_logs_titre_2']	= "Gestion des logs";
$LANG['voir_les_logs_par']		= "pour voir les actions d'un seul user : cliquez sur son login en gras.";
$LANG['voir_tous_les_logs']		= "Pour voir tous les logs";
$LANG['divers_cliquez_ici']		= "cliquez ici";
$LANG['form_delete_logs']		= "Vider les Logs";
$LANG['confirm_vider_logs']		= "Vouler vous vraiment éffacer tous les LOGS de PHP_CONGES ??";
$LANG['no_logs_in_db']			= "Pas de Logs dans la DataBase !";

$LANG['divers_date_maj_1']		= "Date";
$LANG['divers_fait_par_maj_1']	= "Fait par";
$LANG['divers_pour_maj_1']		= "Pour";

// ajout d'un user
$LANG['admin_gestion_groupe_users_group_of_new_user']	= "Groupes auxquels le user appartient";

// CONFIG
$LANG['config_comment_calendrier_select_all_groups']    = "//  AFFICHAGE DU CALENDRIER : Selection du groupe : choix entre tous les groupes ou non<br>\n//--------------------------------------------------------------------------------------------<br>\n// si la gestion des groupes est active, on définit ici si le choix du groupe à afficher dans le calendrier inclus tous les groupes ou pas.<br>\n// si à FALSE : seuls les groupes de l'utilisateur (ou dont il est responsable) apparaissent dans la sélection sur le calendrier des congés (FALSE est la valeur par defaut)<br>\n// si à TRUE : tous les groupes apparaissent dans la sélection sur le calendrier des congés";
$LANG['config_comment_consult_calendrier_sans_auth']    = "//  AFFICHAGE DU CALENDRIER SANS AUTHENTIFICATION<br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : Il n'est pas possible d'afficher le calendrier des congés sans &ecirc;tre connecté à PHP_CONGES. (FALSE est la valeur par defaut)<br>\n// si à TRUE : Possibilité de consulter le calendrier des congés sans se connecter à PHP_CONGES (via un lien sur la page d'accueil, ou avec l'URL directe).";
$LANG['config_comment_resp_ajoute_conges']				= "//  AJOUT DE JOURS DE CONGES PAR LE RESPONSABLE<br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : Le responsable ne peut pas ajouter (créditer) de jours de congés à ses utilisateurs.<br>\n// si à TRUE : Le responsable peut ajouter (créditer) de jours de congés à ses utilisateurs. (FALSE est la valeur par defaut)";


// CONFIGURATION MAILS
$LANG['mail_remplace_date_debut']	= "__DATE_DEBUT__ sera automatiquement remplacé par la date de début de l'absence concernée.";
$LANG['mail_remplace_date_fin']		= "__DATE_FIN__   sera automatiquement remplacé par la date de fin   de l'absence concernée.";





/***********************/
/***********************/
/***********************/
// NEW : V1.4.0

/***********************/
// CONFIGURATION MAILS
$LANG['mail_remplace_commentaire']	= "__COMMENT__    sera automatiquement remplacé par le commentaire   de l'absence concernée.";
$LANG['mail_remplace_type_absence']	= "__TYPE_ABSENCE__   sera automatiquement remplacé par le libellé   de l'absence concernée.";

// PARTIE ADMINISTRATEUR
$LANG['admin_button_jours_fermeture_1']       = "Jours fermeture";
$LANG['admin_button_jours_fermeture_2']       = "Jours fermeture";
// page admin_jours_fermeture
$LANG['admin_jours_fermeture_titre']            = "Saisie jours fermeture";
$LANG['admin_jours_fermeture_fermeture_pour_tous']	= "Fermeture pour tous";
$LANG['admin_jours_fermeture_fermeture_par_groupe']	= "Fermeture par groupe";
$LANG['admin_jours_fermeture_new_fermeture']	= "Nouvelle Fermeture";
$LANG['admin_jours_fermeture_enregistrees']		= "Fermetures Enregistrées";
$LANG['admin_jours_fermeture_dates_incompatibles']		= "dates saisies incompatibles !!! veuillez recommencer";
$LANG['admin_jours_fermeture_date_passee_error']		= "dates passées impossibles !!! veuillez recommencer";
$LANG['admin_jours_fermeture_annee_non_saisie']			= "les jours feriés de ces années ne sont pas saisie !!! veuillez recommencer ou saisir les jours fériés pour ces années";
$LANG['admin_jours_fermeture_chevauche_periode']		= "Erreur: la fermeture saisie chevauche une autre fermeture ou un congé pris par un utilisateur concerné !!! veuillez recommencer";
$LANG['admin_jours_fermeture_fermeture_aujourd_hui']	= "Erreur: la fermeture saisie débute ou finie aujourd'hui, ce qui est interdit !!! veuillez recommencer";
$LANG['admin_jours_fermeture_affect_type_conges']		= "à quel type de conges affecter cette fermeture : ";
$LANG['admin_annuler_fermeture']           		= "Annuler cette Fermeture";
$LANG['admin_annul_fermeture_confirm']          = "Veuillez confirmer cette Annulation";
$LANG['config_comment_fermeture_par_groupe']	= "//  GESTION DES FERMETURES PAR GROUPE : Fermetures d'établissement / de service par groupe ou non<br>\n//--------------------------------------------------------------------------------------------<br>\n// si la gestion des groupes est active, on définit ici si les fermetures d'établissement sont différentes suivant les groupes d'utilisateurs ou pas.<br>\n// si à FALSE : les fermetures s'appliquent à tous les parsonnels (la fermeture leur est débitée en congés) (FALSE est la valeur par defaut)<br>\n// si à TRUE : les fermetures d'établissement / de service sont gérées par groupe et peuvent &ecirc;tre différentes d'un groupe à l'autre.";
$LANG['divers_fermeture']		= "fermeture";
$LANG['divers_fermeture_du']	= "fermeture du";
$LANG['divers_du']				= "du";
$LANG['divers_au']				= "au";
$LANG['divers_date_debut']		= "date de début";
$LANG['divers_date_fin']		= "date de fin";
$LANG['divers_confirmer_maj_1']		= "Confirmer";
// CONFIG
$LANG['config_comment_affiche_demandes_dans_calendrier']	= "//  AFFICHAGE DES DEMANDES DE CONGES DES UTILISATEURS DANS LE CALENDRIER : <br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : les demandes de congés de tous les utilisateurs n'apparaissent pas sur le calendrier des congés. (seules les demandes de l'utilisateur connecté apparaissent) (FALSE est la valeur par defaut)<br>\n// si à TRUE : les demandes de congés de tous les utilisateurs apparaissent sur le calendrier des congés.";
$LANG['config_comment_calcul_auto_jours_feries_france']		= "//  SAISIE DES JOURS FERIES DE L'ANNEE : <br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : la saisie des jours fériés de l'année par l'administrateur se fait à la main. (FALSE est la valeur par defaut)<br>\n// si à TRUE : lors de la saisie des jours fériés de l'année par l'administrateur, les jours fériés (de France) sont automatiquement renseignés (iln'y a plus qu'à vérifier et valider).";
$LANG['config_comment_gestion_cas_absence_responsable']		= "//  PRISE EN COMPTE DES ABSENCES DU RESPONSABLE : <br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : en cas d'absence de leur responsable, les demandes des utilisateurs attendent le retour de celui ci. (FALSE est la valeur par defaut)<br>\n// si à TRUE : en cas d'absence de leur responsable, les demandes des utilisateurs sont transmises au responsable du responsable qui peut alors les traiter.";
// DIVERS
$LANG['divers_normal_maj_1']		= "Tous";



/***********************/
/***********************/
/***********************/
// NEW : V1.4.1

/***********************/
// CONFIG
$LANG['config_comment_texte_page_login']		= "// TEXTE DE LA PAGE D'ACCUEIL<br>\n//------------------<br>\n// texte qui apparaitra sous l'image sur la page de login (peut &ecirc;tre vide)";
$LANG['config_comment_solde_toujours_positif']	= "//  SOLDES TOUJOURS POSITIFS : <br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : le solde d'un congé peut &ecirc;tre négatif. (FALSE est la valeur par defaut)<br>\n// si à TRUE : le solde d'un congé ne peut pas &ecirc;tre négatif (un utilisateur ne peut poser un congé si son solde devient négatif).";
// VERIF SOLDE POSITIF
$LANG['verif_solde_erreur_part_1']	= "Attention le nombre de jours d'absence demandés";
$LANG['verif_solde_erreur_part_2']	= "est supérieur à votre solde (somme du solde";
$LANG['verif_solde_erreur_part_3']	= "et des congés à valider";


/***********************/
/***********************/
/***********************/
// NEW : V1.5.0

/***********************/
// ADMIN
$LANG['admin_groupes_nb_users']        = "nb membres";
$LANG['resp_cloture_exercice_titre']   = "cloture/début d'exercice";
$LANG['divers_cloturer_maj_1']         = "Cloturer";
$LANG['divers_reliquat']        	   = "reliquat";
$LANG['button_cloture']  		       = "Changement Exercice";
$LANG['resp_cloture_exercice_all']     = "Cloture d'exercice globale pour Tous";
$LANG['resp_cloture_exercice_groupe']  = "Cloture d'exercice globale par groupe";
$LANG['resp_cloture_exercice_users']   = "Cloture d'exercice par personne";
$LANG['resp_cloture_exercice_for_all_text_confirmer']     = "Confirmez la cloture de l'exercice en cours et le début de l'exercice suivant pour TOUS les utilisateurs de l'application ?";
$LANG['resp_cloture_exercice_for_groupe_text_confirmer']  = "Confirmez la cloture de l'exercice en cours et le début de l'exercice suivant pour tous les utilisateurs du groupe séléctionné ?";
$LANG['form_valid_cloture_global']     = "Valider la cloture globale";
$LANG['form_valid_cloture_group']      = "Valider la cloture pour le groupe";
$LANG['resp_cloture_exercice_commentaire']	= "cloture exercice";
// CONFIG
$LANG['config_comment_autorise_reliquats_exercice']	= "// RELIQUATS AUTORISES D'UN EXERCICE SUR L'AUTRE : <br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : le solde d'un congé ne peut pas &ecirc;tre reporté comme reliquat sur l'exercice suivant.<br>\n// si à TRUE : le solde d'un congé peut &ecirc;tre reporté comme reliquat sur l'exercice suivant.. (TRUE est la valeur par defaut)";
$LANG['config_comment_nb_maxi_jours_reliquats']		= "// NOMBRE MAX DE JOURS DE RELIQUATS AUTORISES D'UN EXERCICE SUR L'AUTRE : <br>\n//--------------------------------------------------------------------------------------------<br>\n// Nombre maximum de jours qui peut &ecirc;tre reporté comme reliquat sur l'exercice suivant (les jours au dela du maxi sont perdus). Mettre à 0 (zero) si pas de limite. (0 est la valeur par defaut)";
$LANG['config_comment_jour_mois_limite_reliquats']	= "// DATE LIMITE D'UTILISATION DES RELIQUATS : <br>\n//------------------<br>\n// (si les reliquats sont autorisés) : date maximum dans l'année pour utiliser ses reliquats de congés de l'exercice précédent (au dela, ils sont perdus) (date au format JJ-MM) (égal à 0 si pas de date limite)";
//
$LANG['config_jour_mois_limite_reliquats_modif_impossible']	= "IMPOSSIBLE de modifier la date limite des reliquats ! (format invalide !)";
//
$LANG['lang']['session_pas_de_compte_dans_db']   = "Il n'existe pas de compte correspondant à votre login dans la base de données de PHP_CONGES<br>\n";
$LANG['lang']['session_contactez_admin']   = "Contactez l'administrateur de php_conges";


/***********************/
/***********************/
/***********************/
// Nouveau pour gestion RH

/***********************/

$LANG['resp_menu_button_mode_hr']    = "Mode RH";
$LANG['config_comment_titre_hr_index']      = "Titre des pages RH";
$LANG['hr_menu_titre']                     = "MODE RH:";
$LANG['admin_users_is_hr']         = "est RH";
$LANG['admin_users_is_resp']         = "Droits responsable ?";
$LANG['admin_users_resp_login']         = "Login du responsable";
$LANG['admin_users_is_admin']         = "Droits admin ?";
$LANG['admin_users_is_hr']         = "Droits RH ?";
$LANG['admin_users_see_all']         = "see_all";
$LANG['admin_new_users_is_hr']         = "Droits RH ?";
$LANG['hr_traite_user_etat_conges']         = "Etat des congés des Utilisateurs :";

// FIN DES VARIABLES A RENSEIGNER :
/*************************************************************************************************/
$_SESSION['lang']=$LANG ;

