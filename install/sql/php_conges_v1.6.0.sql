#
# Base de données: `db_conges`
#

# --------------------------------------------------------
#
# ATTENTION :  toutes les requetes doivent se terminer par un point virgule ";"

# --------------------------------------------------------

#
# Structure de la table `conges_echange_rtt`
#

CREATE TABLE IF NOT EXISTS `conges_echange_rtt` (
  `e_login` varbinary(99) NOT NULL default '',
  `e_date_jour` date NOT NULL default '0000-00-00',
  `e_absence` enum('N','J','M','A') NOT NULL default 'N',
  `e_presence` enum('N','J','M','A') NOT NULL default 'N',
  `e_comment` varchar(255) default NULL,
  PRIMARY KEY  (`e_login`,`e_date_jour`)
) DEFAULT CHARSET=latin1;

#
# Contenu de la table `conges_echange_rtt`
#


# --------------------------------------------------------

#
# Structure de la table `conges_edition_papier`
#

CREATE TABLE IF NOT EXISTS `conges_edition_papier` (
  `ep_id` int(11) NOT NULL auto_increment,
  `ep_login` varbinary(99)  NOT NULL default '',
  `ep_date` date NOT NULL default '0000-00-00',
  `ep_num_for_user` int(5) unsigned NOT NULL default '1',
  PRIMARY KEY  (`ep_id`)
) DEFAULT CHARSET=latin1;

#
# Contenu de la table `conges_edition_papier`
#

# --------------------------------------------------------

#
# Structure de la table `conges_groupe`
#

CREATE TABLE IF NOT EXISTS `conges_groupe` (
  `g_gid` int(11) NOT NULL auto_increment,
  `g_groupename` varchar(50) NOT NULL default '',
  `g_comment` varchar(250) default NULL,
  `g_double_valid` enum('Y','N') NOT NULL default 'N',
  PRIMARY KEY  (`g_gid`)
) DEFAULT CHARSET=latin1;

#
# Contenu de la table `conges_groupe`
#


# --------------------------------------------------------

#
# Structure de la table `conges_groupe_resp`
#

CREATE TABLE IF NOT EXISTS `conges_groupe_resp` (
  `gr_gid` int(11) NOT NULL default '0',
  `gr_login` varbinary(99) NOT NULL default ''
) DEFAULT CHARSET=latin1;

#
# Contenu de la table `conges_groupe_resp`
#

# --------------------------------------------------------

#
# Structure de la table `conges_groupe_grd_resp`
#

CREATE TABLE IF NOT EXISTS `conges_groupe_grd_resp` (
  `ggr_gid` int(11) NOT NULL default '0',
  `ggr_login` varbinary(99) NOT NULL default ''
) DEFAULT CHARSET=latin1;

#
# Contenu de la table `conges_groupe_resp`
#

# --------------------------------------------------------

#
# Structure de la table `conges_groupe_users`
#

CREATE TABLE IF NOT EXISTS `conges_groupe_users` (
  `gu_gid` int(11) NOT NULL default '0',
  `gu_login` varbinary(99) NOT NULL default ''
) DEFAULT CHARSET=latin1;

#
# Contenu de la table `conges_groupe_users`
#

# --------------------------------------------------------

#
# Structure de la table `conges_jours_feries`
#

CREATE TABLE IF NOT EXISTS `conges_jours_feries` (
  `jf_date` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`jf_date`)
) DEFAULT CHARSET=latin1;

#
# Contenu de la table `conges_jours_feries`
#

# --------------------------------------------------------

#
# Structure de la table `conges_periode`
#

CREATE TABLE IF NOT EXISTS `conges_periode` (
  `p_login` varbinary(99) NOT NULL default '',
  `p_date_deb` date NOT NULL default '0000-00-00',
  `p_demi_jour_deb` enum('am','pm') NOT NULL default 'am',
  `p_date_fin` date NOT NULL default '0000-00-00',
  `p_demi_jour_fin` enum('am','pm') NOT NULL default 'pm',
  `p_nb_jours` decimal(5,2) NOT NULL default '0.00',
  `p_commentaire` varchar(50) default NULL,
  `p_type` int(2) UNSIGNED NOT NULL default '1',
  `p_etat` enum('ok', 'valid','demande','ajout','refus','annul') NOT NULL default 'demande',
  `p_edition_id` int(11) default NULL,
  `p_motif_refus` varchar(110) default NULL,
  `p_date_demande` datetime default NULL,
  `p_date_traitement` datetime default NULL,
  `p_fermeture_id` int(5),
  `p_num` int(5) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`p_num`)
) DEFAULT CHARSET=latin1;

#
# Contenu de la table `conges_periode`
#

# --------------------------------------------------------

#
# Structure de la table `conges_users`
#

CREATE TABLE IF NOT EXISTS `conges_users` (
  `u_login` varbinary(99)  NOT NULL default '',
  `u_nom` varchar(30) NOT NULL default '',
  `u_prenom` varchar(30) NOT NULL default '',
  `u_is_resp` enum('Y','N') NOT NULL default 'N',
  `u_resp_login` varbinary(99) default NULL,
  `u_is_admin` enum('Y','N') NOT NULL default 'N',
  `u_is_hr` enum('Y','N') NOT NULL default 'N',
  `u_is_active` enum('Y','N') NOT NULL default 'Y',
  `u_see_all` enum('Y','N') NOT NULL default 'N',
  `u_passwd` varchar(64) NOT NULL default '',
  `u_quotite` int(3) default '100',
  `u_email` varchar(100) default NULL,
  `u_num_exercice` int( 2 ) NOT NULL default '0',
  PRIMARY KEY  (`u_login`),
  KEY `u_login` (`u_login`)
) DEFAULT CHARSET=latin1;

#
# Contenu de la table `conges_users`
#

INSERT IGNORE INTO `conges_users` VALUES ('admin', 'php_conges', 'admin', 'N', 'admin', 'Y', 'N','Y','N', '636d61cf9094a62a81836f3737d9c0da', 100, NULL, 0);
INSERT IGNORE INTO `conges_users` VALUES ('conges', 'conges', 'responsable-virtuel', 'Y', NULL, 'Y', 'Y','Y','N', '3cdb69ff35635d9a3f6eccb6a5e269e6', 100, NULL, 0);

# --------------------------------------------------------

#
# Structure de la table `conges_config`
#

CREATE TABLE IF NOT EXISTS `conges_config` (
  `conf_nom` varchar(100) binary NOT NULL default '',
  `conf_valeur` varchar(200) binary NOT NULL default '',
  `conf_groupe` varchar(200) NOT NULL default '',
  `conf_type` varchar(200) NOT NULL default 'texte',
  `conf_commentaire` text NOT NULL,
  PRIMARY KEY  (`conf_nom`)
) DEFAULT CHARSET=latin1;

#
# Contenu de la table `conges_config`
#

INSERT IGNORE INTO `conges_config` VALUES ('installed_version', '0', '00_php_conges', 'texte', 'config_comment_installed_version');
INSERT IGNORE INTO `conges_config` VALUES ('lang', 'fr', '00_php_conges', 'enum=fr/test', 'config_comment_lang');

INSERT IGNORE INTO `conges_config` VALUES ('URL_ACCUEIL_CONGES', 'http://mon-serveur/mon-chemin/php_conges', '01_Serveur Web', 'texte', 'config_comment_URL_ACCUEIL_CONGES');

INSERT IGNORE INTO `conges_config` VALUES ('titre_calendrier', 'CONGES : Calendrier', '03_TITRES', 'texte', 'config_comment_titre_calendrier');
INSERT IGNORE INTO `conges_config` VALUES ('titre_user_index', 'CONGES : Utilisateur', '03_TITRES', 'texte', 'config_comment_titre_user_index');
INSERT IGNORE INTO `conges_config` VALUES ('titre_resp_index', 'CONGES : Page Responsable', '03_TITRES', 'texte', 'config_comment_titre_resp_index');
INSERT IGNORE INTO `conges_config` VALUES ('titre_admin_index', 'CONGES : Administrateur', '03_TITRES', 'texte', 'config_comment_titre_admin_index');

INSERT IGNORE INTO `conges_config` VALUES ('auth', 'TRUE', '04_Authentification', 'boolean', 'config_comment_auth');
INSERT IGNORE INTO `conges_config` VALUES ('how_to_connect_user', 'dbconges', '04_Authentification', 'enum=dbconges/ldap/CAS', 'config_comment_how_to_connect_user');
INSERT IGNORE INTO `conges_config` VALUES ('export_users_from_ldap', 'FALSE', '04_Authentification', 'boolean', 'config_comment_export_users_from_ldap');
INSERT IGNORE INTO `conges_config` VALUES ('consult_calendrier_sans_auth', 'FALSE', '04_Authentification', 'boolean', 'config_comment_consult_calendrier_sans_auth');

INSERT IGNORE INTO `conges_config` VALUES ('user_saisie_demande', 'TRUE', '05_Utilisateur', 'boolean', 'config_comment_user_saisie_demande');
INSERT IGNORE INTO `conges_config` VALUES ('user_affiche_calendrier', 'TRUE', '05_Utilisateur', 'boolean', 'config_comment_user_affiche_calendrier');
INSERT IGNORE INTO `conges_config` VALUES ('user_saisie_mission', 'TRUE', '05_Utilisateur', 'boolean', 'config_comment_user_saisie_mission');
INSERT IGNORE INTO `conges_config` VALUES ('user_ch_passwd', 'TRUE', '05_Utilisateur', 'boolean', 'config_comment_user_ch_passwd');

INSERT IGNORE INTO `conges_config` VALUES ('responsable_virtuel', 'FALSE', '06_Responsable', 'boolean', 'config_comment_responsable_virtuel');
INSERT IGNORE INTO `conges_config` VALUES ('resp_affiche_calendrier', 'TRUE', '06_Responsable', 'boolean', 'config_comment_resp_affiche_calendrier');
INSERT IGNORE INTO `conges_config` VALUES ('resp_saisie_mission', 'FALSE', '06_Responsable', 'boolean', 'config_comment_resp_saisie_mission');
INSERT IGNORE INTO `conges_config` VALUES ('resp_ajoute_conges', 'TRUE', '06_Responsable', 'boolean', 'config_comment_resp_ajoute_conges');
INSERT IGNORE INTO `conges_config` VALUES ('gestion_cas_absence_responsable', 'FALSE', '06_Responsable', 'boolean', 'config_comment_gestion_cas_absence_responsable');
INSERT IGNORE INTO `conges_config` VALUES ('print_disable_users',  'FALSE',  '06_Responsable',  'Boolean',  'config_comment_print_disable_users');

INSERT IGNORE INTO `conges_config` VALUES ('admin_see_all', 'FALSE', '07_Administrateur', 'boolean', 'config_comment_admin_see_all');
INSERT IGNORE INTO `conges_config` VALUES ('admin_change_passwd', 'TRUE', '07_Administrateur', 'boolean', 'config_comment_admin_change_passwd');
INSERT IGNORE INTO `conges_config` VALUES ('affiche_bouton_config_pour_admin', 'FALSE', '07_Administrateur', 'boolean', 'config_comment_affiche_bouton_config_pour_admin');
INSERT IGNORE INTO `conges_config` VALUES ('affiche_bouton_config_absence_pour_admin', 'FALSE', '07_Administrateur', 'boolean', 'config_comment_affiche_bouton_config_absence_pour_admin');
INSERT IGNORE INTO `conges_config` VALUES ('affiche_bouton_config_mail_pour_admin', 'FALSE', '07_Administrateur', 'boolean', 'config_comment_affiche_bouton_config_mail_pour_admin');

INSERT IGNORE INTO `conges_config` VALUES ('mail_new_demande_alerte_resp', 'FALSE', '08_Mail', 'boolean', 'config_comment_mail_new_demande_alerte_resp');
INSERT IGNORE INTO `conges_config` VALUES ('mail_valid_conges_alerte_user', 'FALSE', '08_Mail', 'boolean', 'config_comment_mail_valid_conges_alerte_user');
INSERT IGNORE INTO `conges_config` VALUES ('mail_prem_valid_conges_alerte_user', 'FALSE', '08_Mail', 'boolean', 'config_comment_mail_prem_valid_conges_alerte_user');
INSERT IGNORE INTO `conges_config` VALUES ('mail_refus_conges_alerte_user', 'FALSE', '08_Mail', 'boolean', 'config_comment_mail_refus_conges_alerte_user');
INSERT IGNORE INTO `conges_config` VALUES ('mail_annul_conges_alerte_user', 'FALSE', '08_Mail', 'boolean', 'config_comment_mail_annul_conges_alerte_user');
INSERT IGNORE INTO `conges_config` VALUES ('serveur_smtp', '', '08_Mail', 'texte', 'config_comment_serveur_smtp');
INSERT IGNORE INTO `conges_config` VALUES ('where_to_find_user_email', 'dbconges', '08_Mail', 'enum=dbconges/ldap', 'config_comment_where_to_find_user_email');

INSERT IGNORE INTO `conges_config` VALUES ('samedi_travail', 'FALSE', '09_jours ouvrables', 'boolean', 'config_comment_samedi_travail');
INSERT IGNORE INTO `conges_config` VALUES ('dimanche_travail', 'FALSE', '09_jours ouvrables', 'boolean', 'config_comment_dimanche_travail');

INSERT IGNORE INTO `conges_config` VALUES ('gestion_groupes', 'FALSE', '10_Gestion par groupes', 'boolean', 'config_comment_gestion_groupes');
INSERT IGNORE INTO `conges_config` VALUES ('affiche_groupe_in_calendrier', 'FALSE', '10_Gestion par groupes', 'boolean', 'config_comment_affiche_groupe_in_calendrier');
INSERT IGNORE INTO `conges_config` VALUES ('calendrier_select_all_groups', 'FALSE', '10_Gestion par groupes', 'boolean', 'config_comment_calendrier_select_all_groups');
INSERT IGNORE INTO `conges_config` VALUES ('fermeture_par_groupe', 'FALSE', '10_Gestion par groupes', 'boolean', 'config_comment_fermeture_par_groupe');

INSERT IGNORE INTO `conges_config` VALUES ('editions_papier', 'TRUE', '11_Editions papier', 'boolean', 'config_comment_editions_papier');
INSERT IGNORE INTO `conges_config` VALUES ('texte_haut_edition_papier', '- php_conges : édition des congés -', '11_Editions papier', 'texte', 'config_comment_texte_haut_edition_papier');
INSERT IGNORE INTO `conges_config` VALUES ('texte_bas_edition_papier', '- édité par php_conges -', '11_Editions papier', 'texte', 'config_comment_texte_bas_edition_papier');

INSERT IGNORE INTO `conges_config` VALUES ('user_echange_rtt', 'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_user_echange_rtt');
INSERT IGNORE INTO `conges_config` VALUES ('double_validation_conges', 'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_double_validation_conges');
INSERT IGNORE INTO `conges_config` VALUES ('grand_resp_ajout_conges', 'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_grand_resp_ajout_conges');
INSERT IGNORE INTO `conges_config` VALUES ('gestion_conges_exceptionnels', 'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_gestion_conges_exceptionnels');
INSERT IGNORE INTO `conges_config` VALUES ('solde_toujours_positif', 'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_solde_toujours_positif');
INSERT IGNORE INTO `conges_config` VALUES ('autorise_reliquats_exercice', 'TRUE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_autorise_reliquats_exercice');
INSERT IGNORE INTO `conges_config` VALUES ('nb_maxi_jours_reliquats', '0', '12_Fonctionnement de l\'Etablissement', 'texte', 'config_comment_nb_maxi_jours_reliquats');
INSERT IGNORE INTO `conges_config` VALUES ('jour_mois_limite_reliquats', '0', '12_Fonctionnement de l\'Etablissement', 'texte', 'config_comment_jour_mois_limite_reliquats');

INSERT IGNORE INTO `conges_config` VALUES ('affiche_bouton_calcul_nb_jours_pris', 'TRUE', '13_Divers', 'boolean', 'config_comment_affiche_bouton_calcul_nb_jours_pris');
INSERT IGNORE INTO `conges_config` VALUES ('rempli_auto_champ_nb_jours_pris', 'TRUE', '13_Divers', 'boolean', 'config_comment_rempli_auto_champ_nb_jours_pris');
INSERT IGNORE INTO `conges_config` VALUES ('disable_saise_champ_nb_jours_pris', 'FALSE', '13_Divers', 'boolean', 'config_comment_disable_saise_champ_nb_jours_pris');
INSERT IGNORE INTO `conges_config` VALUES ('interdit_saisie_periode_date_passee', 'FALSE', '13_Divers', 'boolean', 'config_comment_interdit_saisie_periode_date_passee');
INSERT IGNORE INTO `conges_config` VALUES ('interdit_modif_demande', 'FALSE', '13_Divers', 'boolean', 'config_comment_interdit_modif_demande');
INSERT IGNORE INTO `conges_config` VALUES ('duree_session', '1800', '13_Divers', 'texte', 'config_comment_duree_session');
INSERT IGNORE INTO `conges_config` VALUES ('export_ical_vcal', 'TRUE', '13_Divers', 'boolean', 'config_comment_export_ical_vcal');
INSERT IGNORE INTO `conges_config` VALUES ('affiche_date_traitement', 'FALSE', '13_Divers', 'boolean', 'config_comment_affiche_date_traitement');
INSERT IGNORE INTO `conges_config` VALUES ('affiche_soldes_calendrier', 'TRUE', '13_Divers', 'boolean', 'config_comment_affiche_soldes_calendrier');
INSERT IGNORE INTO `conges_config` VALUES ('affiche_demandes_dans_calendrier', 'FALSE', '13_Divers', 'boolean', 'config_comment_affiche_demandes_dans_calendrier');
INSERT IGNORE INTO `conges_config` VALUES ('affiche_jours_current_month_calendrier',  'FALSE',  '13_Divers',  'boolean',  'config_comment_affiche_jours_current_month_calendrier');
INSERT IGNORE INTO `conges_config` VALUES ('calcul_auto_jours_feries_france', 'FALSE', '13_Divers', 'boolean', 'config_comment_calcul_auto_jours_feries_france');

INSERT IGNORE INTO `conges_config` VALUES ('stylesheet_file', 'style.css', '14_Presentation', 'texte', 'config_comment_stylesheet_file');
INSERT IGNORE INTO `conges_config` VALUES ('light_grey_bgcolor', '#DEDEDE', '14_Presentation', 'texte', 'config_comment_light_grey_bgcolor');



# --------------------------------------------------------

#
# Structure de la table `conges_type_absence`
#

CREATE TABLE IF NOT EXISTS `conges_type_absence` (
  `ta_id` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `ta_type` enum('conges','absences', 'conges_exceptionnels') NOT NULL default 'conges',
  `ta_libelle` varchar(20) NOT NULL default '',
  `ta_short_libelle` char(3) NOT NULL default '',
  PRIMARY KEY  (`ta_id`)
) DEFAULT CHARSET=latin1;

#
# Contenu de la table `conges_type_absence`
###############################################

INSERT IGNORE INTO `conges_type_absence` VALUES (1, 'conges', 'congés payés', 'cp');
INSERT IGNORE INTO `conges_type_absence` VALUES (2, 'conges', 'rtt', 'rtt');
INSERT IGNORE INTO `conges_type_absence` VALUES (3, 'absences', 'formation', 'fo');
INSERT IGNORE INTO `conges_type_absence` VALUES (4, 'absences', 'misson', 'mi');
INSERT IGNORE INTO `conges_type_absence` VALUES (5, 'absences', 'autre', 'ab');
INSERT IGNORE INTO `conges_type_absence` VALUES (6, 'absences', 'malade', 'mal');
INSERT IGNORE INTO `conges_type_absence` VALUES (11, 'conges_exceptionnels', 'enfant malade', 'enf');
INSERT IGNORE INTO `conges_type_absence` VALUES (12, 'conges_exceptionnels', 'maladie', 'mal');


# --------------------------------------------------------

#
# Structure de la table `conges_solde_user`
#

CREATE TABLE IF NOT EXISTS `conges_solde_user` (
  `su_login` varbinary(99) NOT NULL default '',
  `su_abs_id` int(2) unsigned NOT NULL default '0',
  `su_nb_an` decimal(5,2) NOT NULL default '0.00',
  `su_solde` decimal(5,2) NOT NULL default '0.00',
  `su_reliquat` decimal(4,2) NOT NULL default '0.00',
  PRIMARY KEY  (`su_login`,`su_abs_id`)
) DEFAULT CHARSET=latin1;

#
# Contenu de la table `conges_solde_user`
#

# --------------------------------------------------------

#
# Structure de la table `conges_solde_edition`
#

CREATE TABLE IF NOT EXISTS `conges_solde_edition` (
`se_id_edition` INT( 11 ) NOT NULL ,
`se_id_absence` INT( 2 ) NOT NULL ,
`se_solde` DECIMAL( 4, 2 ) NOT NULL
) DEFAULT CHARSET=latin1;

# --------------------------------------------------------

#
# Structure de la table `conges_mail`
#

CREATE TABLE IF NOT EXISTS `conges_mail` (
`mail_nom` VARCHAR( 100 ) NOT NULL ,
`mail_subject` TEXT NULL ,
`mail_body` TEXT NULL ,
UNIQUE KEY `mail_nom` (`mail_nom`)
) DEFAULT CHARSET=latin1;

#
# Contenu de la table `conges_mail`
#

INSERT IGNORE INTO `conges_mail` (`mail_nom`, `mail_subject`, `mail_body`) VALUES ('mail_new_demande', 'APPLI CONGES - Demande de congés', ' __SENDER_NAME__ a solicité une demande de congés dans l''application de gestion des congés.\r\n\r\nMerci de consulter votre application php_conges : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique.');
INSERT IGNORE INTO `conges_mail` (`mail_nom`, `mail_subject`, `mail_body`) VALUES ('mail_new_demande_resp_absent', 'APPLI CONGES - Demande de congés', ' __SENDER_NAME__ a solicité une demande de congés dans l''application de gestion des congés.\r\n\r\nEn votre absence, cette demande a été transférée à votre (vos) propre(s) responsable(s)./\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique.');
INSERT IGNORE INTO `conges_mail` (`mail_nom`, `mail_subject`, `mail_body`) VALUES ('mail_valid_conges', 'APPLI CONGES - Congés accepté', ' __SENDER_NAME__ a enregistré/accepté un congés pour vous dans l''application de gestion des congés.\r\n\r\nMerci de consulter votre application php_conges : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique.');
INSERT IGNORE INTO `conges_mail` (`mail_nom`, `mail_subject`, `mail_body`) VALUES ('mail_refus_conges', 'APPLI CONGES - Congés refusé', ' __SENDER_NAME__ a refusé une demande de congés pour vous dans l''application de gestion des congés.\r\n\r\nMerci de consulter votre application php_conges : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique.');
INSERT IGNORE INTO `conges_mail` (`mail_nom`, `mail_subject`, `mail_body`) VALUES ('mail_annul_conges', 'APPLI CONGES - Congés annulé', ' __SENDER_NAME__ a annulé un de vos congés dans l''application de gestion des congés.\r\n\r\nMerci de consulter votre application php_conges : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique.');
INSERT IGNORE INTO `conges_mail` (`mail_nom`, `mail_subject`, `mail_body`) VALUES ('mail_prem_valid_conges', 'APPLI CONGES - Congés validé', ' __SENDER_NAME__ a validé (première validation) un congés pour vous dans l''application de gestion des congés.\r\n\Il doit maintenant être accepté en deuxième validation.\r\n\r\nMerci de consulter votre application php_conges : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique.');
# --------------------------------------------------------

#
# Structure de la table `conges_logs`
#

CREATE TABLE IF NOT EXISTS `conges_logs` (
   `log_id` integer not null auto_increment,
   `log_p_num` int(5) unsigned NOT NULL,
   `log_user_login_par` varbinary(99) NOT NULL default '',
   `log_user_login_pour` varbinary(99) NOT NULL default '',
   `log_etat` varchar(16) NOT NULL default '',
   `log_comment` TEXT NULL,
   `log_date` TIMESTAMP NOT NULL,
   PRIMARY KEY  (`log_id`)
) DEFAULT CHARSET=latin1;


# --------------------------------------------------------

#
# Structure de la table `conges_jours_fermeture`
#

 CREATE TABLE IF NOT EXISTS `conges_jours_fermeture` (
	`jf_id` INT( 5 ) NOT NULL ,
	`jf_gid` INT( 11 ) NOT NULL DEFAULT '0',
	`jf_date` DATE NOT NULL
) DEFAULT CHARSET=latin1;

# --------------------------------------------------------

#
# Structure de la table `conges_appli`
#

CREATE TABLE IF NOT EXISTS `conges_appli` (
  `appli_variable` varchar(100) binary NOT NULL default '',
  `appli_valeur` varchar(200) binary NOT NULL default '',
  PRIMARY KEY  (`appli_variable`)
) DEFAULT CHARSET=latin1;

#
# Contenu de la table `conges_appli`
#

INSERT IGNORE INTO `conges_appli` VALUES ('num_exercice', '1');
INSERT IGNORE INTO `conges_appli` VALUES ('date_limite_reliquats', '0');
INSERT IGNORE INTO `conges_appli` VALUES ('semaine_bgcolor', '#FFFFFF');
INSERT IGNORE INTO `conges_appli` VALUES ('week_end_bgcolor', '#BFBFBF');
INSERT IGNORE INTO `conges_appli` VALUES ('temps_partiel_bgcolor', '#FFFFC4');
INSERT IGNORE INTO `conges_appli` VALUES ('conges_bgcolor', '#DEDEDE');
INSERT IGNORE INTO `conges_appli` VALUES ('demande_conges_bgcolor', '#E7C4C4');
INSERT IGNORE INTO `conges_appli` VALUES ('absence_autre_bgcolor', '#D3FFB6');
INSERT IGNORE INTO `conges_appli` VALUES ('fermeture_bgcolor', '#7B9DE6');

# --------------------------------------------------------
