<?php
/************************************************************************************************
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

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

// Fichier de Langue FR (FRANCAIS )
$LANG=array();
/*************************************************************************************************/
// VARIABLES A RENSEIGNER :



/***********************/
// MOIS ET JOURS
$LANG['janvier']    = "January";
$LANG['fevrier']    = "February";
$LANG['mars']       = "March";
$LANG['avril']      = "April";
$LANG['mai']        = "May";
$LANG['juin']       = "June";
$LANG['juillet']    = "July";
$LANG['aout']       = "Août";
$LANG['septembre']  = "September";
$LANG['octobre']    = "October";
$LANG['novembre']   = "November";
$LANG['decembre']   = "Décember";

$LANG['lundi']      = "monday";
$LANG['mardi']      = "tuesday";
$LANG['mercredi']   = "wednesday";
$LANG['jeudi']      = "thursday";
$LANG['vendredi']   = "friday";
$LANG['samedi']     = "saturday";
$LANG['dimanche']   = "sunday";

$LANG['lundi_short']        = "mon";
$LANG['mardi_short']        = "tue";
$LANG['mercredi_short']     = "wed";
$LANG['jeudi_short']        = "thu";
$LANG['vendredi_short']     = "fri";
$LANG['samedi_short']       = "sat";
$LANG['dimanche_short']     = "sun";

$LANG['lundi_2c']       = "mo";
$LANG['mardi_2c']       = "tu";
$LANG['mercredi_2c']    = "we";
$LANG['jeudi_2c']       = "th";
$LANG['vendredi_2c']    = "fr";
$LANG['samedi_2c']      = "sa";
$LANG['dimanche_2c']    = "su";

$LANG['lundi_1c']       = "M";
$LANG['mardi_1c']       = "T";
$LANG['mercredi_1c']    = "W";
$LANG['jeudi_1c']       = "T";
$LANG['vendredi_1c']    = "F";
$LANG['samedi_1c']      = "S";
$LANG['dimanche_1c']    = "S";



/***********************/
// BOUTONS COMMUNS
$LANG['button_deconnect']   = "Disconnection";
$LANG['button_refresh']     = "Refresh Page";
$LANG['button_editions']    = "Paper Prints";
$LANG['button_admin_mode']  = "Administrator Mode";
$LANG['button_calendar']    = "Display calendar";



/***********************/
// FORMULAIRES 
$LANG['form_ok']            = "OK";
$LANG['form_submit']        = "Validate";
$LANG['form_cancel']        = "Abandon";
$LANG['form_retour']        = "Return";
$LANG['form_ajout']         = "Add";
$LANG['form_supprim']       = "Delete";
$LANG['form_modif']         = "Modify";
$LANG['form_annul']         = "Cancel";
$LANG['form_redo']          = "Start again";
$LANG['form_am']            = "morning";
$LANG['form_pm']            = "afternoon";
$LANG['form_day']           = "full day";
$LANG['form_close_window']  = "Close this Window";
$LANG['form_save_modif']    = "Save modifications";
$LANG['form_modif_ok']      = "Modifications saved with success !";
$LANG['form_modif_not_ok']  = "ERROR ! Modifications NOT saved !";
$LANG['form_valid_global']  = "Validate global input";
$LANG['form_valid_groupe']  = "Validate input for the Group";
$LANG['form_password']      = "Password";
$LANG['form_start']         = "Start";
$LANG['form_continuer']     = "Continue";



/***********************/
// DIVERS
$LANG['divers_quotite']         = "quota";
$LANG['divers_quotite_maj_1']   = "Quota";
$LANG['divers_an']              = "year";
$LANG['divers_an_maj']          = "YEAR";
$LANG['divers_solde']           = "balance";
$LANG['divers_solde_maj']       = "BALANCE";
$LANG['divers_solde_maj_1']     = "Balance";
$LANG['divers_debut_maj']       = "BEGINNING";
$LANG['divers_debut_maj_1']     = "Beginning";
$LANG['divers_fin_maj']         = "END";
$LANG['divers_fin_maj_1']       = "End";
$LANG['divers_type']            = "type";
$LANG['divers_type_maj_1']      = "Type";
$LANG['divers_comment_maj_1']   = "Comment";
$LANG['divers_etat_maj_1']      = "Status";
$LANG['divers_nb_jours_pris_maj_1'] = "nb Days Taken";
$LANG['divers_nb_jours_maj_1']  = "nb Days";
$LANG['divers_inconnu']         = "unknown";
$LANG['divers_motif_refus']     = "Motive for the refusal";
$LANG['divers_motif_annul']     = "motive for the cancellation";
$LANG['divers_refuse']          = "refused";
$LANG['divers_annule']          = "canceled";
$LANG['divers_login']           = "login";
$LANG['divers_login_maj_1']     = "Login";
$LANG['divers_personne_maj_1']  = "Person";
$LANG['divers_responsable_maj_1']   = "Manager";
$LANG['divers_nom_maj']         = "NAME";
$LANG['divers_nom_maj_1']       = "Name";
$LANG['divers_prenom_maj']      = "FIRST NAME";
$LANG['divers_prenom_maj_1']    = "First name";
$LANG['divers_accepter_maj_1']  = "Accept";
$LANG['divers_refuser_maj_1']   = "Refuse";
$LANG['divers_fermer_maj_1']    = "Close";
$LANG['divers_am_short']        = "am";
$LANG['divers_pm_short']        = "pm";
$LANG['divers_conges']          = "holidays";
$LANG['divers_conges_maj_1']    = "Holidays";
$LANG['divers_absences']        = "absences";
$LANG['divers_absences_maj_1']  = "Absences";
$LANG['divers_nouvelle_absence']        = "New Absence";
$LANG['divers_mois_precedent']          = "previous month";
$LANG['divers_mois_precedent_maj_1']    = "Previous month";
$LANG['divers_mois_suivant']            = "next month";
$LANG['divers_mois_suivant_maj_1']      = "Next month";




/***********************/
// PARTIE UTILISATEUR
//divers
$LANG['user']               = "User";

//onglets
$LANG['user_onglet_echange_abs']        = "Exchange the absence day";
$LANG['user_onglet_demandes']           = "Current demands";
$LANG['user_onglet_historique_conges']  = "Holidays historic";
$LANG['user_onglet_historique_abs']     = "Other absences historic";
$LANG['user_onglet_change_passwd']      = "Change password";

//titres des pages
$LANG['user_echange_rtt']               = "Exchange the rtt day, part-time / day worked";
$LANG['user_etat_demandes']             = "State of the current demands";
$LANG['user_historique_conges']         = "Holidays historic";
$LANG['user_historique_abs']            = "Historic of the absences for mission, training, etc....";
$LANG['user_change_password']           = "Change your password";

//page etat des demandes
$LANG['user_demandes_aucune_demande']   = "No current demand ...";

//page historique des conges
$LANG['user_conges_aucun_conges']       = "No holidays in the database ...";

//page historique des absences
$LANG['user_abs_aucune_abs']            = "No  absences in the database ...";
$LANG['user_abs_type']              = "Absence";

//page changer password
$LANG['user_passwd_saisie_1']       = "1st input";
$LANG['user_passwd_saisie_2']       = "2nd input";
$LANG['user_passwd_error']          = "ERROR ! both inputs are different or empty !!";


//page modification demande / absence
$LANG['user_modif_demande_titre']       = "Modification of a demand/absence.";

//page suppression demande / absence
$LANG['user_suppr_demande_titre']       = "Deletion holidays demand .";





/***********************/
// PARTIE RESPONSABLE
//menu
$LANG['resp_menu_titre']                    = "PERSON IN CHARGE MODE :";
$LANG['resp_menu_button_retour_main']       = "Return to Main page";
$LANG['resp_menu_button_traite_demande']    = "Handle all Demands";
$LANG['resp_menu_button_affiche_user']      = "display person";
$LANG['resp_menu_button_ajout_jours']       = "Add Days Holidays";
$LANG['resp_menu_button_mode_user']         = "User Mode";
$LANG['resp_menu_button_mode_admin']        = "Administrator Mode";

//page etat des conges des users
$LANG['resp_etat_users_afficher']   = "Display";
$LANG['resp_etat_users_imprim']     = "Paper Edition";
//page traite toutes les demandes
$LANG['resp_traite_demandes_titre']             = "Holidays demands processing :";
$LANG['resp_traite_demandes_aucune_demande']    = "No current holidays demand in the database ...";
$LANG['resp_traite_demandes_nb_jours']          = "nb Days<br>Taken";
$LANG['resp_traite_demandes_attente']           = "Pending";
$LANG['resp_traite_demandes_motif_refus']       = "Refusal<br>motive";
//page ajout conges
$LANG['resp_ajout_conges_titre']                = "Holidays adding :";
$LANG['resp_ajout_conges_nb_jours_ajout']       = "NB of days to add";
$LANG['resp_ajout_conges_ajout_all']            = "Global adding for All :";
$LANG['resp_ajout_conges_nb_jours_all_1']       = "Number of days of";
$LANG['resp_ajout_conges_nb_jours_all_2']       = "to add to all :";
$LANG['resp_ajout_conges_calcul_prop']          = "Proportional calculation in the quota of each person :";
$LANG['resp_ajout_conges_oui']                  = "YES";
$LANG['resp_ajout_conges_calcul_prop_arondi']   = "the proportional calculation is rounded to the closest 1/2";
$LANG['resp_ajout_conges_ajout_groupe']         = "Adding by Group : (adding to all members of a group)";
$LANG['resp_ajout_conges_choix_groupe']         = "group choice";
$LANG['resp_ajout_conges_nb_jours_groupe_1']    = "Number of days of";
$LANG['resp_ajout_conges_nb_jours_groupe_2']    = "to add to the group :";
$LANG['resp_ajout_conges_comment_periode_user'] = "adding day";
$LANG['resp_ajout_conges_comment_periode_all']  = "adding for all the staffs";
$LANG['resp_ajout_conges_comment_periode_groupe']   = "adding for the group";
//page traite user
$LANG['resp_traite_user_titre']             = "Processing of :";
$LANG['resp_traite_user_new_conges']        = "New Holidays/Absence :";
$LANG['resp_traite_user_etat_demandes']     = "State of the demands :";
$LANG['resp_traite_user_etat_conges']       = "State of the holidays :";
$LANG['resp_traite_user_aucune_demande']    = "No holidays demand for that person in the database ...";
$LANG['resp_traite_user_motif_refus']       = "Refusal motive";
$LANG['resp_traite_user_aucun_conges']      = "No holidays demand for that person in the database ...";
$LANG['resp_traite_user_motif_possible']    = "refusal motive or possible cancellation";
$LANG['resp_traite_user_annul']             = "Cancel";
$LANG['resp_traite_user_motif_annul']       = "cancellation motive";
$LANG['resp_traite_user_motif']             = "motive";
$LANG['resp_traite_user_valeurs_not_ok']    = "ERROR ! The values typed in are wrong or missing  !!!";





/***********************/
// PARTIE ADMINISTRATEUR
//divers
$LANG['admin_titre']                    = "Administration of users";
$LANG['admin_button_close_window_1']    = "Closing of the Administrator mode";
$LANG['admin_button_config_1']          = "Configuration of php_conges";
$LANG['admin_button_config_2']          = "Configuration";
$LANG['admin_button_config_abs_1']      = "Configuration of the absence types managed by php_conges";
$LANG['admin_button_config_abs_2']      = "Config Absences";
$LANG['admin_button_jours_chomes_1']    = "input of days off";
$LANG['admin_button_jours_chomes_2']    = "input of days off";
$LANG['admin_button_save_db_1']         = "Database Backup/Restoration";
$LANG['admin_button_save_db_2']         = "Database Backup/Restoration";
//
$LANG['admin_onglet_gestion_user']      = "Users Management";
$LANG['admin_onglet_add_user']          = "Adding a User";
$LANG['admin_onglet_gestion_groupe']    = "Groups Management";
$LANG['admin_onglet_groupe_user']       = "Groups Management <-> Users";
$LANG['admin_onglet_user_groupe']       = "Users Management <-> Groups";
$LANG['admin_onglet_groupe_resp']       = "Groups Management <-> Managers";
$LANG['admin_onglet_resp_groupe']       = "Managers Management <-> Groups";
//
$LANG['admin_verif_param_invalides']    = "WARNING : some fields values are invalid ......";
$LANG['admin_verif_login_exist']        = "WARNING : login already used, please change login ......";
$LANG['admin_verif_bad_mail']           = "WARNING : wrong mail address ......";
$LANG['admin_verif_groupe_invalide']    = "WARNING : group name already used, please change group name ......";
// page gestion utilisateurs
$LANG['admin_users_titre']              = "Users State";
$LANG['admin_users_is_resp']            = "is_resp";
$LANG['admin_users_resp_login']         = "resp_login";
$LANG['admin_users_is_admin']           = "is_admin";
$LANG['admin_users_see_all']            = "see_all";
$LANG['admin_users_mail']               = "email";
$LANG['admin_users_password_1']         = "password1";
$LANG['admin_users_password_2']         = "password2";
// page ajout utilisateur
$LANG['admin_new_users_titre']          = "New User :";
$LANG['admin_new_users_is_resp']        = "is_manager";
$LANG['admin_new_users_is_admin']       = "is_administrator";
$LANG['admin_new_users_see_all']        = "see_all";
$LANG['admin_new_users_password']       = "password";
$LANG['admin_new_users_nb_par_an']      = "nb / year";
// page ajout utilisateur
$LANG['admin_groupes_groupe']           = "Group";
$LANG['admin_groupes_libelle']          = "description";
$LANG['admin_groupes_new_groupe']       = "New Group :";
// page gestion groupes
$LANG['admin_gestion_groupe_etat']      = "Groups State";
//
$LANG['admin_aff_choix_groupe_titre']   = "Group Choice";
$LANG['admin_aff_choix_user_titre']     = "User Choice";
$LANG['admin_aff_choix_resp_titre']     = "Manager Choice";
// page gestion groupe <-> users
$LANG['admin_gestion_groupe_users_membres'] = "Members of the Group";
$LANG['admin_gestion_groupe_users_group_of_user']   = "Groups to which belongs";
// page gestion groupe <-> users
$LANG['admin_gestion_groupe_resp_groupes']      = "Groups of the Manager";
$LANG['admin_gestion_groupe_resp_responsables'] = "Managers of the Group";
// page change password user
$LANG['admin_chg_passwd_titre']     = "User Password modification";
// page admin_suppr_user
$LANG['admin_suppr_user_titre']     = "User Deletion";
// page admin_modif_user
$LANG['admin_modif_user_titre']     = "User Modification";
$LANG['admin_modif_nb_jours_an']    = "nb days / year";
// grille saisie temps partiel et RTT
$LANG['admin_temps_partiel_titre']          = "input of absence days for ARTT or part time";
$LANG['admin_temps_partiel_sem_impaires']   = "Odd weeks";
$LANG['admin_temps_partiel_sem_paires']     = "Even weeks";
$LANG['admin_temps_partiel_am']             = "morning";
$LANG['admin_temps_partiel_pm']             = "afternoon";
$LANG['admin_temps_partiel_date_valid']     = "Beginning validity date for this schedule";
// page admin_suppr_groupe
$LANG['admin_suppr_groupe_titre']       = "Group Deletion.";
// page admin_suppr_groupe
$LANG['admin_modif_groupe_titre']       = "Group Modification.";
// page admin_sauve_restaure_db
$LANG['admin_sauve_db_titre']           = "Backup / Restoration of the Database";
$LANG['admin_sauve_db_choisissez']      = "Choose";
$LANG['admin_sauve_db_sauve']           = "Backup";
$LANG['admin_sauve_db_restaure']        = "Restore";
$LANG['admin_sauve_db_do_sauve']        = "Start the backup";
$LANG['admin_sauve_db_options']         = "Backup Options";
$LANG['admin_sauve_db_complete']        = "Full Backup";
$LANG['admin_sauve_db_data_only']       = "Backup of the data only";
$LANG['admin_sauve_db_save_ok']         = "Backup done";
$LANG['admin_sauve_db_restaure']        = "Restoration of the database";
$LANG['admin_sauve_db_file_to_restore'] = "File to restore";
$LANG['admin_sauve_db_warning']         = "WARNING : all the data of the php_conges database will be overriden before the restoration";
$LANG['admin_sauve_db_do_restaure']     = "Run the Restoration";
$LANG['admin_sauve_db_bad_file']        = "Indicated File inexistant";
$LANG['admin_sauve_db_restaure_ok']     = "Restoration done with success";
// page admin_jours_chomes
$LANG['admin_jours_chomes_titre']               = "Input of days off";
$LANG['admin_jours_chomes_annee_precedente']    = "previous year";
$LANG['admin_jours_chomes_annee_suivante']      = "next year";
$LANG['admin_jours_chomes_confirm']             = "Confirm this Input";



/***********************/
// EDITIONS PAPIER
$LANG['editions_titre']         = "Holidays Prints";
$LANG['editions_last_edition']  = "Next Print";
$LANG['editions_aucun_conges']  = "No holidays to print in the database ...";
$LANG['editions_lance_edition']     = "Run the print";
$LANG['editions_pdf_edition']       = "Print in PDF";
$LANG['editions_hitorique_edit']        = "Prints Historic";
$LANG['editions_aucun_hitorique']   = "No print recorded for that user ...";
$LANG['editions_numero']            = "Number";
$LANG['editions_date']              = "Date";
$LANG['editions_edit_again']        = "Print again";
$LANG['editions_edit_again_pdf']    = "Print again in PDF";
//
$LANG['editions_bilan_au']          = "statement at";
$LANG['editions_historique']        = "Historic";
$LANG['editions_soldes_precedents_inconnus']    = "previous balances unknown";
$LANG['editions_solde_precedent']   = "previous balance";
$LANG['editions_nouveau_solde']     = "new balance";
$LANG['editions_signature_1']       = "Signature of the holder";
$LANG['editions_signature_2']       = "Signature of the manager";
$LANG['editions_cachet_etab']       = "and stamp of the establishment";
$LANG['editions_jours_an']          = "days / year";



/***********************/
// SAISIE CONGES
$LANG['saisie_conges_compter_jours']        = "Count the days";
$LANG['saisie_conges_nb_jours']             = "NB_Days_Taken";



/***********************/
// SAISIE ECHANGE ABSENCE
$LANG['saisie_echange_titre_calendrier_1']      = "Ordinary absence day";
$LANG['saisie_echange_titre_calendrier_2']      = "Wished absence day";



/***********************/
// CALENDRIER
$LANG['calendrier_titre']           = "HOLIDAYS CALENDAR";
$LANG['calendrier_imprimable']      = "Printable Version";
$LANG['calendrier_jour_precedent']  = "Previous Day";
$LANG['calendrier_jour_suivant']    = "Next Day";
$LANG['calendrier_legende_we']          = "week-end or holiday";
$LANG['calendrier_legende_conges']      = "holidays taken or to be taken";
$LANG['calendrier_legende_demande']     = "holidays asked (not yet granted)";
$LANG['calendrier_legende_part_time']   = "daily absence (part time , RTT)";
$LANG['calendrier_legende_abs']         = "absence other (mission, training, illness, ...)";



/***********************/
// CALCUL NB JOURS
$LANG['calcul_nb_jours_nb_jours']   = "Number of days to take :";
$LANG['calcul_nb_jours_reportez']   = "transfer this number in the box";
$LANG['calcul_nb_jours_form']       = "of the form";



/***********************/
// ERREUR
$LANG['erreur_user']            = "Impossible to identify the user";
$LANG['erreur_login_password']  = "couple login/password invalid or login missing";
$LANG['erreur_session']         = "session invalid or expired";



/***********************/
// INCLUDE_PHP
$LANG['mysql_srv_connect_failed']   = "Impossible to get connected to the server ";
$LANG['mysql_db_connect_failed']        = "Impossible to get connected to the database";

// page d'authentification / login screen
$LANG['cookies_obligatoires']       = "Your navigator must accept the <b>cookies</b> to be able to connect yourself to PHP_CONGES.";
$LANG['javascript_obligatoires']        = "Your navigator should accept the <b>Javascript</b> to use PHP_CONGES.";
$LANG['login_passwd_incorrect']     = "ERROR : User name and/or wrong password !!!";
$LANG['login_non_connu']                = "ERROR : User not recorded for the holidays management !!!";
//
$LANG['login_fieldset']         = "Identification";
$LANG['password']                   = "password";
$LANG['msie_alert']             = "Notice : Some displays may not be possible with Microsoft IE. Better use Mozilla Firefox.";


// verif saisie
$LANG['verif_saisie_erreur_valeur_manque']      = "ERROR : bad input : missing <b>values !!!</b>";
$LANG['verif_saisie_erreur_nb_jours_bad']       = "ERROR : bad input : <b>the number of days is invalid</b>";
$LANG['verif_saisie_erreur_fin_avant_debut']    = "ERROR : bad input : <b>ending date is previous than beginning date !!!</b>";
$LANG['verif_saisie_erreur_debut_apres_fin']    = "ERROR : bad input : <b>beginning date is later than ending date !!!</b>";
$LANG['verif_saisie_erreur_nb_bad']             = "ERROR : bad input : <b>the input number is invalid</b>";


/***********************/
// CONFIG TYPES ABSENCES
$LANG['config_abs_titre']               = "Configuration of absence types managed by PHP_CONGES";
$LANG['config_abs_comment_conges']      = "Absence types listed here are diverse holidays, each deducted on seperate accounts." ;
$LANG['config_abs_comment_absences']    = "Absence types listed here are not deducted (they are authorized holidays)." ;
$LANG['config_abs_libelle']             = "description";
$LANG['config_abs_libelle_short']       = "short description";
$LANG['config_abs_add_type_abs']            = "add an absence type :";
$LANG['config_abs_add_type_abs_comment']    = "Type in the absence type you want to add :";
$LANG['config_abs_saisie_not_ok']           = "invalid input :";
$LANG['config_abs_bad_caracteres']          = "followin characters are forbidden:";
$LANG['config_abs_champs_vides']            = "some fields are empty !";
$LANG['config_abs_suppr_impossible']        = "Deletion IMPOSSIBLE !";
$LANG['config_abs_already_used']            = "Holidays/absences of this type are being processed !";
$LANG['config_abs_confirm_suppr_of']        = "Please confirm the deletion of";



/***************************/
// CONFIGURATION PHP_CONGES
$LANG['config_appli_titre_1']       = "Configuration of the PHP_CONGES Application";
$LANG['config_appli_titre_2']       = "Configuration of php_conges";
//groupes de paramètres
$LANG['00_php_conges']              = "00 php_conges";
$LANG['01_Serveur Web']             = "01 Web Server";
$LANG["02_PAGE D'AUTENTIFICATION"]  = "02 AUTHENTIFICATION PAGE";
$LANG['03_TITRES']                  = "03 TITLES";
$LANG['04_Authentification']        = "04 Authentification";
$LANG['05_Utilisateur']             = "05 User";
$LANG['06_Responsable']             = "06 Manager";
$LANG['07_Administrateur']          = "07 Administrator";
$LANG['08_Mail']                    = "08 Mail";
$LANG['09_jours ouvrables']         = "09 working days";
$LANG['10_Gestion par groupes']     = "10 Management by groups";
$LANG['11_Editions papier']         = "11 Paper editions";
$LANG["12_Fonctionnement de l'Etablissement"]   = " 12 Functioning of the Establishment";
$LANG['13_Divers']                  = "13 Diverse";
$LANG['14_Présentation']            = "14 Presentation";
$LANG['15_Modules Externes']        = "15 Extern Modules";
// parametres de config
$LANG['config_comment_installed_version']   = "release number installed";
$LANG['config_comment_lang']                = "// LANGUE / LANGUAGE<br>\n//---------------------------<br>\n// fr = français<br>\n// test = seulement pour les développeurs de php_conges (only for php_conges developpers)";
$LANG['config_comment_URL_ACCUEIL_CONGES']  = "// MAIN URL OF YOUR INSTALLATION OF PHP_CONGES<br>\n//-------------------------------------------------<br>\n// Main URL of php_conges on your server (what you must type to get to authentification page.<br>\n// (do NOT finish by a / and without the index.php at the end)<br>\n// URL_ACCUEIL_CONGES = \"http://mywebserver.mydomain/php_conges\"";
$LANG['config_comment_img_login']           = "// PICTURE OF LOGIN PAGE<br>\n//---------------------------<br>\n// picture that displays on to of authentification page of php_conges";
$LANG['config_comment_texte_img_login']     = "// TEXTE DE L'IMAGE<br>\n//-------------------<br>\n// texte de l'image";
$LANG['config_comment_lien_img_login']      = "// PICTURE LINK<br>\n//------------------<br>\n// URL where sends the picture of the login page";
$LANG['config_comment_titre_calendrier']    = "Calendar title page of php_conges";
$LANG['config_comment_titre_user_index']    = "User title pages (will be followed by the user login)";
$LANG['config_comment_titre_resp_index']    = "Manager title pages";
$LANG['config_comment_titre_admin_index']   = "Administrator title pages";
$LANG['config_comment_auth']                = "// Authentification :<br>\n//---------------------<br>\n// if = FALSE : no authentification at start , one must pass the login parameter during call of php_conges<br>\n// if = TRUE  : authentification page displays during call of php_conges (TRUE is the default value)";
$LANG['config_comment_how_to_connect_user'] = "// How to verify the users login and password at start :<br>\n//--------------------------------------------------------------------------<br>\n// if at \"dbconges\" : the users authentification is done in user table of the db_conges database<br>\n// if at \"ldap\"     : the users authentification is done in a LDAP phone book that on will question (cf config_ldap.php)<br>\n// if at \"CAS\"      : the users authentification is done on a CAS server that one will question (cf config_CAS.php)<br>\n// warning : all other value than \"dbconges\" or \"ldap\" or \"CAS\" will lead to an error !!!";
$LANG['config_comment_export_users_from_ldap']  = "// Export of Users from LDAP :<br>\n//--------------------------------<br>\n// if = FALSE : the users are created \"manually\" directly in php_conges (FALSE is the default value)<br>\n// if = TRUE  : the users are imported from the LDAP server (with the help of a drop-down list) (cf config_ldap.php)";
$LANG['config_comment_user_saisie_demande']     = "//  HOLIDAYS DEMANDS<br>\n//---------------------------------------<br>\n// if at FALSE : no demand input by user, no demand management by the manager<br>\n// if at TRUE : demand input by user, and demand management by the manager (TRUE is the default value)";
$LANG['config_comment_user_affiche_calendrier'] = "//  DISPLAY OF THE BUTTON OF THE CALENDAR FOR THE USER<br>\n//--------------------------------------------------------------------------------------<br>\n// if at FALSE : the users cannot display the holidays calendrar<br>\n// if at TRUE : the users can display the holidays calendrar(TRUE is the default value)";
$LANG['config_comment_user_saisie_mission']     = "//  INPUT BY THE USER OF ABSENCES FOR MISSIONS, TRAININGS, CONGRESSES, ETC ....<br>\n//--------------------------------------------------------------------------------------<br>\n// ( absences of this type do not take off holidays days ! )<br>\n// if at FALSE : no input by the user of absences for mission, training, congress, etc ....<br>\n// if at TRUE : input by user of absences for mission, training, congress, etc .... (TRUE is default value)";
$LANG['config_comment_user_ch_passwd']          = "//  CHANGE YOUR PASSWORD<br>\n//---------------------------------------<br>\n// if at FALSE : the user cannot change his password<br>\n// if at TRUE : the user can change his password (TRUE is the default value)";
$LANG['config_comment_responsable_virtuel']     = "//  GENERIC VIRTUAL MANAGER OR NOT<br>\n//-------------------------------------------<br>\n// if at FALSE : the manager that handles the staff holidays is a real person (user of php_conges) (FALSE is the default value)<br>\n// if at TRUE : the manager that handles the staff holidays is a virtual generic user (login=conges)";
$LANG['config_comment_resp_affiche_calendrier'] = "//  DISPLAY OF THE BUTTON OF THE CALENDAR FOR THE MANAGER<br>\n//--------------------------------------------------------------------------------------<br>\n// si à FALSE : the managers cannot display the holidays calendrar<br>\n// if at TRUE : the managers can display the holidays calendrar(TRUE is the default value)";
$LANG['config_comment_resp_saisie_mission']     = "//  INPUT BY THE MANAGER OF ABSENCES FOR MISSIONS, TRAININGS, CONGRESSES, ETC ....<br>\n//---------------------------------------------------------------------------------------<br>\n// ( absences of this type do not take off holidays days ! )<br>\n// if at FALSE : no input by the manager of absences for mission, training, congress, etc ....(FALSE is the default value)<br>\n// if at TRUE : input by manager of absences for mission, training, congress, etc ....";
$LANG['config_comment_resp_vertical_menu']      = "//  CONFIG  OF THE MENU OF THE MANAGER<br>\n//---------------------------------------<br>\n// if at TRUE : in the manager window, the menu is vertical (on the left) (TRUE is the default value)<br>\n// if at FALSE : in the manager window, the menu is horizontal (on top)";
$LANG['config_comment_admin_see_all']           = "//  CONFIG  OF THE ADMINISTRATOR MODE<br>\n//---------------------------------------<br>\n// if at FALSE : the admin only manages the users of which he is responsable (FALSE is the default value)<br>\n// if at TRUE : the admin manages all users";
$LANG['config_comment_admin_change_passwd']     = "//  CHANGE THE PASSWORD OF A USER<br>\n//-----------------------------------------<br>\n// if at FALSE : the administrator cannot change the users password<br>\n// if at TRUE : the administrator can change the users password (TRUE is the default value)";
$LANG['config_comment_affiche_bouton_config_pour_admin']            = "// ACCESS TO THE CONFIG OF THE APPLI FOR THE ADMINS<br>\n//-------------------------------------------------------<br>\n// if at FALSE : the access button to php_conges configuration doesn't show on the administrator page (FALSE is the default value)<br>\n// if at TRUE : the access button to the php_conges configuration shows on the administrator page";
$LANG['config_comment_affiche_bouton_config_absence_pour_admin']    = "// ACCESS TO THE CONFIG OF THE TYPES OF ABSENCES OF THE APPLI FOR THE ADMINS<br>\n//---------------------------------------------------------------------<br>\n// if at FALSE : the access button to the configuration of the types of absences managed by php_conges doen't show on the administrator's page (FALSE is the default value)<br>\n// if at TRUE : the access button to the configuration of the types of absences managed by php_conges shows on the administrator's page";
$LANG['config_comment_mail_new_demande_alerte_resp']    = "// SENDING OF MAIL TO THE MANAGER FOR A NEW DEMAND :<br>\n//----------------------------------------------------------<br>\n// if at FALSE : the manager doesn't receive a mail during a new holidays demand by a user (FALSE is the default value)<br>\n// if at TRUE : the manager receives a warning mail at each new holidays demand from a user\n";
$LANG['config_comment_mail_valid_conges_alerte_user']   = "// SENDING OF MAIL TO THE USER FOR A NEW HOLIDAYS TYPED IN OR VALID :<br>\n//----------------------------------------------------------------<br>\n// if at FALSE : the user doesn't receive a mail when the manager types in or accepts a new holiday for him (FALSE is the default value)<br>\n// if at TRUE : the user receives a warning mail eachtime the managers types in a new holiday or accepts a demand for him\n";
$LANG['config_comment_mail_refus_conges_alerte_user']   = "// SENDING OF MAIL TO THE USER FOR THE REFUSAL OF A HOLIDAYS DEMAND :<br>\n//----------------------------------------------------------------<br>\n// if at FALSE : the user doesn't receive a mail when the manager refuses one of his holidays demands (FALSE is the default value)<br>\n// if at TRUE : the user receives a warning mail eachtime the manager refuses one of his holidays demands.\n";
$LANG['config_comment_mail_annul_conges_alerte_user']   = "// SENDING OF MAIL TO THE USER FOR THE CANCELLATION BY THE MANAG FOR A HOLIDAYS ALREADY VALIDATED :<br>\n//---------------------------------------------------------------------------------<br>\n// if at FALSE : the user doesn't receive a mail when the manager cancels his holidays (FALSE is the default value)<br>\n// if at TRUE : the user receives a warning mail eachtime the manager cancels one of his holidays.\n";
$LANG['config_comment_serveur_smtp']                    = "//  SERVER SMTP TO USE<br>\n//---------------------------------------<br>\n// address ip or name of the smpt server to use to send the mails<br>\n// If you don't master the SMTP server or if, while using, you have a connection érror to the server, leave this field empty (\"\")";
$LANG['config_comment_where_to_find_user_email']        = "//  WHERE TO FIND THE USERS' MAIL ADDRESSES<br>\n//-------------------------------------------------<br>\n// many possibilities to find the users' mail addresses :<br>\n// if at \"dbconges\" : the users' mail can be found in the users table of the db_conges database<br>\n// if at \"ldap\"     : the users' mail can be found in a LDAP directory that one will query (cf file config_ldap.php)<br>\n// WARNING : all other value than \"dbconges\" or \"ldap\" will lead to an error !!!";
$LANG['config_comment_samedi_travail']      = "//  MANAGEMENT OF THE SATURDAYS AS WORKING DAYS OR NOT<br>\n//--------------------------------------------------------------------------------------<br>\n// one defines here if the saturdays are working days or not.<br>\n// if at TRUE : the considered day is a working day ....<br>\n// if at FALSE : the considered day is not a working day (weekend).... (FALSE is the default value)";
$LANG['config_comment_dimanche_travail']    = "//  MANAGEMENT OF THE SUNDAYS AS WORKING DAYS OR NOT<br>\n//--------------------------------------------------------------------------------------<br>\n// one defines here if the sundays are working days or not.<br>\n// if at TRUE : the considered day is a working day ....<br>\n// if at FALSE : the considered day is not a working day (weekend).... (FALSE is the default value)";
$LANG['config_comment_gestion_groupes']     = "//  MANAGEMENT OF THE GROUPS OF USERS<br>\n//--------------------------------------<br>\n// one defines here if ones want to be able to manage the users by group or not.<br>\n// if at TRUE : the groups of users are managed in the application ....<br>\n// if at FALSE : the groups of users are NOT managed in the application .... (FALSE is the default value)";
$LANG['config_comment_affiche_groupe_in_calendrier']    = "//  DISPLAY OF THE CALENDRAR : all the users or the users of a group only<br>\n//--------------------------------------------------------------------------------------------<br>\n// if at FALSE : every persons are displayed on the holidays calendrar (FALSE is the default value)<br>\n// if at TRUE : only persons of the same group than the user display on the holidays calendrar";
$LANG['config_comment_editions_papier']             = "//  PAPER REPORTS<br>\n//--------------------------------------<br>\n// one defines here if the manager can generate the holidays reports of an user.<br>\n// if at TRUE : the paper reports are available ....(TRUE is the default value)<br>\n// if at FALSE : the paper reports are not available in the application ....";
$LANG['config_comment_texte_haut_edition_papier']   = "//  Text on top of the PAPER REPORTS<br>\n//--------------------------------------<br>\n// one defines here the eventual text that will appear on top of page of the paper reports of the holidays of an user.";
$LANG['config_comment_texte_bas_edition_papier']    = "//  Text at the bottom of the PAPER REPORTS<br>\n//--------------------------------------<br>\n// one defines here the eventual text that will appear at the bottom page of the paper reports of the holidays of an user.";
$LANG['config_comment_user_echange_rtt']            = "//  EXCHANGE RTT OR PART TIME AUTHORIZED FOR THE USERS<br>\n//---------------------------------------------------------------------------------------<br>\n// one authorizes or not the user to ponctually reverse a working day and an absence day (of rtt or part time)<br>\n// if at FALSE : no exchange authorized for the user (FALSE is the default value)<br>\n// if at TRUE : exchange authorized for the user";
$LANG['config_comment_affiche_bouton_calcul_nb_jours_pris'] = "//  BUTTON OF CALCULATION OF THE NB OF DAYS TAKEN<br>\n//--------------------------------------------------------------------------------------<br>\n// if at FALSE : one doesn't display the button of calculation of the nb of days taken during input of a new abscence<br>\n// if at TRUE : displays the button of calculation of the nb of days taken during input of a new abscence (TRUE is the default value)<br>\n// WARNING : if at TRUE : the days off must be typed in (see administration module)";
$LANG['config_comment_rempli_auto_champ_nb_jours_pris']     = "//  AUTOMATIC FILLING OF THE FIELD DURING THE CALL TO THE NB OF DAYS TAKEN CALCULATION<br>\n//--------------------------------------------------------------------------------------<br>\n// if at FALSE : the call to the nb of days taken calculation button doesn't automatically fills the field of the form (typed in manually)<br>\n// if at TRUE : the call to the nb of days taken calculation button automatically fills the field of the form (TRUE is the default value)";
$LANG['config_comment_duree_session']   = "// Max inactivity duration of a session before expiration (in secondes)";
$LANG['config_comment_verif_droits']    = "// Control of the Access rights :<br>\n//------------------------------------<br>\n// set to TRUE To manage access rights to pages (is at FALSE by default)<br>\n/* parameter characteristic of certain install environments only !!!...... */";
$LANG['config_comment_stylesheet_file'] = "// STYLE SHEET<br>\n//--------------------------<br>\n// name of the style sheet file to use (with relative path from the root of php_conges)";
$LANG['config_comment_bgcolor']         = "// background color of the pages";
$LANG['config_comment_bgimage']         = "// background picture of the pages (NO / at the beginning !!)";
$LANG['config_comment_light_grey_bgcolor']                  = "// various colors (light grey)";
$LANG['config_comment_php_conges_fpdf_include_path']        = "// PATH TO THE fpdf DIRECTORY<br>\n//-------------------------------------------------------<br>\n// One defines here the path to access to the PHP library directory \"fpdf\".<br>\n// The path must be relative from the php_conges root application.";
$LANG['config_comment_php_conges_phpmailer_include_path']   = "// PATH TO THE phpmailer DIRECTORY<br>\n//-------------------------------------------------------<br>\n// One defines here the path to access to the PHP library directory \"phpmailer\".<br>\n// The path must be relative from the php_conges root application.";
$LANG['config_comment_php_conges_cas_include_path']         = "// PATH TO THE cas DIRECTORY<br>\n//-------------------------------------------------------<br>\n// One defines here the path to access to the PHP library directory \"CAS\".<br>\n// The path must be relative from the php_conges root application.";
$LANG['config_comment_php_conges_authldap_include_path']    = "// PATH TO THE authLDAP.php FILE<br>\n//-------------------------------------------------------<br>\n// One defines here the path to access to the PHP library directory \"authLDAP.php\".<br>\n// The path must be relative from the php_conges root application.";



/***************************/
// INSTALLATION PHP_CONGES
//page index
$LANG['install_le_fichier']     = "The file";
$LANG['install_bad_fichier']    = "cannot be found in the main folder of the new php_conges, or hasn't enough reading rights";
$LANG['install_read_the_file']  = "refer to the file";
$LANG['install_reload_page']    = "then reload this page";
$LANG['install_db_inaccessible']        = "the database is not reachable";
$LANG['install_verifiez_param_file']    = "Please verify the file parameters";
$LANG['install_verifiez_priv_mysql']    = "Make sure that the MySql database, the user and the privileges have been created.";
$LANG['install_install_phpconges']      = "Installation of php_conges";
$LANG['install_index_titre']            = "Application PHP_CONGES";
$LANG['install_no_prev_version_found']  = "No previous release can be found";
$LANG['install_indiquez']               = "Please indicate whether it is";
$LANG['install_nouvelle_install']       = "a New Installation";
$LANG['install_mise_a_jour']            = "an Update";
$LANG['install_indiquez_pre_version']   = "please indicate the release already installed";
$LANG['install_installed_version']      = "release already installed";
$LANG['install_configuration']          = "Configuration";
$LANG['install_config_appli']           = "configure the application";
$LANG['install_config_types_abs']       = "configure the types of holidays to manage";
//page install
$LANG['install_install_titre']          = "Installation of the PHP_CONGES application";
$LANG['install_impossible_sur_db']      = "impossible on the database";
$LANG['install_verif_droits_mysql']     = "verify the Mysql rights of";
$LANG['install_puis']                   = "then";
$LANG['install_ok']                     = "Installation successful";
$LANG['install_vous_pouvez_maintenant'] = "You can now";
$LANG['install_acceder_appli']          = "access the application";
//page mise_a_jour
$LANG['install_version_non_choisie']    = "the release to update hasn't been chosen";
$LANG['install_maj_titre_1']            = "Update";
$LANG['install_maj_titre_2']            = "Update of the PHP_CONGES application";
$LANG['install_maj_passer_de']          = "you are about to change release from";
$LANG['install_maj_a_version']          = "to release";
$LANG['install_maj_sauvegardez']        = "Before going on, take care in doing a backup of your database";
$LANG['install_etape']                  = "step";
$LANG['install_inaccessible']           = "is not reachable";
$LANG['install_maj_conserv_config']     = "To insure the preservation of your configuration,";
$LANG['install_maj_copy_config_file']   = "please copy your old config.php file in the new folder";
$LANG['install_maj_whith_name']         = "under the name";
$LANG['install_maj_and']                = "and";
$LANG['install_maj_verif_droit_fichier']    = "verify the reading rights on that file.";



/***********************/
/***********************/
/***********************/
// NEW : V1.2
$LANG['divers_date_traitement'] = "date-time asking/processing";
$LANG['divers_demande']     = "asking";
$LANG['divers_traitement']  = "processing";
$LANG['divers_mois']        = "month";
$LANG['divers_annee']       = "year";

$LANG['phpmailer_not_valid']    = "MAIL ERROR : The file class.phpmailer.php cannot be read or cannot be found. The alert mail will not be sent !";
$LANG['fpdf_not_valid']     = "ERROR : The file fpdf.php cannot be read or cannot be found. The PDF document cannot be generated !";

$LANG['install_remove_fichier']     = "is from now on useless... Please delete it !";
$LANG['install_config_mail']    = "configure the mails sent by php_conges";

/***********************/
// EXPORT ICAL / VCAL
$LANG['button_export_1']            = "Export ical / vcal";
$LANG['button_export_2']            = "Export the events in the ical / vcal format";
$LANG['config_comment_disable_saise_champ_nb_jours_pris']   = "//  INPUT FORBIDDEN IN THE TEXT FIELD OF NB OF DAYS TAKEN<br>\n//--------------------------------------------------------------------------------------<br>\n// if at FALSE : the text field of the nb of days taken is active (manual input possible)(FALSE is the default value)<br>\n// if at TRUE : the text field of the nb of days taken is inactive (manual input impossible)";
$LANG['calcul_nb_jours_commentaire']            = "warning: this period overlaps other holidays !";
$LANG['calcul_nb_jours_commentaire_impossible'] = "CALCULATION IMPOSSIBLE : this period overlaps a current holidays demand pending !";
$LANG['config_comment_export_ical_vcal']    = "//  EXPORT HOLIDAYS IN ICS OU VCS FORMAT<br>\n//----------------------------------------------------------------------<br>\n// if at FALSE : the users cannot export their holidays/absences in the ics or vcs formats.<br>\n// if at TRUE : the users can export their holidays/absences in the ics or vcs formats (to import in electronic agendas or in planning softwares). (TRUE is the default value)";
$LANG['export_cal_titre']       = "Export in ical / vcal format";
$LANG['export_cal_from_date']   = "from";
$LANG['export_cal_to_date']     = "to";
$LANG['export_cal_saisir_debut']    = "selection of beginning date";
$LANG['export_cal_saisir_fin']      = "selection of ending date";
$LANG['export_cal_format']      = "choice of the format";

/***************************/
// CONFIG
/*
$LANG['config_comment_semaine_bgcolor']         = "background color of the week days in the calendar";
$LANG['config_comment_week_end_bgcolor']        = "background color of the week-end days in the calendar";
$LANG['config_comment_temps_partiel_bgcolor']   = "background color of the part-time or de rtt days in the calendar";
$LANG['config_comment_conges_bgcolor']          = "background color of the holidays in the calendar (holidays accepted by the manager)";
$LANG['config_comment_demande_conges_bgcolor']  = "background color of the asked holidays in the calendar (not yet accepted by the manager)";
$LANG['config_comment_absence_autre_bgcolor']   = "background color of the absence days for mission, etc ... in the calendar";
*/
/***************************/
// MAIL DE PHP_CONGES
$LANG['mail_new_demande_comment']           = "alert message to the manager in case of new demand of holidays.";
$LANG['mail_valid_conges_comment']          = "alert message d'alerte to the user in case of validation of a holiday demand or of input of a new holiday by the manager";
$LANG['mail_refus_conges_comment']          = "alert message d'alerte to the user in case of refusal by the manager of a holiday demand";
$LANG['mail_annul_conges_comment']          = "alert message d'alerte to the user in case of cancellation by the manager of holidays";
$LANG['mail_prem_valid_conges_comment']     = "alert message d'alerte to the user in case of first authorization by the manager of a holiday demand (if we use the double authorization).";

$LANG['mail_remplace_url_accueil_comment']      = "__URL_ACCUEIL_CONGES__ will automatically be replaced by a link to your php_conges application.";
$LANG['mail_remplace_sender_name_comment']      = "__SENDER_NAME__ will automatically be replaced by the name and first name of the sender.";
$LANG['mail_remplace_destination_name_comment'] = "__DESTINATION_NAME__ will automatically be replaced by the name and first name of the recipeint(s).";
$LANG['mail_remplace_retour_ligne_comment']     = "__RETOUR_LIGNE__ will automatically be replaced by line return.";

/***********************/
// CONFIG MAILS
$LANG['config_mail_titre']      = "Configuration of the mails sent by PHP_CONGES";
$LANG['config_mail_alerte_config']  = "This page is only used if the mail sent by php_conges is validated (see configuration of the application).";
$LANG['config_mail_subject']    = "mail subject";
$LANG['config_mail_body']       = "mail body";

$LANG['admin_button_config_mail_1']     = "Configuration of the mails sent by php_conges";
$LANG['admin_button_config_mail_2']     = "Config Mails";
$LANG['config_comment_affiche_bouton_config_mail_pour_admin']   = "// ACCESS TO THE CONFIG OF MAILS OF THE APPLI FOR THE ADMINS<br>\n//---------------------------------------------------------------------<br>\n// if at FALSE : the access button to the mail configuration sent by php_conges does not show on the administrator page (FALSE is the default value)<br>\n// if at TRUE : the access button to the mail configuration sent by php_conges shows on the administrator page";
$LANG['config_comment_mail_prem_valid_conges_alerte_user']  = "// SENDING OF MAIL TO THE USER AND BIG MANAGER AFTER THE FIRST VALIDATION OF A DEMAND (case of a double demand authorization) :<br>\n//----------------------------------------------------------------<br>\n// if at FALSE : the user and the big manager don't receive any mail when the manager approves a demand (first authorization). (FALSE is the default value)<br>\n// if at TRUE : the user and the big manager receive an alert mail when the manager approves a demand (first authorization).\n";
$LANG['config_comment_affiche_date_traitement'] = "// DISPLAY OF PROCESSING DATES AND HOURS IN THE HOLIDAYS HISTORICS<br>\n//---------------------------------------------------------------------<br>\n// if at FALSE : the dates and hours of holidays demands or de demands approvals are not displayed in the tables (FALSE is the default value)<br>\n// if at TRUE : dates and hour of holidays demands or of approval demands are displayed in the tables and reports (warning, this increases considerably the width of the displayed page).";

/***********************/
// DOUBLE VALIDATION
$LANG['config_comment_double_validation_conges']    = "//  DOUBLE APPROVAL OF THE HOLIDAYS DEMANDS<br>\n//----------------------------------------------------------------------<br>\n// if at FALSE : the holidays demand of a user is accepted or not by the manager (only one approval)(FALSE is the default value).<br>\n// if at TRUE : the holidays demand of an user must be approved by the manager, then accepted by the manager of the manager (double approval).<br>\WARNING, this parameter is valid ONLY if in the case of a management by group of the users !)";
$LANG['admin_groupes_double_valid']     = "double approval";
$LANG['admin_gestion_groupe_grand_resp_responsables']   = "Big Managers of Group";
$LANG['divers_grand_responsable_maj_1'] = "Big_Manager";
$LANG['resp_traite_demandes_titre_tableau_1']   = "Demands to be approved";
$LANG['resp_traite_demandes_titre_tableau_2']   = "Demands in second approval";
$LANG['resp_traite_user_etat_demandes_2_valid']     = "State of demands in second approval :";
$LANG['resp_etat_users_titre_double_valid']     = "Users with double demand approvals";
$LANG['resp_etat_aucun_user']   = "No user for this manager !!!";

/***********************/
// IMPRIM CALENDRIER
$LANG['button_imprim_calendar'] = "print a calendar";
$LANG['imprim_calendrier_titre']    = "choose the month to print";



/***********************/
/***********************/
/***********************/
// NEW : V1.2.1

$LANG['install_test_mail']  = "test the mail sendings of php_conges";

$LANG['session_pas_session_ouverte']    = "No opened session." ;

$LANG['divers_acces_page_interdit'] = "ACCESS TO THIS PAGE FORBIDDEN !!" ;
$LANG['divers_user_disconnected']   = "You have been disconnected from the application !" ;
$LANG['divers_veuillez']            = "Please" ;
$LANG['divers_vous_authentifier']   = "authentify yourself" ;

/***************************/
// CONFIGURATION PHP_CONGES
$LANG['14_Presentation']            = "14 Presentation";
$LANG['config_comment_affiche_soldes_calendrier']   = "//  DISPLAY OF THE USERS BALANCES IN THE CALENDAR : <br>\n//--------------------------------------------------------------------------------------------<br>\n// if at FALSE : the users holidays balance don't appear on the calendar holidays<br>\n// if at TRUE : the users holidays balance appear on the calendar holidays (TRUE is the default value)";
$LANG['mail_remplace_nb_jours']         = "__NB__JOURS__ will automatically be replaced by the number of days of the concerned absence.";



/***********************/
/***********************/
/***********************/
// NEW : V1.3.0

/***********************/
$LANG['divers_traitement_ok']       = "accepted";
$LANG['divers_traitement_refus']    = "refused";
$LANG['divers_traitement_annul']    = "canceled";

// GESTION CONGES EXCEPTIONNELS
$LANG['config_comment_gestion_conges_exceptionnels']    = "// EXCEPTIONAL HOLIDAYS<br>\n//----------------------------------------------------------------------<br>\n// holidays with an annual number of days always set to 0<br>\n// if at FALSE : the holidays of the type 'exceptional holidays' are not managed.(FALSE is the default value)<br>\n// if at TRUE : it is possible to define holidays of the type 'exceptional holidays' for the users (annual unpaid holidays).";
$LANG['config_abs_comment_conges_exceptionnels']        = "The types of holidays listed here have no annual rates." ;
$LANG['config_abs_desactive_cong_excep_impossible']     = "IMPOSSIBLE to deactivate the exceptional holidays! (no exceptional holidays must be recorded to allow this operation.)";

$LANG['divers_conges_exceptionnels']        = "exceptional holidays";
$LANG['divers_conges_exceptionnels_maj_1']  = "Exceptional Holidays";
$LANG['divers_semaine'] = "week";

// CONFIG
$LANG['config_comment_grand_resp_ajout_conges']    = "//  ADDING OF HOLIDAYS BY THE BIG MANAGER<br>\n//----------------------------------------------------------------------<br>\n// ONLY works if the double approval is activated !<br>\n// if at FALSE : a manager can only add holidays to his direct users (of which he is 'manager').(FALSE is default value)<br>\n// if at TRUE : a manager can add holidays to users of which he is 'manager' AND 'big manager' !";
$LANG['config_comment_interdit_saisie_periode_date_passee']    = "//  FORBID HOLIDAYS INPUT FOR PAST DATES<br>\n//----------------------------------------------------------------------<br>\n//if at FALSE : it is possible to input a holiday demand for a past date.(FALSE is the default value)<br>\n// if at TRUE : it is forbidden (and impossible) to input a holiday demand at a past date. ";



/***********************/
/***********************/
/***********************/
// NEW : V1.3.1

/***********************/
$LANG['calcul_nb_jours_commentaire_bad_date']   = "warning: ending date de fin smaller than beginning date !";
$LANG['calcul_impossible']                      = "warning: Calculation Impossible !";
$LANG['jours_feries_non_saisis']                = "holidays are not registered for the concerning year.";
$LANG['contacter_admin']                        = "Contact your administrator.";

// CONFIG
$LANG['config_comment_interdit_modif_demande']    = "//  FORBID THE HOLIDAY DEMAND MODIFICATION BY A USER<br>\n//----------------------------------------------------------------------<br>\n//if at FALSE : the user can modify an existing holiday demand.(FALSE is the default value)<br>\n// if at TRUE : it is forbidden (and impossible) for the user to modify an existing holiday demand. ";





/***********************/
/***********************/
/***********************/
// NEW : V1.3.2

/***********************/
$LANG['calendrier_afficher_groupe'] = "group to display";
$LANG['divers_groupe']              = "group";
$LANG['config_logs']            = "see the logs";
$LANG['config_logs_titre_1']    = "PHP_CONGES log management";
$LANG['config_logs_titre_2']    = "Log management";
$LANG['voir_les_logs_par']      = "to see the actions of only one user : click on his login in bold.";
$LANG['voir_tous_les_logs']     = "To see all logs";
$LANG['divers_cliquez_ici']     = "click here";
$LANG['form_delete_logs']       = "Empty Logs";
$LANG['confirm_vider_logs']     = "Do you really want to delete all LOGS of PHP_CONGES ??";
$LANG['no_logs_in_db']          = "No Logs in the DataBase !";

$LANG['divers_date_maj_1']      = "Date";
$LANG['divers_fait_par_maj_1']  = "Done by";
$LANG['divers_pour_maj_1']      = "For";

// ajout d'un user
$LANG['admin_gestion_groupe_users_group_of_new_user']   = "Groups to which belongs the user";

// CONFIG
$LANG['config_comment_calendrier_select_all_groups']    = "//  DISPLAY OF THE CALENDAR : Group selection : choice between all groups or not<br>\n//--------------------------------------------------------------------------------------------<br>\n// if the group management is active, we define here if the choice of the group to display in the calendar includes all groups or not.<br>\n// if at FALSE : only the user's groups (or of which he is the manager) display in the selection on the holidays calendar (FALSE is the default value)<br>\n// if at TRUE : all groups display in the selection on holidays calendar";
$LANG['config_comment_consult_calendrier_sans_auth']    = "//  DISPLAY OF THE CALENDAR WITHOUT AUTHENTIFICATION<br>\n//--------------------------------------------------------------------------------------------<br>\n// if at FALSE : It is not possible to display the holidays calendar without being connected to PHP_CONGES. (FALSE is the default value)<br>\n// if at TRUE : Possibility of consulting the holidays calendar without connecting to PHP_CONGES (via a link on the home page, or with the direct URL).";
$LANG['config_comment_resp_ajoute_conges']              = "//  ADDING OF HOLIDAYS BY THE MANAGER<br>\n//--------------------------------------------------------------------------------------------<br>\n// if at FALSE : The manager cannot add holidays to his users.<br>\n// if at TRUE : The manager can add holidays to his users. (FALSE is the default value)";


// CONFIGURATION MAILS
$LANG['mail_remplace_date_debut']   = "__DATE_DEBUT__ will automatically be replaced the beginning date of the concerned absence.";
$LANG['mail_remplace_date_fin']     = "__DATE_FIN__   will automatically be replaced the ending date of the concerned absence.";




/***********************/
/***********************/
/***********************/
// NEW : V1.4.0

/***********************/
// CONFIGURATION MAILS
//$LANG['mail_remplace_commentaire']	= "__COMMENT__    sera automatiquement remplacé par le commentaire   de l'absence concernée.";
//$LANG['mail_remplace_type_absence']	= "__TYPE_ABSENCE__   sera automatiquement remplacé par le libellé   de l'absence concernée.";

// PARTIE ADMINISTRATEUR
//$LANG['admin_button_jours_fermeture_1']       = "saisie des jours de fermeture";
//$LANG['admin_button_jours_fermeture_2']       = "saisie des jours de fermeture";
// page admin_jours_fermeture
//$LANG['admin_jours_fermeture_titre']            = "Saisie des jours de fermeture";
//$LANG['admin_jours_fermeture_fermeture_pour_tous']	= "Fermeture pour tous";
//$LANG['admin_jours_fermeture_fermeture_par_groupe']	= "Fermeture par groupe";
//$LANG['admin_jours_fermeture_new_fermeture']	= "Nouvelle Fermeture";
//$LANG['admin_jours_fermeture_enregistrees']		= "Fermetures Enregistrées";
//$LANG['admin_jours_fermeture_dates_incompatibles']		= "dates saisies incompatibles !!! veuillez recommencer";
//$LANG['admin_jours_fermeture_date_passee_error']		= "dates passées impossibles !!! veuillez recommencer";
//$LANG['admin_jours_fermeture_annee_non_saisie']			= "les jours feriés de ces années ne sont pas saisie !!! veuillez recommencer ou saisir les jours fériés pour ces années";
//$LANG['admin_jours_fermeture_chevauche_periode']		= "Erreur: la fermeture saisie chevauche une autre fermeture ou un congé pris par un utilisateur concerné !!! veuillez recommencer";
//$LANG['admin_jours_fermeture_fermeture_aujourd_hui']	= "Erreur: la fermeture saisie débute ou finie aujourd'hui, ce qui est interdit !!! veuillez recommencer";
//$LANG['admin_jours_fermeture_affect_type_conges']		= "à quel type de conges affecter cette fermeture : ";
//$LANG['admin_annuler_fermeture']           		= "Annuler cette Fermeture";
//$LANG['admin_annul_fermeture_confirm']          = "Veuillez confirmer cette Annulation";
//$LANG['config_comment_fermeture_par_groupe']	= "//  GESTION DES FERMETURES PAR GROUPE : Fermetures d'établissement / de service par groupe ou non<br>\n//--------------------------------------------------------------------------------------------<br>\n// si la gestion des groupes est active, on définit ici si les fermetures d'établissement sont différentes suivant les groupes d'utilisateurs ou pas.<br>\n// si à FALSE : les fermetures s'appliquent à tous les parsonnels (la fermeture leur est débitée en congés) (FALSE est la valeur par defaut)<br>\n// si à TRUE : les fermetures d'établissement / de service sont gérées par groupe et peuvent être différentes d'un groupe à l'autre.";
//$LANG['divers_fermeture']		= "fermeture";
//$LANG['divers_fermeture_du']	= "fermeture du";
//$LANG['divers_du']				= "du";
//$LANG['divers_au']				= "au";
//$LANG['divers_date_debut']		= "date de début";
//$LANG['divers_date_fin']		= "date de fin";
//$LANG['divers_confirmer_maj_1']		= "Confirmer";
// CONFIG
//$LANG['config_comment_affiche_demandes_dans_calendrier']	= "//  AFFICHAGE DES DEMANDES DE CONGES DES UTILISATEURS DANS LE CALENDRIER : <br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : les demandes de congés de tous les utilisateurs n'apparaissent pas sur le calendrier des congés. (seules les demandes de l'utilisateur connecté apparaissent) (FALSE est la valeur par defaut)<br>\n// si à TRUE : les demandes de congés de tous les utilisateurs apparaissent sur le calendrier des congés.";
//$LANG['config_comment_calcul_auto_jours_feries_france']		= "//  SAISIE DES JOURS FERIES DE L'ANNEE : <br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : la saisie des jours fériés de l'année par l'administrateur se fait à la main. (FALSE est la valeur par defaut)<br>\n// si à TRUE : lors de la saisie des jours fériés de l'année par l'administrateur, les jours fériés (de France) sont automatiquement renseignés (iln'y a plus qu'à vérifier et valider).";
//$LANG['config_comment_gestion_cas_absence_responsable']		= "//  PRISE EN COMPTE DES ABSENCES DU RESPONSABLE : <br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : en cas d'absence de leur responsable, les demandes des utilisateurs attendent le retour de celui ci. (FALSE est la valeur par defaut)<br>\n// si à TRUE : en cas d'absence de leur responsable, les demandes des utilisateurs sont transmises au responsable du responsable qui peut alors les traiter.";
// DIVERS
//$LANG['divers_normal_maj_1']		= "Normal";



/***********************/
/***********************/
/***********************/
// NEW : V1.4.1

/***********************/
// CONFIG
//$LANG['config_comment_texte_page_login']		= "// TEXTE DE LA PAGE D'ACCUEIL<br>\n//------------------<br>\n// texte qui apparaitra sous l'image sur la page de login (peut être vide)";
//$LANG['config_comment_solde_toujours_positif']	= "//  SOLDES TOUJOURS POSITIFS : <br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : le solde d'un congé peut être négatif. (FALSE est la valeur par defaut)<br>\n// si à TRUE : le solde d'un congé ne peut pas être négatif (un utilisateur ne peut poser un congé si son solde devient négatif).";
// VERIF SOLDE POSITIF
//$LANG['verif_solde_erreur_part_1']	= "Attention le nombre de jours d'absence demandés";
//$LANG['verif_solde_erreur_part_2']	= "est supérieur à votre solde (somme du solde";
//$LANG['verif_solde_erreur_part_3']	= "et des congés à valider";



/***********************/
/***********************/
/***********************/
// NEW : V1.5.0

/***********************/
// ADMIN
//$LANG['admin_groupes_nb_users']        = "nb membres";
//$LANG['resp_cloture_exercice_titre']   = "cloture/début d'exercice";
//$LANG['divers_cloturer_maj_1']         = "Cloturer";
//$LANG['divers_reliquat']        	   = "reliquat";
//$LANG['button_cloture']  		       = "Changement Exercice";
//$LANG['resp_cloture_exercice_all']     = "Cloture d'exercice globale pour Tous";
//$LANG['resp_cloture_exercice_groupe']  = "Cloture d'exercice globale par groupe";
//$LANG['resp_cloture_exercice_users']   = "Cloture d'exercice par personne";
//$LANG['resp_cloture_exercice_for_all_text_confirmer']     = "Confirmez la cloture de l'exercice en cours et le début de l'exercice suivant pour tous les utilisateurs dont vous êtes responsable ?";
//$LANG['resp_cloture_exercice_for_groupe_text_confirmer']  = "Confirmez la cloture de l'exercice en cours et le début de l'exercice suivant pour tous les utilisateurs du groupe séléctionné ?";
//$LANG['form_valid_cloture_global']     = "Valider la cloture globale";
//$LANG['form_valid_cloture_group']      = "Valider la cloture pour le groupe";
//$LANG['resp_cloture_exercice_commentaire']	= "cloture exercice";
// CONFIG
//$LANG['config_comment_autorise_reliquats_exercice']	= "// RELIQUATS AUTORISES D'UN EXERCICE SUR L'AUTRE : <br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : le solde d'un congé ne peut pas être reporté comme reliquat sur l'exercice suivant.<br>\n// si à TRUE : le solde d'un congé peut être reporté comme reliquat sur l'exercice suivant.. (TRUE est la valeur par defaut)";
//$LANG['config_comment_nb_maxi_jours_reliquats']		= "// NOMBRE MAX DE JOURS DE RELIQUATS AUTORISES D'UN EXERCICE SUR L'AUTRE : <br>\n//--------------------------------------------------------------------------------------------<br>\n// Nombre maximum de jours qui peut être reporté comme reliquat sur l'exercice suivant (les jours au dela du maxi sont perdus). Mettre à 0 (zero) si pas de limite. (0 est la valeur par defaut)";
//$LANG['config_comment_jour_mois_limite_reliquats']	= "// DATE LIMITE D'UTILISATION DES RELIQUATS : <br>\n//------------------<br>\n// (si les reliquats sont autorisés) : date maximum dans l'année pour utiliser ses reliquats de congés de l'exercice précédent (au dela, ils sont perdus) (date au format JJ-MM) (égal à 0 si pas de date limite)";
//$LANG['config_jour_mois_limite_reliquats_modif_impossible']	= "IMPOSSIBLE de modifier la date limite des reliquats ! (format invalide !)";
//
//$LANG['lang']['session_pas_de_compte_dans_db']   = "Il n'existe pas de compte correspondant à votre login dans la base de données de PHP_CONGES<br>\n";
//$LANG['lang']['session_contactez_admin']   = "Contactez l'administrateur de php_conges";



// FIN DES VARIABLES A RENSEIGNER :
/*************************************************************************************************/
$_SESSION['lang']=$LANG ;

