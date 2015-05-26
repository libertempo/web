<?php
/************************************************************************************************
PHP_CONGES : Gestion Interactive des Cong?
Copyright (C) 2005 (cedric chauvineau)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les
termes de la Licence Publique G??ale GNU publi? par la Free Software Foundation.
Ce programme est distribu?car potentiellement utile, mais SANS AUCUNE GARANTIE,
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation
dans un but sp?ifique. Reportez-vous ?la Licence Publique G??ale GNU pour plus de d?ails.
Vous devez avoir re? une copie de la Licence Publique G??ale GNU en m?e temps
que ce programme ; si ce n'est pas le cas, ?rivez ?la Free Software Foundation,
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, ?ats-Unis.
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
$LANG['janvier']	= "Enero";
$LANG['fevrier']	= "Febrero";
$LANG['mars']		= "Marzo";
$LANG['avril']		= "Abril";
$LANG['mai']		= "Mayo";
$LANG['juin']		= "Junio";
$LANG['juillet']	= "Julio";
$LANG['aout']		= "Agosto";
$LANG['septembre']	= "Septiembre";
$LANG['octobre']	= "Octubre";
$LANG['novembre']	= "Noviembre";
$LANG['decembre']	= "Diciembre";

$LANG['lundi']		= "lunes";
$LANG['mardi']		= "martes";
$LANG['mercredi']	= "miercoles";
$LANG['jeudi']		= "jueves";
$LANG['vendredi']	= "viernes";
$LANG['samedi']		= "sabado";
$LANG['dimanche']	= "domingo";

$LANG['lundi_short']		= "lun";
$LANG['mardi_short']		= "mar";
$LANG['mercredi_short']		= "mie";
$LANG['jeudi_short']		= "jue";
$LANG['vendredi_short']		= "vie";
$LANG['samedi_short']		= "sab";
$LANG['dimanche_short']		= "dom";

$LANG['lundi_2c']		= "lu";
$LANG['mardi_2c']		= "ma";
$LANG['mercredi_2c']	= "mi";
$LANG['jeudi_2c']		= "ju";
$LANG['vendredi_2c']	= "vi";
$LANG['samedi_2c']		= "sa";
$LANG['dimanche_2c']	= "do";

$LANG['lundi_1c']		= "L";
$LANG['mardi_1c']		= "M";
$LANG['mercredi_1c']	= "M";
$LANG['jeudi_1c']		= "J";
$LANG['vendredi_1c']	= "V";
$LANG['samedi_1c']		= "S";
$LANG['dimanche_1c']	= "D";



/***********************/
// BOUTONS COMMUNS
$LANG['button_deconnect']	= "Desconexion";
$LANG['button_refresh']		= "Actualizar la Pagina";
$LANG['button_editions']	= "Edicion Papel";
$LANG['button_admin_mode']	= "Modo Administrador";
$LANG['button_calendar']	= "Mostrar el Calendario";



/***********************/
// FORMULAIRES
$LANG['form_ok']			= "OK";
$LANG['form_submit']		= "Validar";
$LANG['form_cancel']		= "Abandonar";
$LANG['form_retour']		= "Volver";
$LANG['form_ajout']			= "A?adir";
$LANG['form_supprim']		= "Suprimir";
$LANG['form_modif']			= "Modificar";
$LANG['form_annul']			= "Anular";
$LANG['form_redo']			= "Volver a empezar";
$LANG['form_am']			= "mañana";
$LANG['form_pm']			= "tarde";
$LANG['form_day']			= "jornada completa";
$LANG['form_close_window']	= "Cerrar esta ventana";
$LANG['form_save_modif']	= "Registrar las modificaciones";
$LANG['form_modif_ok']		= "Modificaciones registradas !";
$LANG['form_modif_not_ok']	= "ERROR ! Modificaciones NO registradas !";
$LANG['form_valid_global']	= "Validar la entrada global";
$LANG['form_valid_groupe']	= "Validar la entrada para el Grupo";
$LANG['form_password']		= "Password";
$LANG['form_start']			= "Comenzar";
$LANG['form_continuer']		= "Continuar";



/***********************/
// DIVERS
$LANG['divers_quotite']			= "porcentaje";
$LANG['divers_quotite_maj_1']		= "Porcentaje";
$LANG['divers_annee']			= "año";
$LANG['divers_annee_maj']		= "AÑO";
$LANG['divers_solde']			= "saldo";
$LANG['divers_solde_maj']		= "SALDO";
$LANG['divers_solde_maj_1']		= "Saldo";
$LANG['divers_debut_maj']		= "INICIO";
$LANG['divers_debut_maj_1']		= "Inicio";
$LANG['divers_fin_maj']			= "FIN";
$LANG['divers_fin_maj_1']		= "Fin";
$LANG['divers_type']			= "tipo";
$LANG['divers_type_maj_1']		= "Tipo";
$LANG['divers_comment_maj_1']	= "Comentario";
$LANG['divers_etat_maj_1']		= "Situacion";
$LANG['divers_nb_jours_pris_maj_1']	= "n° de dias cogidos";
$LANG['divers_nb_jours_maj_1']	= "n? Dias";
$LANG['divers_inconnu']			= "desconocido";
$LANG['divers_motif_refus']		= "motivo de la negativa";
$LANG['divers_motif_annul']		= "motivo de la anulacion";
$LANG['divers_refuse']			= "denegado";
$LANG['divers_annule']			= "anulado";
$LANG['divers_login']			= "login";
$LANG['divers_login_maj_1']		= "Login";
$LANG['divers_personne_maj_1']	= "Persona";
$LANG['divers_responsable_maj_1']	= "Responsable";
$LANG['divers_nom_maj']			= "APELLIDO";
$LANG['divers_nom_maj_1']		= "Apellido";
$LANG['divers_prenom_maj']		= "NOMBRE";
$LANG['divers_prenom_maj_1']	= "Nombre";
$LANG['divers_accepter_maj_1']	= "Acceptar";
$LANG['divers_refuser_maj_1']	= "Denegar";
$LANG['divers_fermer_maj_1']	= "Cerrar";
$LANG['divers_am_short']		= "am";
$LANG['divers_pm_short']		= "pm";
$LANG['divers_conges']			= "vacaciones";
$LANG['divers_conges_maj_1']	= "Vacaciones";
$LANG['divers_absences']		= "ausencias";
$LANG['divers_absences_maj_1']	= "Ausencias";
$LANG['divers_nouvelle_absence']	= "Nueva Ausencia";
$LANG['divers_mois_precedent']			= "mes precedente";
$LANG['divers_mois_precedent_maj_1']	= "Mois Precedente";
$LANG['divers_mois_suivant']			= "mois siguiente";
$LANG['divers_mois_suivant_maj_1']		= "Mois Siguiente";




/***********************/
// PARTIE UTILISATEUR
//divers
$LANG['user']				= "Usuario";

//onglets
$LANG['user_onglet_echange_abs']		= "Cambio dia ausencia";
$LANG['user_onglet_demandes']			= "solicitudes en curso";
$LANG['user_onglet_historique_conges']	= "Historico vacaciones";
$LANG['user_onglet_historique_abs']		= "Historico otras ausencias";
$LANG['user_onglet_change_passwd']		= "Cambiar el password";

//titres des pages
$LANG['user_echange_rtt']				= "Cambio dia rtt,tiempo parcial / dia trabajado";
$LANG['user_etat_demandes']				= "Estado de las solicitudes en curso";
$LANG['user_historique_conges']			= "Historico vacaciones";
$LANG['user_historique_abs']			= "Historico de las ausencias por mision, formacion, etc ...";
$LANG['user_change_password']			= "Cambiar vuestro password";

//page etat des demandes
$LANG['user_demandes_aucune_demande']	= "Ninguna solicitud en curso ...";

//page historique des conges
$LANG['user_conges_aucun_conges']		= "Ningunas vacaciones en la base de datos ...";

//page historique des absences
$LANG['user_abs_aucune_abs']			= "Ninguna ausencia en la base de datos ...";
$LANG['user_abs_type']				= "Ausencia";

//page changer password
$LANG['user_passwd_saisie_1']		= "1era entrada";
$LANG['user_passwd_saisie_2']		= "2da entrada";
$LANG['user_passwd_error']			= "ERROR ! las 2 entradas son diferentes o vacias !!";


//page modification demande / absence
$LANG['user_modif_demande_titre']		= "Modificacion de una solicitud/ausencia.";

//page suppression demande / absence
$LANG['user_suppr_demande_titre']		= "Suprimir solicitud de vacaciones .";





/***********************/
// PARTIE RESPONSABLE
//menu
$LANG['resp_menu_titre']					= "MODO RESPONSABLE :";
$LANG['resp_menu_button_retour_main']		= "Retorno pagina Principal";
$LANG['resp_menu_button_traite_demande']	= "Tratar todas las Solicitudes";
$LANG['resp_menu_button_affiche_user']		= "seleccionar persona";
$LANG['resp_menu_button_ajout_jours']		= "A?adir Dias Vacaciones";
$LANG['resp_menu_button_mode_user']			= "Modo Usuario";
$LANG['resp_menu_button_mode_admin']		= "Mode Administrador";

//page etat des conges des users
$LANG['resp_etat_users_afficher']	= "Ver";
$LANG['resp_etat_users_imprim']		= "Edicion Papel";
//page traite toutes les demandes
$LANG['resp_traite_demandes_titre']				= "Tratamiento de solicitudes de vacaciones :";
$LANG['resp_traite_demandes_aucune_demande']	= "Ninguna solicitud de vacaciones en curso en la base de datos...";
$LANG['resp_traite_demandes_nb_jours']			= "n° Dias<br>Tomados";
$LANG['resp_traite_demandes_attente']			= "Espera";
$LANG['resp_traite_demandes_motif_refus']		= "Motivo<br>del rechazo";
//page ajout conges
$LANG['resp_ajout_conges_titre']				= "Añadir vacaciones :";
$LANG['resp_ajout_conges_nb_jours_ajout']		= "N° dias a a?adir";
$LANG['resp_ajout_conges_ajout_all']			= "Adicion global para Todos :";
$LANG['resp_ajout_conges_nb_jours_all_1']		= "Numero de dias de";
$LANG['resp_ajout_conges_nb_jours_all_2']		= "A añadir a todos :";
$LANG['resp_ajout_conges_calcul_prop']			= "Calculo proporcional al porcentaje de cada persona :";
$LANG['resp_ajout_conges_oui']					= "SI";
$LANG['resp_ajout_conges_calcul_prop_arondi']	= "el calculo proporcional es redondeado a 1/2 mas cercano";
$LANG['resp_ajout_conges_ajout_groupe']			= "Adicion por Grupo : (adicion a todos los miembros de un grupo)";
$LANG['resp_ajout_conges_choix_groupe']			= "eleccion del grupo";
$LANG['resp_ajout_conges_nb_jours_groupe_1']	= "Numero de dias de";
$LANG['resp_ajout_conges_nb_jours_groupe_2']	= "a añadir al grupo :";
$LANG['resp_ajout_conges_comment_periode_user']	= "añadir dia";
$LANG['resp_ajout_conges_comment_periode_all']	= "añadir para todas las personas";
$LANG['resp_ajout_conges_comment_periode_groupe']	= "añadir para el grupo";
//page traite user
$LANG['resp_traite_user_titre']				= "Tratamiento de :";
$LANG['resp_traite_user_new_conges']		= "Nuevas Vacaciones/Ausencias :";
$LANG['resp_traite_user_etat_demandes']		= "Estado de las solicitudes :";
$LANG['resp_traite_user_etat_conges']		= "Estado de las vacaciones :";
$LANG['resp_traite_user_aucune_demande']	= "Ninguna solicitud de vacaciones para esta persona en la base de datos ...";
$LANG['resp_traite_user_motif_refus']		= "motivo del rechazo";
$LANG['resp_traite_user_aucun_conges']		= "Ningunas vacaciones para esta persona en la base de datos ...";
$LANG['resp_traite_user_motif_possible']	= "motivo rechazo o anulacion eventual";
$LANG['resp_traite_user_annul']				= "Anular";
$LANG['resp_traite_user_motif_annul']		= "motivo anulacion";
$LANG['resp_traite_user_motif']				= "motivo";
$LANG['resp_traite_user_valeurs_not_ok']	= "ERROR ! Los valores entrados son incorrectos o faltan  !!!";





/***********************/
// PARTIE ADMINISTRATEUR
//divers
$LANG['admin_titre']					= "Administracion de la Base de Datos : ";
$LANG['admin_button_close_window_1']	= "Cierre del modo Administradorr";
$LANG['admin_button_config_1']			= "Configuracion de php_vacaciones";
$LANG['admin_button_config_2']			= "Configuracion";
$LANG['admin_button_config_abs_1']		= "Configuracion de los tipos de ausencias gestionadas por php_vacaciones";
$LANG['admin_button_config_abs_2']		= "Config Ausencias";
$LANG['admin_button_jours_chomes_1']	= "entrada de los dias no trabajados";
$LANG['admin_button_jours_chomes_2']	= "entrada de los dias no trabajados";
$LANG['admin_button_save_db_1']			= "Copia seguridad/Restauracion Base de Datos";
$LANG['admin_button_save_db_2']			= "Copia seguridad/Restauracion Base de Datos";
//
$LANG['admin_onglet_gestion_user']		= "Gestion de los  Usuarios";
$LANG['admin_onglet_add_user']			= "Añadir un Usuario";
$LANG['admin_onglet_gestion_groupe']	= "Gestion de los Grupos";
$LANG['admin_onglet_groupe_user']		= "Gestion Grupos <-> Usuarios";
$LANG['admin_onglet_user_groupe']		= "Gestion Usuarios <-> Grupos";
$LANG['admin_onglet_groupe_resp']		= "Gestion Grupos <-> Responsables";
$LANG['admin_onglet_resp_groupe']		= "Gestion Responsables <-> Grupos";
//
$LANG['admin_verif_param_invalides']	= "ATENCION : algunos campos entrados no son validos ......";
$LANG['admin_verif_login_exist']		= "ATENCION : login ya utilizado, se ruega modificarlo ......";
$LANG['admin_verif_bad_mail']			= "ATENCION : direccion mail erronea ......";
$LANG['admin_verif_groupe_invalide']	= "ATENCION : nombre de grupo ya utilizado, se ruega modificarlo ......";
// page gestion utilisateurs
$LANG['admin_users_titre']				= "Estado de los Usuarios";
$LANG['admin_users_is_resp']			= "is_resp";
$LANG['admin_users_resp_login']			= "resp_login";
$LANG['admin_users_is_admin']			= "is_admin";
$LANG['admin_users_see_all']			= "see_all";
$LANG['admin_users_mail']				= "email";
$LANG['admin_users_password_1']			= "password1";
$LANG['admin_users_password_2']			= "password2";
// page ajout utilisateur
$LANG['admin_new_users_titre']			= "Nuevo Usuario :";
$LANG['admin_new_users_is_resp']		= "es_responsable";
$LANG['admin_new_users_is_admin']		= "es_administrador";
$LANG['admin_new_users_see_all']		= "ver_todos";
$LANG['admin_new_users_password']		= "password";
$LANG['admin_new_users_nb_par_an']		= "n° / año";
// page ajout utilisateur
$LANG['admin_groupes_groupe']			= "Grupo";
$LANG['admin_groupes_libelle']			= "Llamado";
$LANG['admin_groupes_new_groupe']		= "Nuevo Grupo :";
// page gestion groupes
$LANG['admin_gestion_groupe_etat']		= "Estado de los Grupos";
//
$LANG['admin_aff_choix_groupe_titre']	= "Eleccion de un Grupo";
$LANG['admin_aff_choix_user_titre']		= "Eleccion de un Usuario";
$LANG['admin_aff_choix_resp_titre']		= "Eleccion de un Responsable";
// page gestion groupe <-> users
$LANG['admin_gestion_groupe_users_membres']	= "Miembros de un Grupo";
$LANG['admin_gestion_groupe_users_group_of_user']	= "Grupos a los que pertenece";
// page gestion groupe <-> users
$LANG['admin_gestion_groupe_resp_groupes']		= "Grupos del Responsable";
$LANG['admin_gestion_groupe_resp_responsables']	= "Responsables del Grupo";
// page change password user
$LANG['admin_chg_passwd_titre']		= "Modificacion Password usuario";
// page admin_suppr_user
$LANG['admin_suppr_user_titre']		= "Supresion Usuario";
// page admin_modif_user
$LANG['admin_modif_user_titre']		= "Modificacion usuario";
$LANG['admin_modif_nb_jours_an']	= "n° dias / año";
// grille saisie temps partiel et RTT
$LANG['admin_temps_partiel_titre']			= "entrada de los dias de ausencia por ARTT o tiempo parcial";
$LANG['admin_temps_partiel_sem_impaires']	= "semanas Impares";
$LANG['admin_temps_partiel_sem_paires']		= "semanas Pares";
$LANG['admin_temps_partiel_am']				= "am";
$LANG['admin_temps_partiel_pm']				= "pm";
$LANG['admin_temps_partiel_date_valid']		= "Fecha de inicio de validez de este horario";
// page admin_suppr_groupe
$LANG['admin_suppr_groupe_titre']		= "Supresion Grupo.";
// page admin_suppr_groupe
$LANG['admin_modif_groupe_titre']		= "Modificacion Grupo.";
// page admin_sauve_restaure_db
$LANG['admin_sauve_db_titre']			= "Copia / Restauracion de la Base de datos";
$LANG['admin_sauve_db_choisissez']		= "Seleccionar";
$LANG['admin_sauve_db_sauve']			= "Salvar";
$LANG['admin_sauve_db_restaure']		= "Restaurar";
$LANG['admin_sauve_db_do_sauve']		= "Iniciar la Copia";
$LANG['admin_sauve_db_options']			= "Opciones de la Copia";
$LANG['admin_sauve_db_complete']		= "Copia completa";
$LANG['admin_sauve_db_data_only']		= "Salvar solo los datos";
$LANG['admin_sauve_db_save_ok']			= "Copia efectuada";
$LANG['admin_sauve_db_restaure']		= "Restauracion de la base de datos";
$LANG['admin_sauve_db_file_to_restore']	= "Archivo a restaurar";
$LANG['admin_sauve_db_warning']			= "ATENCION : todos los datos de la database php_vacaciones van a ser suprimidos antes de la restauracion";
$LANG['admin_sauve_db_do_restaure']		= "Comenzar la Restauracion";
$LANG['admin_sauve_db_bad_file']		= "Archivo indicado inexistente";
$LANG['admin_sauve_db_restaure_ok']		= "Restauracion efectuada";
// page admin_jours_chomes
$LANG['admin_jours_chomes_titre']				= "Entrada de los dias no trabajados";
$LANG['admin_jours_chomes_annee_precedente']	= "año precedente";
$LANG['admin_jours_chomes_annee_suivante']		= "año siguiente";
$LANG['admin_jours_chomes_confirm']				= "Confirmar esta Entrada";



/***********************/
// EDITIONS PAPIER
$LANG['editions_titre']			= "Ediciones Vacaciones";
$LANG['editions_last_edition']	= "Proxima Edicion";
$LANG['editions_aucun_conges']	= "Ningunas vacaciones a editar en la base de datos ...";
$LANG['editions_lance_edition']		= "Iniciar la edicion";
$LANG['editions_pdf_edition']		= "Edicion en PDF";
$LANG['editions_hitorique_edit']		= "Historico de ediciones";
$LANG['editions_aucun_hitorique']	= "Ninguna  edicion grabada para este usuario ...";
$LANG['editions_numero']			= "Numero";
$LANG['editions_date']				= "Fecha";
$LANG['editions_edit_again']		= "Editar de nuevo";
$LANG['editions_edit_again_pdf']	= "Editar de nuevo en PDF";
//
$LANG['editions_bilan_au']			= "balance a";
$LANG['editions_historique']		= "Historico";
$LANG['editions_soldes_precedents_inconnus']	= "saldo precedente desconocido";
$LANG['editions_solde_precedent']	= "saldo precedente";
$LANG['editions_nouveau_solde']		= "nuevo saldo";
$LANG['editions_signature_1']		= "Firma del titular";
$LANG['editions_signature_2']		= "Firma del responsable";
$LANG['editions_cachet_etab']		= "y sello del establecimiento";
$LANG['editions_jours_an']			= "dias / año";



/***********************/
// SAISIE CONGES
$LANG['saisie_conges_compter_jours']		= "Contar los dias";
$LANG['saisie_conges_nb_jours']				= "N° Dias Tomados";



/***********************/
// SAISIE ECHANGE ABSENCE
$LANG['saisie_echange_titre_calendrier_1']		= "Dia de ausencia previsto";
$LANG['saisie_echange_titre_calendrier_2']		= "Dia de ausencia deseado";



/***********************/
// CALENDRIER
$LANG['calendrier_titre']			= "CALENDARIO de VACACIONES";
$LANG['calendrier_imprimable']		= "version imprimible";
$LANG['calendrier_jour_precedent']	= "Dia Previo";
$LANG['calendrier_jour_suivant']	= "Dia Siguiente";
$LANG['calendrier_legende_we']			= "week-end o dia festivo";
$LANG['calendrier_legende_conges']		= "vacaciones tomadas o a tomar";
$LANG['calendrier_legende_demande']		= "vacaciones solicitadas (aun no concedidas)";
$LANG['calendrier_legende_part_time']	= "ausencia semanal (tiempo parcial , RTT)";
$LANG['calendrier_legende_abs']			= "ausencia otra (mision, formacion, enfermedad, ...)";



/***********************/
// CALCUL NB JOURS
$LANG['calcul_nb_jours_nb_jours']	= "Numero de dias a tomar :";
$LANG['calcul_nb_jours_reportez']	= "trasladar este numero a la casilla";
$LANG['calcul_nb_jours_form']		= "del formulario";



/***********************/
// ERREUR
$LANG['erreur_user']			= "Imposible de identificar el  usuario";
$LANG['erreur_login_password']	= "pareja login/password no valida o login ausente";
$LANG['erreur_session']			= "sesion invalida o expirada";



/***********************/
// INCLUDE_PHP
$LANG['mysql_srv_connect_failed']	= "Imposible de conectarse al servidor ";
$LANG['mysql_db_connect_failed']		= "Imposible de conectarse a la base de datos";

// page d'authentification / login screen
$LANG['cookies_obligatoires']		= "Es necesario que vuestro navegador accepte las cookies para poder conectaros a PHP_VACACIONES.";
$LANG['javascript_obligatoires']		= "Es aconsejado que vuestro navegador accepte el Javascript para utilizar PHP_VACACIONES.";
$LANG['login_passwd_incorrect']		= "ERROR : Nombre de usuario y/o password incorrecto !!!";
$LANG['login_non_connu']				= "ERROR : Usuario no registrado para la gestion de vacaciones !!!";
//
$LANG['login_fieldset']			= "Identificacion";
$LANG['password']					= "Password";
$LANG['msie_alert']				= "Comentario : Ciertos parametros pueden no ser tomados en cuenta por Microsoft IE. Utilicen preferentemente Mozilla Firefox.";


// verif saisie
$LANG['verif_saisie_erreur_valeur_manque']		= "ERROR : entrada incorrecta : <b>faltan valores !!!</b>";
$LANG['verif_saisie_erreur_nb_jours_bad']		= "ERROR : entrada incorrecta : <b>el numero de dias es incorrecto</b>";
$LANG['verif_saisie_erreur_fin_avant_debut']	= "ERROR : entrada incorrecta : <b>la fecha de final es anterior a la fecha de inicio !!!</b>";
$LANG['verif_saisie_erreur_debut_apres_fin']	= "ERROR : entrada incorrecta : <b>la fecha de inicio es posterior a la fecha de final !!!</b>";
$LANG['verif_saisie_erreur_nb_bad']				= "ERROR : entrada incorrecta : <b>el numero entrado es incorrecto</b>";


/***********************/
// CONFIG TYPES ABSENCES
$LANG['config_abs_titre']				= "Configuracion de los tipos de ausencia tratados por PHP_VACACIONES";
$LANG['config_abs_comment_conges']		= "Los tipos de ausencias listados aqui son de las diversas vacaciones, descontadas cada una en cuentas separadas." ;
$LANG['config_abs_comment_absences']	= "Los tipos de ausencias listados aqui no estan descontados (son ausencias autorizadas)." ;
$LANG['config_abs_libelle']				= "titular";
$LANG['config_abs_libelle_short']		= "titular corto";
$LANG['config_abs_add_type_abs']			= "añadir un tipo de ausencia :";
$LANG['config_abs_add_type_abs_comment']	= "Entrar el tipo de ausencia que Ud. quiere a?adir :";
$LANG['config_abs_saisie_not_ok']			= "entrada incorrecta :";
$LANG['config_abs_bad_caracteres']			= "los caracteres siguientes estan prohibidos:";
$LANG['config_abs_champs_vides']			= "los campos estan vacios !";
$LANG['config_abs_suppr_impossible']		= "Supresion IMPOSIBLE !";
$LANG['config_abs_already_used']			= "Las vacaciones/ausencias de este tipo estan en curso !";
$LANG['config_abs_confirm_suppr_of']		= "Por favor confirmar la supresion de";



/***************************/
// CONFIGURACION PHP_CONGES
$LANG['config_appli_titre_1']		= "Configuracion de la Aplicacion PHP_VACACIONES";
$LANG['config_appli_titre_2']		= "Configuracion de php_vacaciones";
//groupes de param?tres
$LANG['00_php_conges']				= "00 php_vacaciones";
$LANG['01_Serveur Web']				= "01 Servidor Web";
$LANG["02_PAGE D'AUTENTIFICATION"]	= "02 PAGINA DE AUTENTIFICACION";
$LANG['03_TITRES']					= "03 TITULOS";
$LANG['04_Authentification']		= "04 Autentificacion";
$LANG['05_Utilisateur']				= "05 Usuario";
$LANG['06_Responsable']				= "06 Responsable";
$LANG['07_Administrateur']			= "07 Administrador";
$LANG['08_Mail']					= "08 Mail";
$LANG['09_jours ouvrables']			= "09 dias laborales";
$LANG['10_Gestion par groupes']		= "10 Gestion por grupos";
$LANG['11_Editions papier']			= "11 Ediciones papel";
$LANG["12_Fonctionnement de l'Etablissement"]	= " 12 Funcionamiento del Establecimiento";
$LANG['13_Divers']					= "13 Varios";
$LANG['14_Pr?sentation']			= "14 Presentacion";
$LANG['15_Modules Externes']		= "15 Modulos Externos";
// parametres de config
$LANG['config_comment_installed_version']	= "numero de version instalada";
$LANG['config_comment_lang']				= "// IDIOMA / LENGUAJE<br>\n//---------------------------<br>\n// fr = frances<br>\n// test = solo para los programadores de php_vacaciones (only for php_conges developpers)";
$LANG['config_comment_URL_ACCUEIL_CONGES']	= "// URL DE BASE DE VUESTRA INSTALACION DE PHP_VACACIONES<br>\n//-------------------------------------------------<br>\n// URL de base de php_vacaciones sobre vuestro servidor (lo que Ud devbe escribir para obtener la pagina de autentificacion.<br>\n// (NO termine por un / y sin el index.php al final)<br>\n// URL_INICIO_VACACIONES = \"http://monserveurweb.mondomaine/php_conges\"";
$LANG['config_comment_img_login']			= "// IMAGEN DE LA PAGINA DE LOGIN<br>\n//---------------------------<br>\n// imagen que aparece en lo alto de la pagina de autentificacion de php_vacaciones";
$LANG['config_comment_texte_img_login']		= "// TEXTO DE LA IMAGEN<br>\n//-------------------<br>\n// texto de la imagen";
$LANG['config_comment_lien_img_login']		= "// ENLACE DE LA IMAGEN<br>\n//------------------<br>\n// URL donde reenvia la imagen de la pagina de login";
$LANG['config_comment_titre_calendrier']	= "Titulo de la pagina calendario de php_vacaciones";
$LANG['config_comment_titre_user_index']	= "Titulo de las paginas Usuario (sera seguido del login del usuario)";
$LANG['config_comment_titre_resp_index']	= "Titulo de las paginas Responsable";
$LANG['config_comment_titre_admin_index']	= "Titulo de las paginas Administrador";
$LANG['config_comment_auth']				= "// Autentificacion :<br>\n//---------------------<br>\n// si = FALSE : ninguna autentificacion al comienzo, hace falta pasar el parametro login a la llamada de php_vacaciones<br>\n// si = TRUE  : la pagina de autentificacion aparece a la llamada de php_vacaciones (TRUE es el valor por defecto)";
$LANG['config_comment_how_to_connect_user']	= "// Como verificar el login y password de los usuarios al inicio :<br>\n//--------------------------------------------------------------------------<br>\n// si a \"dbconges\" : la autentificacion de los usuarios se hace dentro la tabla usuarios de la database db_conges<br>\n// si a \"ldap\"     : la autentificacion de los usuarios se hace dentro de un anuario LDAP que vamos interrogar (cf config_ldap.php)<br>\n// si a \"CAS\"      : la autentificacion de los usuarios se hace sobre un servidor CAS que vamos interrogar (cf config_CAS.php)<br>\n// atencion : toda otro valor que \"dbconges\" o \"ldap\" o \"CAS\" comportara un error !!!";
$LANG['config_comment_export_users_from_ldap']	= "// Exportacion de los Usuarios desde LDAP :<br>\n//--------------------------------<br>\n// si = FALSE : los usuarios son creados \"a la mano\" directamente dentro php_vacaciones (FALSE es el valor por defecto)<br>\n// si = TRUE  : los usuarios son importados del servidor LDAP (gracias a una lista) (cf config_ldap.php)";
$LANG['config_comment_user_saisie_demande']		= "//  SOLICITUD DE VACACIONES<br>\n//---------------------------------------<br>\n// si FALSE : ninguna entrada de solicitud por el usuario, ninguna gestion de las solicitudes por el responsable<br>\n// si TRUE : entrada de solicitud por el usuario, y gestion de las solicitudes por el responsable (TRUE es el valor por defecto)";
$LANG['config_comment_user_affiche_calendrier']	= "//  APARICION DEL BOTON DE CALENDARIO PARA EL USUARIO<br>\n//--------------------------------------------------------------------------------------<br>\n// si FALSE : los usuarios no tienen la posibilidad de ver el calendario de las vacaciones<br>\n// si TRUE : los usuarios tienen la posibilidad de ver el calendario de las vacaciones (TRUE es el valor por defecto)";
$LANG['config_comment_user_saisie_mission']		= "//  ENTRADA DE LAS AUSENCIAS PARA MISIONES, FORMATIONES, CONGRESOS, ETC .... POR EL USUARIO<br>\n//--------------------------------------------------------------------------------------<br>\n// ( las ausencias de este tipo no quitan dias de vacaciones ! )<br>\n// si FALSE : ninguna entrada por el usuario de ausencias por mision, formacion, congreso, etc ....<br>\n// si TRUE : entrada por el usuario de las ausencias por mision, formacion, congreso, etc .... (TRUE es el valor por defecto)";
$LANG['config_comment_user_ch_passwd']			= "//  CAMBIAR SU PASSWORD<br>\n//---------------------------------------<br>\n// si FALSE : el usuario no puede cambiar su password<br>\n// si TRUE : el usuario puede cambiar su password (TRUE es el valor por defecto)";
$LANG['config_comment_responsable_virtuel']		= "//  RESPONSABLE GENERICO VIRTUAL O NO<br>\n//-------------------------------------------<br>\n// si FALSE : el responsable que trate las vacaciones del personal es una persona real (usuario de php_vacaciones) (FALSE es el valor por defecto)<br>\n// si TRUE : el responsable que trate las vacaciones del personal es un usuario generico virtual (login=conges)";
$LANG['config_comment_resp_affiche_calendrier']	= "//  APARICION DEL BOTON DE CALENDARIO PARA EL RESPONSABLE<br>\n//--------------------------------------------------------------------------------------<br>\n// si FALSE : los responsables no tienen la posibilidad de ver el calendario de las vacaciones<br>\n// si TRUE : los responsables tienen la posibilidad de ver el calendario de las vacaciones (TRUE es el valor por defecto)";
$LANG['config_comment_resp_saisie_mission']		= "//  ENTRADA DE LAS AUSENCIAS POR MISIONES, FORMACIONES, CONGRESOS, ETC .... POR EL RESPONSABLE<br>\n//---------------------------------------------------------------------------------------<br>\n// ( las ausencias de este tipo no quitan dias de vacaciones ! )<br>\n// si FALSE : ninguna entrada para el responsable de las ausencias por mision, formacion, congreso, etc ....(FALSE es el valor por defecto)<br>\n// si TRUE : entrada por el responsable de las ausencias por mision, formacion, congreso, etc ....";
$LANG['config_comment_resp_vertical_menu']		= "//  CONFIG  DEL MENU DEL RESPONSABLE<br>\n//---------------------------------------<br>\n// si TRUE : dentro la ventana responsable, el menu es vertical (a la izquierda) (TRUE es el valor por defecto)<br>\n// si FALSE : dentro de la ventana responsable, el menu es horizontal (en lo alto)";
$LANG['config_comment_admin_see_all']			= "//  CONFIG  DEL MODO ADMINISTRADOR<br>\n//---------------------------------------<br>\n// si FALSE : el administrador solo gestiona los users de los que es responsable (FALSE es el valor por defecto)<br>\n// si TRUE : el administrador gestiona todos los users";
$LANG['config_comment_admin_change_passwd']		= "//  CAMBIAR EL PASSWORD DE UN USUARIO<br>\n//-----------------------------------------<br>\n// si FALSE : el administrador no puede cambiar el password de los usuarios<br>\n// si TRUE : el administrador puede cambiar el password de los usuarios (TRUE es el valor por defecto)";
$LANG['config_comment_affiche_bouton_config_pour_admin']			= "// ACCESO A LA CONFIG DE LA APPLICACION PARA LOS ADMINISTRADORES<br>\n//-------------------------------------------------------<br>\n// si FALSE : el boton de acceso a la configuracion de php_vacaciones no aparece sobre la pagina administrador (FALSE es la valor por defecto)<br>\n// si TRUE : el boton de acceso a la configuracion de php_vacaciones aparece en la pagina administrador";
$LANG['config_comment_affiche_bouton_config_absence_pour_admin']	= "// ACCESO A LA CONFIG DE LOS TIPOS DE AUSENCIA DE LA APPLI PARA LOS ADMINS<br>\n//---------------------------------------------------------------------<br>\n// si FALSE : el boton de acceso a la configuracion de los tipos de ausencia gestionados por php_vacaciones no aparece sobre la pagina administrador (FALSE es el valor por defecto)<br>\n// si TRUE : el boton de acceso a la configuracion de los tipos de ausencia gestionados por php_vacaciones aparece en la pagina administrador";
$LANG['config_comment_mail_new_demande_alerte_resp']	= "// ENVIO DE MAIL AL RESPONSABLE PARA UNA NUEVA PETICION :<br>\n//----------------------------------------------------------<br>\n// si FALSE : el responsable no recibe el mail despues de una nueva peticion de vacaciones por un usuario (FALSE es el valor por defecto)<br>\n// si TRUE : el responsable recibe un mail de advertencia a cada nueva peticion de vacaciones de un usuario<br>\n// (ATENCION : usted puede personalizar el titulo y el texto del mail de alerta mas abajo dentro de este fichero)";
$LANG['config_comment_mail_valid_conges_alerte_user']	= "// ENVIO DE MAIL AL USUARIO PARA UNAS NUEVAS VACACIONES ENTRADAS O VALIDAS :<br>\n//----------------------------------------------------------------<br>\n// si FALSE : el usuario no recibe el mail cuando el responsable le acepta una nuevas vacaciones (FALSE es el valor por defecto)<br>\n// si TRUE : el usuario recibe un mail de advertencia a cada vez que el responsable entra una nuevas vacaciones o acepta una solicitud para el<br>\n// (ATENCION : usted puede personalizar el titulo y el texto del mail de alerta mas abajo dentro de este fichero)";
$LANG['config_comment_mail_refus_conges_alerte_user']	= "// ENVIO DE MAIL AL USUARIO POR EL RECHAZO DE UNA SOLICITUD DE VACACIONES :<br>\n//----------------------------------------------------------------<br>\n// si FALSE : el usuario no recibe el mail cuando el responsable rechaza una de sus solicitudes de vacaciones (FALSE es el valor por defecto)<br>\n// si TRUE : el usuario recibe un mail de advertencia cada vez que el responsable rechaza una de sus solicitudes de vacaciones <br>\n// (ATENCION : usted puede personalizar el titulo y el texto del mail de alerta mas abajo dentro de este fichero)";
$LANG['config_comment_mail_annul_conges_alerte_user']	= "// ENVIO DE MAIL AL USUARIO POR LA ANULACION POR EL RESP DE UNAS VACACIONES YA ACEPTADAS :<br>\n//---------------------------------------------------------------------------------<br>\n// si FALSE : el usuario no recibe el mail cuando el responsable le anula unas vacaciones (FALSE es el valor por defecto)<br>\n// si TRUE : el usuario recibe un mail de advertencia cada vez que el responsable anula unas de sus vacaciones<br>\n// (ATENCION : usted puede personalizar el titulo y el texto del mail de alerta mas abajo dentro de este fichero)";
$LANG['config_comment_serveur_smtp']					= "//  SERVIDOR SMTP A UTILIZAR<br>\n//---------------------------------------<br>\n// direccion ip  o  nombre del servidor smpt a utilizar para enviar los mails<br>\n// Si usted no domina el servidor SMTP o si, a la utilizacion, usted tiene un error a la connexion al servidor, deje esta variable vacia (\"\")";
$LANG['config_comment_where_to_find_user_email']		= "//  DONDE ENCONTRAR LAS DIRECCIONES MAILS DE LOS USUARIOS<br>\n//-------------------------------------------------<br>\n// varias posibilidades para encontrar las direcciones mails de los usuarios :<br>\n// si ? \"dbconges\" : el mail de los user se encuentra dentro de la tabla users de la database db_vacaciones<br>\n// si ? \"ldap\"     : el mail de los user se encuentra dentro de un anuario LDAP que vamos a interrogar (cf fichero config_ldap.php)<br>\n// ATENCION : todo otro valor que \"dbconges\" o \"ldap\" comportara une ?rreur !!!";
$LANG['config_comment_samedi_travail']		= "//  GESTION DE LOS SABADOS COMO TRABAJADOS O NO<br>\n//--------------------------------------------------------------------------------------<br>\n// definimos aqui si los sabados pueden ser trabajados o no.<br>\n// si TRUE :  el dia se considera como trabajado....<br>\n// si FALSE : el dia se considera como no trabajado (weeekend).... (FALSE es el valor por defecto)";
$LANG['config_comment_dimanche_travail']	= "//  GESTION DE LOS DOMINGOS COMO TRABAJADOS O NO<br>\n//--------------------------------------------------------------------------------------<br>\n// definimos aqui si los domingos pueden ser trabajados o no.<br>\n// si TRUE : el dia se considera como trabajado ....<br>\n// si FALSE : el dia se considera como no trabajado (weeekend).... (FALSE es el valor por defecto)";
$LANG['config_comment_gestion_groupes']		= "//  GESTION DE LOS GRUPOS DE USUARIOS<br>\n//--------------------------------------<br>\n// definimos aqui si queremos gestionar a los usuarios por grupos o no.<br>\n// si TRUE : los grupos de usuarios son gestionados dentro de la aplicacion ....<br>\n// si FALSE : los grupos de usuarios no son gestionados dentro de la aplicacion .... (FALSE es el valor por defecto)";
$LANG['config_comment_affiche_groupe_in_calendrier']	= "//  VER EL CALENDARIO : todos los usuarios o los usuarios de un grupo solamente<br>\n//--------------------------------------------------------------------------------------------<br>\n// si FALSE : todas las personas aparecen en el calendario de vacaciones (FALSE es el valor por defecto)<br>\n// si TRUE : solo las personas del mismo grupo que el usuario aparecen en el calendario de vacaciones";
$LANG['config_comment_editions_papier']				= "//  EDICION PAPEL<br>\n//--------------------------------------<br>\n// decidimos aqui si el responsable puede imprimir las vacaciones de un user.<br>\n// si TRUE : las impresiones son disponibles ....(TRUE es el valor por defecto)<br>\n// si FALSE : las impresiones no son disponibles en la aplicacion ....";
$LANG['config_comment_texte_haut_edition_papier']	= "//  Cabecera de las IMPRESIONES<br>\n//--------------------------------------<br>\n// definimos aqui el texto eventual que figurara en lo alto de la pagina de las impresiones de las vacaciones de un user.";
$LANG['config_comment_texte_bas_edition_papier']	= "//  Pie de pagina de las IMPRESIONES<br>\n//--------------------------------------<br>\n// definimos aqui el texto eventual que figurara en el pie de pagina de las impresiones de las vacaciones de un user.";
$LANG['config_comment_user_echange_rtt']			= "//  CAMBIO RTT O TIEMPO PARCIAL AUTORIZADO PARA LOS USUARIOS<br>\n//---------------------------------------------------------------------------------------<br>\n// autorizamos o no al usuario a invertir puntualmente un dia trabajado y un dia de ausencia (de rtt o tiempo parcial)<br>\n// si FALSE : cambio no autorizado para el usuario (FALSE es el valor por defecto)<br>\n// si TRUE : cambio autorizado para el usuario";
$LANG['config_comment_affiche_bouton_calcul_nb_jours_pris']	= "//  BOTON DE CALCULO DEL N? DE DIAS TOMADOS<br>\n//--------------------------------------------------------------------------------------<br>\n// si FALSE : no aparece el boton de calculo del n? de dias tomados cuando se efectua una nueva entrada de ausencia<br>\n// si TRUE : aparece el boton de calculod del n? de dias tomados cuando se efectua una nueva entrada de ausencia (TRUE es el valor por defecto)<br>\n// ATENCION : si es TRUE : los dias no trabajados deben ser registrados (ver el modulo de administracion)";
$LANG['config_comment_rempli_auto_champ_nb_jours_pris']		= "//  RELLENO AUTOMATICO DEL CAMPO EN EL MOMENTO DE LA SOLICITUD DEL CALCULO DEL N? DE DIAS TOMADOS<br>\n//--------------------------------------------------------------------------------------<br>\n// si FALSE : la solicitud al boton de calculo del n? de dias tomados no rellena automaticamente el campo del formulario (entrada a mano)<br>\n// si TRUE : la solicitud al boton de calculo del n? de dias tomados rellena automaticamente el campo del formulario (TRUE es el valor por defecto)";
$LANG['config_comment_duree_session']	= "// Duracion maxima de incatividad de una sesion antes de la expiracion (en segundos)";
$LANG['config_comment_stylesheet_file']	= "// HOJA DE ESTILO<br>\n//--------------------------<br>\n// nombre del fichero de la hoja de estilo a utilizar (con camino relativo desde la raiz de php_vacaciones)";
$LANG['config_comment_bgcolor']			= "// color de fondo de las paginas";
$LANG['config_comment_bgimage']			= "// imagen de fondo de las paginas (NO de / al inicio !!)";
$LANG['config_comment_light_grey_bgcolor']					= "// colores diversos (gris claro)";
$LANG['config_comment_php_conges_fpdf_include_path']		= "// CAMINO HACIA EL REPERTORIO DE fpdf<br>\n//-------------------------------------------------------<br>\n// Definimos aqui el camino para acceder al repertorio de la libreria PHP \"fpdf\".<br>\n// El camino debe ser relativo desde la raiz de la aplicacion php_vacaciones.";
$LANG['config_comment_php_conges_phpmailer_include_path']	= "// CAMINO HACIA EL REPERTORIO DE phpmailer<br>\n//-------------------------------------------------------<br>\n// Definimos aqui el camino para acceder al repertorio de la libreria PHP \"phpmailer\".<br>\n// El camino debe ser relativo desde la raiz de la aplicacion php_vacaciones.";
$LANG['config_comment_php_conges_cas_include_path']			= "// CAMINO HACIA EL REPERTORIO DE cas<br>\n//-------------------------------------------------------<br>\n// Definimos aqui el camino para acceder al repertorio de la libreria PHP \"CAS\".<br>\n// El camino debe ser relativo desde la raiz de la aplicacion php_vacaciones.";
$LANG['config_comment_php_conges_authldap_include_path']	= "// CAMINO HACIA EL fichero authLDAP.php<br>\n//-------------------------------------------------------<br>\n// Definimos aqui el camino para acceder al repertorio de la libreria PHP \"authLDAP.php\".<br>\n// El camino debe ser relativo desde la raiz de la aplicacion php_vacaciones.";



/***************************/
// INSTALLATION PHP_CONGES
//page index
$LANG['install_le_fichier']		= "El fichero";
$LANG['install_bad_fichier']	= "no se encuentra dentro el repertorio raiz del nuevo php_vacaciones, o no hay los derechos en lecture suficiente";
$LANG['install_read_the_file']	= "dirigirse al fichero";
$LANG['install_reload_page']	= "despues recargar esta pagina";
$LANG['install_db_inaccessible']		= "la database no es accesible";
$LANG['install_verifiez_param_file']	= "Por favor verificar los parametros del fichero";
$LANG['install_verifiez_priv_mysql']	= "Aseguraros que la database, el usuario y los privilegios MySql han estado correctamente creados.";
$LANG['install_install_phpconges']		= "Instalacion de php_vacaciones";
$LANG['install_index_titre']			= "Aplicacion PHP_VACACIONES";
$LANG['install_no_prev_version_found']	= "Ninguna version anterior no ha podido ser constatada";
$LANG['install_indiquez']				= "Por favor indicar si se trata";
$LANG['install_nouvelle_install']		= "de una Nueva Instalacion";
$LANG['install_mise_a_jour']			= "de una Actualizacion";
$LANG['install_indiquez_pre_version']	= "por favor indicar la version ya instalada";
$LANG['install_installed_version']		= "version ya instalada";
$LANG['install_configuration']			= "Configuracion";
$LANG['install_config_appli']			= "configurar la aplicacion";
$LANG['install_config_types_abs']		= "configurar los tipos de vacaciones a gestionar";
//page install
$LANG['install_install_titre']			= "Instalacion de la aplicacion PHP_VACACIONES";
$LANG['install_impossible_sur_db']		= "imposible sobre la database";
$LANG['install_verif_droits_mysql']		= "verificar los derechos mysql de";
$LANG['install_puis']					= "despues";
$LANG['install_ok']						= "Instalacion efectuada con exito";
$LANG['install_vous_pouvez_maintenant']	= "Udsted puede ahora";
$LANG['install_acceder_appli']			= "acceder a la aplicacion";
//page mise_a_jour
$LANG['install_version_non_choisie']	= "la version a actualizar no ha sido seleccionada";
$LANG['install_maj_titre_1']			= "Actualizacion";
$LANG['install_maj_titre_2']			= "Actualizacion de la aplicacion PHP_VACACIONES";
$LANG['install_maj_passer_de']			= "usted esta a punto de pasar de la version";
$LANG['install_maj_a_version']			= "a la version";
$LANG['install_maj_sauvegardez']		= "Antes de continuar, tener la precaucion de hacer una copia de seguridad de vuestra base de datos.";
$LANG['install_etape']					= "etapa";
$LANG['install_inaccessible']			= "no es accesible";
$LANG['install_maj_conserv_config']		= "Afin de asegurar la conservacion de vuestra configuracion,";
$LANG['install_maj_copy_config_file']	= "por favor copiar vuestro antiguo fichero config.php dentro el nuevo repertorio";
$LANG['install_maj_whith_name']			= "bajo el nombre";
$LANG['install_maj_and']				= "y";
$LANG['install_maj_verif_droit_fichier']	= "verificar los derechos de lectura sobre este fichero.";



/***********************/
/***********************/
/***********************/
// NEW : V1.2
$LANG['divers_date_traitement']	= "fecha-hora demanda/tratamiento";
$LANG['divers_demande']		= "demanda";
$LANG['divers_traitement']	= "tratamiento";
$LANG['divers_mois']		= "mes";
$LANG['divers_annee']		= "añ";

$LANG['phpmailer_not_valid']	= "ERROR MAIL : El fichero class.phpmailer.php no puede ser leido o no se encuentra. El mail de alerta no podra ser enviado !";
$LANG['fpdf_not_valid']		= "ERROR : El fichero fpdf.php no puede ser leido o no se encuentra. El documento PDF no puede ser generado !";

$LANG['install_remove_fichier']	= "es de aqui en adelante inutil... Por favor suprimalo !";
$LANG['install_config_mail']	= "configurar los mails enviados por php_conges";

/***********************/
// EXPORT ICAL / VCAL
$LANG['button_export_1']			= "Exportar ical / vcal";
$LANG['button_export_2']			= "Exportar los eventos al formato ical / vcal";
$LANG['config_comment_disable_saise_champ_nb_jours_pris']	= "//  PROHIBIDO TECLEAR EN EL CAMPO TEXTO DEL N° DE DIAS TOMADOS<br>\n//--------------------------------------------------------------------------------------<br>\n// si - FALSE : el campo texto del n° de dias tomados es activo (coger la mano posible)(FALSE es el valor por defecto)<br>\n// si \ TRUE : le champ texte du nb de jours pris et inactif (saisi  la main impossible)";
$LANG['calcul_nb_jours_commentaire']			= "atencion: este periodo se superpone con otras vacaciones !";
$LANG['calcul_nb_jours_commentaire_impossible']	= "CALCULO IMPOSIBLE : este periodo se superpone con una solicitud de vacaciones en curso !";
$LANG['config_comment_export_ical_vcal']	= "//  EXPORTAR DESDE VACACIONES AL FORMATO ICS OU VCS<br>\n//----------------------------------------------------------------------<br>\n// si O FALSE : los usuarios no pueden exportar sus vacaciones/ausencias al formato ics o vcs.<br>\n// si  TRUE : los usuarios pueden exportar sus vacaciones/ausencias al formato ics ou vcs (para importar en la agenda electronica o al programa de planning). (TRUE es el valor por defecto)";
$LANG['export_cal_titre']		= "Exportar al formato ical / vcal";
$LANG['export_cal_from_date']	= "de";
$LANG['export_cal_to_date']		= "hasta";
$LANG['export_cal_saisir_debut']	= "seleccion de la fecha de inicio";
$LANG['export_cal_saisir_fin']		= "seleccion de la fecha de fin";
$LANG['export_cal_format']		= "opcion del formato";

/***************************/
// CONFIG
/*
$LANG['config_comment_semaine_bgcolor']			= "color de fondo de los dias de la semana en el calendario";
$LANG['config_comment_week_end_bgcolor']		= "color de fondo de los dias del fin de semana en el calendario";
$LANG['config_comment_temps_partiel_bgcolor']	= "color de fondo de los dias de tiempo parcial o de la rtt en el calendario";
$LANG['config_comment_conges_bgcolor']			= "color de fondo de los dias de vacaciones en el calendario (vacaciones aceptadas por el responsable)";
$LANG['config_comment_demande_conges_bgcolor']	= "color de fondo de los dias de vacaciones solicitadas en el calendario (no aceptadas aun por el responsable)";
$LANG['config_comment_absence_autre_bgcolor']	= "color de fondo de los dias de ausencia por mision, etc ... en el calendario";
*/
/***************************/
// MAIL DE PHP_CONGES
$LANG['mail_new_demande_comment']			= "mensaje de alerta al responsable en caso de nueva demanda de vacaciones.";
$LANG['mail_valid_conges_comment']			= "mensaje de alerta al usuario en caso de validation d'une demande de congs ou de saisie d'un nouveau conges par le responsable";
$LANG['mail_refus_conges_comment']			= "mensaje de alerta al usuario en caso de rechazo de una demanda de vacaciones por el responsable";
$LANG['mail_annul_conges_comment']			= "mensaje de alerta al usuario en caso de anulacion de vacaciones por el responsable";
$LANG['mail_prem_valid_conges_comment']		= "mensaje de alerta al usuario en caso de primera validacion de una demanda de vacaciones por el responsable (si utilizamos la doble validacion).";

$LANG['mail_remplace_url_accueil_comment']		= "__URL_ACCUEIL_CONGES__ sera automaticamente remplazada por un link hacia vuestra aplicacion php_conges.";
$LANG['mail_remplace_sender_name_comment']		= "__SENDER_NAME__ sera automaticamente remplazada por le nom et prxnom de l'expediteur.";
$LANG['mail_remplace_destination_name_comment']	= "__DESTINATION_NAME__ sera automaticamente remplazada por el apellido y el nombre del (de los) destinatario(s).";
$LANG['mail_remplace_retour_ligne_comment']		= "__RETOUR_LIGNE__ sera automaticamente remplazada por pase - la linea.";

/***********************/
// CONFIG MAILS
$LANG['config_mail_titre']		= "Configuracion de los mails enviados por PHP_CONGES";
$LANG['config_mail_alerte_config']	= "Esta pagina solo sirve si  el envio de mail por php_conges es valido (ver configuracion de la aplicaion).";
$LANG['config_mail_subject']	= "sujeto del mail";
$LANG['config_mail_body']		= "texto del mail";

$LANG['admin_button_config_mail_1']		= "Configuracion de los mails enviados por php_conges";
$LANG['admin_button_config_mail_2']		= "Config Mails";
$LANG['config_comment_affiche_bouton_config_mail_pour_admin']	= "// ACCESO A LA CONFIG DE LOS MAILS DE LA APPLI PARA LOS ADMINISTRADORES<br>\n//---------------------------------------------------------------------<br>\n// si  FALSE : el boton de acceso de configuracion de mails enviados por php_conges no aparece sobre la pagina administrador (FALSE es el valor por defecto)<br>\n// si ] TRUE : el boton de acceso a la configuracion de los mails enviados por php_vacaciones aparece sobre la pagina administrador";
$LANG['config_comment_mail_prem_valid_conges_alerte_user']	= "// ENVIO DE MAIL AL USUARIO Y GRAN REPONSABLE DESPUES LA PRIMERA VALIDACION DE UNA DEMANDA (caso de una doble validacion de demanda) :<br>\n//----------------------------------------------------------------<br>\n// si  FALSE : el usuario y el gran responsable no reciben el mail cuando el responsable valida una demanda (primera validacion). (FALSE es el valor por defecto)<br>\n// si es TRUE : el usuario y el gran responsable reciben un mail de alerta cuando el responsable valida una demanda (primera validacion).<br>\n";
$LANG['config_comment_affiche_date_traitement']	= "// PRESENTACION DE LOS DATOS Y HORA DE TRATAMIENTO EN LOS HISTORICOS DE LAS VACACIONES<br>\n//---------------------------------------------------------------------<br>\n// si  FALSE : los datos y horas de demanda de vacaciones o de validacion de demanda no seran presentados en los tableros (FALSE es el valor por defecto)<br>\n// si  TRUE : los datos y horas de demanda de vacaciones o de validacion de demanda seran presentados en los tableros y ditions (attention, cela augmente consid rablement la largeur de la page affiche).";

/***********************/
// DOUBLE VALIDATION
$LANG['config_comment_double_validation_conges']	= "//  DOBLE VALIDACION DE LAS DEMANDAS DE VACACIONES<br>\n//----------------------------------------------------------------------<br>\n// si   FALSE : la demanda de vacaciones de un usuario es aceptada o no por el responsble (une sola validacion)(FALSE es el valor por defecto).<br>\n// si  TRUE : la demanda de vacaciones de un usuario debe ser validada por un responsable, luego aceptada por el responsable del responsable (doble validacion).<br>\nATENCION, este parametro no es valido QUE en el caso de una gestion por grupos de usuarios !)";
$LANG['admin_groupes_double_valid']		= "doble validacion";
$LANG['admin_gestion_groupe_grand_resp_responsables']	= "Grandes Responsables del Grupo";
$LANG['divers_grand_responsable_maj_1']	= "Gran_Responsable";
$LANG['resp_traite_demandes_titre_tableau_1']	= "Demandas o validar";
$LANG['resp_traite_demandes_titre_tableau_2']	= "Demandas en segunda validacion";
$LANG['resp_traite_user_etat_demandes_2_valid']		= "Estado de las demandas en segunda validacion :";
$LANG['resp_etat_users_titre_double_valid']		= "Usuarios con doble validacion de las demandas";
$LANG['resp_etat_aucun_user']	= "Ningun usuario para este responsable !!!";

/***********************/
// IMPRIM CALENDRIER
$LANG['button_imprim_calendar']	= "imprimir un calendario";
$LANG['imprim_calendrier_titre']	= "escoger el mes a imprimir";



/***********************/
/***********************/
/***********************/
// NEW : V1.2.1

//$LANG['install_test_mail']	= "tester l'envoi de mails de php_conges";

//$LANG['session_pas_session_ouverte']	= "Pas de session ouverte." ;

//$LANG['divers_acces_page_interdit']	= "ACCES A CETTE PAGE INTERDIT !!" ;
//$LANG['divers_user_disconnected']	= "Vous avez été déconnecté de l'application !" ;
//$LANG['divers_veuillez']			= "Veuillez" ;
//$LANG['divers_vous_authentifier']	= "vous authentifier" ;

/***************************/
// CONFIGURATION PHP_CONGES
//$LANG['14_Presentation']			= "14 Présentation";
//$LANG['config_comment_affiche_soldes_calendrier']	= "//  AFFICHAGE DES SOLDES UTILISATEURS DANS LE CALENDRIER : <br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : les soldes de congés des utilisateurs n'apparaissent pas sur le calendrier des congés<br>\n// si à TRUE : les soldes de congés des utilisateurs apparaissent sur le calendrier des congés (TRUE est la valeur par defaut)";
//$LANG['mail_remplace_nb_jours']			= "__NB_OF_DAY__ sera automatiquement remplacé par le nombre des jours de l'absence concernée.";



/***********************/
/***********************/
/***********************/
// NEW : V1.3.0

/***********************/
//$LANG['divers_traitement_ok']		= "accepté";
//$LANG['divers_traitement_refus']	= "refusé";
//$LANG['divers_traitement_annul']	= "annulé";

// GESTION CONGES EXCEPTIONNELS
//$LANG['config_comment_gestion_conges_exceptionnels']    = "//  CONGES EXCEPTIONNELS<br>\n//----------------------------------------------------------------------<br>\n// congés avec un  nombre de jours annuel toujours à 0<br>\n// si à FALSE : les congés de type 'congés exceptionnels' ne sont pas gérés.(FALSE est la valeur par defaut)<br>\n// si à TRUE : il est possible de définir des congés de type 'conges exceptionnels' pour les utilisateurs (congés sans solde annuel).";
//$LANG['config_abs_comment_conges_exceptionnels']		= "Les types de conges listés ici n'ont pas de taux annuel." ;
//$LANG['config_abs_desactive_cong_excep_impossible']		= "IMPOSSIBLE de désactiver les congés exceptionnels! (aucun congés exceptionnel ne doit être enregistré pour effectuer cette opération.)";

//$LANG['divers_conges_exceptionnels']		= "congés exceptionnels";
//$LANG['divers_conges_exceptionnels_maj_1']	= "Congés Exceptionnels";
//$LANG['divers_semaine']	= "semaine";

// CONFIG
//$LANG['config_comment_grand_resp_ajout_conges']    = "//  AJOUT DE CONGES PAR LE GRAND RESPONSABLE<br>\n//----------------------------------------------------------------------<br>\n// ne fonctionne QUE si la double validation est activée !<br>\n// si à FALSE : un responsable ne peut ajouter de congés qu'à ses utilisateur direct (dont il est 'responsable').(FALSE est la valeur par defaut)<br>\n// si à TRUE : un responsable peut ajouter de congés aux utilisateur dont il est 'responsable' ET 'grand responsable' !";
//$LANG['config_comment_interdit_saisie_periode_date_passee']    = "//  INTERDIRE LES SAISIES DE CONGES POUR LES DATES PASSEES<br>\n//----------------------------------------------------------------------<br>\n//si à FALSE : il est possible de saisir une demande de congés pour une date passée.(FALSE est la valeur par defaut)<br>\n// si à TRUE : il est interdit (et impossible) de saisir une demande de congés pour une date passée. ";



/***********************/
/***********************/
/***********************/
// NEW : V1.3.1

/***********************/
//$LANG['calcul_nb_jours_commentaire_bad_date']	= "attention: date de fin antérieure à date de début !";
//$LANG['calcul_impossible']						= "attention: Calcul Impossible !";
//$LANG['jours_feries_non_saisis']				= "les jours fériés ne sont pas enregistrés pour l'année voulue.";
//$LANG['contacter_admin']						= "Contactez votre administrateur.";

// CONFIG
//$LANG['config_comment_interdit_modif_demande']    = "//  INTERDIRE LA MODIFICATION D'UNE DEMANDE DE CONGES PAR UN UTILISATEUR<br>\n//----------------------------------------------------------------------<br>\n//si à FALSE : l'utilisateur peut modifier une demande de congés existante.(FALSE est la valeur par defaut)<br>\n// si à TRUE : il est interdit (et impossible) pour l'utilisateur de modifier une demande de congés existante. ";





/***********************/
/***********************/
/***********************/
// NEW : V1.3.2

/***********************/
//$LANG['calendrier_afficher_groupe']	= "groupe à afficher";
//$LANG['divers_groupe']				= "groupe";
//$LANG['config_logs']					= "voir les logs";
//$LANG['config_logs_titre_1']			= "Gestion des logs de PHP_CONGES";
//$LANG['config_logs_titre_2']			= "Gestion des logs";
//$LANG['voir_les_logs_par']				= "pour voir les actions d'un seul user : cliquez sur son login en gras.";
//$LANG['voir_tous_les_logs']				= "Pour voir tous les logs";
//$LANG['divers_cliquez_ici']				= "cliquez ici";
//$LANG['form_delete_logs']			= "Vider les Logs";
//$LANG['confirm_vider_logs']			= "Vouler vous vraiment éffacer tous les LOGS de PHP_CONGES ??";
//$LANG['no_logs_in_db']				= "Pas de Logs dans la DataBase !";

//$LANG['divers_date_maj_1']		= "Date";
//$LANG['divers_fait_par_maj_1']	= "Fait par";
//$LANG['divers_pour_maj_1']		= "Pour";

// ajout d'un user
//$LANG['admin_gestion_groupe_users_group_of_new_user']		= "Groupes auxquels le user appartient";

// CONFIG
//$LANG['config_comment_calendrier_select_all_groups']    = "//  AFFICHAGE DU CALENDRIER : Selection du groupe : choix entre tous les groupes ou non<br>\n//--------------------------------------------------------------------------------------------<br>\n// si la gestion des groupes est active, on définit ici si le choix du groupe à afficher dans le calendrier inclus tous les groupes ou pas.<br>\n// si à FALSE : seuls les groupes de l'utilisateur (ou dont il est responsable) apparaissent dans la sélection sur le calendrier des congés (FALSE est la valeur par defaut)<br>\n// si à TRUE : tous les groupes apparaissent dans la sélection sur le calendrier des congés";
//$LANG['config_comment_consult_calendrier_sans_auth']    = "//  AFFICHAGE DU CALENDRIER SANS AUTHENTIFICATION<br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : Il n'est pas possible d'afficher le calendrier des congés sans être connecté à PHP_CONGES. (FALSE est la valeur par defaut)<br>\n// si à TRUE : Possibilité de consulter le calendrier des congés sans se connecter à PHP_CONGES (via un lien sur la page d'accueil, ou avec l'URL directe).";
//$LANG['config_comment_resp_ajoute_conges']				= "//  AJOUT DE CONGES PAR LE RESPONSABLE<br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : Le responsable ne peut pas ajouter de congés à ses utilisateurs.<br>\n// si à TRUE : Le responsable peut ajouter de congés à ses utilisateurs. (FALSE est la valeur par defaut)";


// CONFIGURATION MAILS
//$LANG['mail_remplace_date_debut']			= "__DATE_DEBUT__ sera automatiquement remplacé par la date de début de l'absence concernée.";
//$LANG['mail_remplace_date_fin']				= "__DATE_FIN__   sera automatiquement remplacé par la date de fin   de l'absence concernée.";





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


