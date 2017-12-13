<?php

namespace App\Libraries;

/**
 * Objet de configuration de l'application
 *
 * Pour le moment, ne gère que l'utilisation courante, pas la modification des données via la page de configuration
 */
class Configuration {

    private $data;

    public function __construct(\includes\SQL $sql) {
        
        $this->loadData($sql);
    }

    /**
     * Charge les données de configuration
     */
    private function loadData(\includes\SQL $sql) {
        $req = 'SELECT * FROM conges_config ORDER BY conf_groupe';
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $groupe = $data['conf_groupe'];
            $nom = $data['conf_nom'];
            $this->data[$groupe][$nom] = [
                'valeur' => $data['conf_valeur'],
                'type' => $data['conf_type'],
            ];
        }
    }

    /**
     * Retourne la version de la base de données
     * 
     * @return string
     */
    public function getInstalledVersion() {
        return $this->getGroupeLibertempoValeur('installed_version');
    }

    /**
     * Retourne la langue
     * @todo a supprimer. non utilisé
     * @return string
     */
    public function getLang() {
        return $this->getGroupeLibertempoValeur('lang');
    }

    /**
     * Retourne une valeur du groupe de Libertempo par son nom
     *
     * @param string $nom
     *
     * @return mixed
     */
    private function getGroupeLibertempoValeur($nom) {
        return $this->getValeur($nom, '00_libertempo');
    }

    /**
     * Retourne l'url racine du site
     * 
     * @return string
     */
    public function getUrlAccueil() {
        return $this->getGroupeServeurValeur('URL_ACCUEIL_CONGES');
    }

    /**
     * Retourne une valeur du groupe de Libertempo par son nom
     *
     * @param string $nom
     *
     * @return mixed
     */
    private function getGroupeServeurValeur($nom) {
        return $this->getValeur($nom, '01_Serveur Web');
    }

    /**
     * Autorise la saisie d'une demande par l'employé
     * 
     * @return boolean
     */
    public function canUserSaisieDemande() {
        return $this->getGroupeUtilisateurValeur('user_saisie_demande');
    }
    
    /**
     * Autorise la saisie d'une demande de mission par l'employé
     * @todo a supprimer au profit de canUserSaisieDemande()
     * 
     * @return boolean
     */
    public function canUserSaisieMission() {
        return $this->getGroupeUtilisateurValeur('user_saisie_mission');
    }

    /**
     * Autorise la modification du mot de passe par l'employé
     * le mot de passe n'est modifiable que si 
     * authentification locale
     * 
     * @return boolean
     */
    public function canUserChangePassword() {
        if ($this->getHowToConnectUser() != 'dbconges') {
            return false;
        }
        return $this->getGroupeUtilisateurValeur('user_ch_passwd');
    }

    public function canUserSaisieNombreJours() {
        return !$this->getGroupeUtilisateurValeur('disable_saise_champ_nb_jours_pris');
    }

    /**
     * Controle si l'utilisateur peut saisir une demande dans le passé
     * 
     * @return boolean
     */
    public function canUserSaisieDemandePasse() {
        return !$this->getGroupeUtilisateurValeur('interdit_saisie_periode_date_passee');
    }

    /**
     * Controle si l'utilisateur peut modifier une demande
     * 
     * @return boolean
     */
    public function canUserModifieDemande() {
        return !$this->getGroupeUtilisateurValeur('interdit_modif_demande');
    }

    /**
     * Retourne une valeur du groupe utilisateur par son nom
     *
     * @param string $nom
     *
     * @return mixed
     */
    private function getGroupeUtilisateurValeur($nom) {
        return $this->getValeur($nom, '05_Utilisateur');
    }

    /**
     * Permet aux responsables de saisir une mission pour leurs employés
     * 
     * @return boolean
     */
    public function canResponsableSaisieMission() {
        return $this->getGroupeResponsableValeur('resp_saisie_mission');
    }

    /**
     * Permet aux responsables d'ajouter des jours de congés
     * 
     * @return boolean
     */
    public function canResponsableAjouteConges() {
        return $this->getGroupeResponsableValeur('resp_ajoute_conges');
    }

    /**
     * Gestion de la délégation de traitement en cas d'absence
     * 
     * @return boolean
     */
    public function isGestionResponsableAbsent() {
        return $this->getGroupeResponsableValeur('gestion_cas_absence_responsable');
    }

    /**
     * Gestion des utilisateur désactivé
     * 
     * @return boolean
     */
    public function isUtilisateurDesactiveVisible() {
        return $this->getGroupeResponsableValeur('print_disable_users');
    }

    public function canResponsablesAssociatePlanning()
    {
        return $this->getGroupeResponsableValeur('resp_association_planning');
    }
    /**
     * Retourne une valeur du groupe responsable par son nom
     *
     * @param string $nom
     *
     * @return mixed
     */
    private function getGroupeResponsableValeur($nom) {
        return $this->getValeur($nom, '06_Responsable');
    }

    /**
     * Permet aux responsables avec les droits admin de voir tous les utilisateurs
     * 
     * @return boolean
     */
    public function canAdminSeeAll() {
        return $this->getGroupeAdministrateurValeur('admin_see_all');
    }

    /**
     * Permet aux admin de changer les mot de passe
     * le mot de passe n'est modifiable que si 
     * authentification locale
     * 
     * @return boolean
     */
    public function canAdminChangePassword() {
        if ($this->getHowToConnectUser() !='dbconges') {
            return false;
        }
        return $this->getGroupeAdministrateurValeur('admin_change_passwd');
    }

    /**
     * Permet aux admin d'accéder à la configuration globale
     * 
     * @return boolean
     */
    public function canAdminAccessConfig() {
        return $this->getGroupeAdministrateurValeur('affiche_bouton_config_pour_admin');
    }

    /**
     * Permet aux admin d'accéder à la configuration des types de congés
     * 
     * @return boolean
     */
    public function canAdminConfigTypesConges() {
        return $this->getGroupeAdministrateurValeur('affiche_bouton_config_absence_pour_admin');
    }

    public function canAdminConfigMail() {
        return $this->getGroupeAdministrateurValeur('affiche_bouton_config_mail_pour_admin');
    }

    /**
     * Retourne une valeur du groupe administrateur par son nom
     *
     * @param string $nom
     *
     * @return mixed
     */
    private function getGroupeAdministrateurValeur($nom) {
        return $this->getValeur($nom, '07_Administrateur');
    }

    public function isSendMailDemandeResponsable() {
        return $this->getGroupeMailValeur('mail_new_demande_alerte_resp');
    }

    public function isSendMailValidationUtilisateur() {
        return $this->getGroupeMailValeur('mail_valid_conges_alerte_user');
    }

    public function isSendMailPremierValidationUtilisateur() {
        return $this->getGroupeMailValeur('mail_prem_valid_conges_alerte_user');
    }

    public function isSendMailRefusUtilisateur() {
        return $this->getGroupeMailValeur('mail_refus_conges_alerte_user');
    }

    public function isSendMailAnnulationCongesUtilisateur() {
        return$this->getGroupeMailValeur('mail_annul_conges_alerte_user');
    }

    public function isSendMailModificationDemandeResponsable() {
        return $this->getGroupeMailValeur('mail_modif_demande_alerte_resp');
    }
    
    public function isSendMailSupprimeDemandeResponsable() {
        return $this->getGroupeMailValeur('mail_supp_demande_alerte_resp');
    }

    public function getMailFromLdap() {
        return $this->getGroupeMailValeur('where_to_find_user_email') == 'ldap';
    }

    /**
     * Retourne une valeur du groupe mail par son nom
     *
     * @param string $nom
     *
     * @return mixed
     */
    private function getGroupeMailValeur($nom) {
        return $this->getValeur($nom, '08_Mail');
    }

    public function isSamediOuvrable() {
        return $this->getGroupeJoursOuvrablesValeur('samedi_travail');
    }
    
    public function isDimancheOuvrable() {
        return $this->getGroupeJoursOuvrablesValeur('dimanche_travail');
    }

    /**
     * Retourne une valeur du groupe des jours ouvrables par son nom
     *
     * @param string $nom
     *
     * @return mixed
     */
    private function getGroupeJoursOuvrablesValeur($nom) {
        return $this->getValeur($nom, '09_jours ouvrables');
    }

    public function canFermetureParGroupe()
    {
        return $this->getGroupeGestionGroupesValeur('fermeture_par_groupe');
    }

    /**
     * Retourne une valeur du groupe de gestion des groupes par son nom
     *
     * @param string $nom
     *
     * @return mixed
     */
    private function getGroupeGestionGroupesValeur($nom) {
        return $this->getValeur($nom, '10_Gestion par groupes');
    }

    public function canEditPapier()
    {
        return $this->getGroupeEditionPapierValeur('editions_papier');
    }

    public function getTextHaut()
    {
        return $this->getGroupeEditionPapierValeur('texte_haut_edition_papier');
    }
    
    public function getTextBas()
    {
        return $this->getGroupeEditionPapierValeur('texte_bas_edition_papier');
    }
    
    /**
     * Retourne une valeur du groupe d'édition papier par son nom
     *
     * @param string $nom
     *
     * @return mixed
     */
    private function getGroupeEditionPapierValeur($nom) {
        return $this->getValeur($nom, '11_Editions papier');
    }

    public function canUserEchangeRTT()
    {
        return $this->getGroupeFonctionnementEtablissementValeur('user_echange_rtt');
    }

    public function isDoubleValidationActive()
    {
        return $this->getGroupeFonctionnementEtablissementValeur('double_validation_conges');
    }
    
    public function canGrandResponsableAjouteConge()
    {
        return $this->getGroupeFonctionnementEtablissementValeur('grand_resp_ajout_conges');
    }

    public function isCongesExceptionnelsActive()
    {
        return $this->getGroupeFonctionnementEtablissementValeur('gestion_conges_exceptionnels');
    }
    
    public function canSoldeNegatif()
    {
        return !$this->getGroupeFonctionnementEtablissementValeur('solde_toujours_positif');
    }
    
    public function isReliquatsAutorise()
    {
        return $this->getGroupeFonctionnementEtablissementValeur('autorise_reliquats_exercice');
    }
    
    public function getReliquatsMax()
    {
        return $this->getGroupeFonctionnementEtablissementValeur('nb_maxi_jours_reliquats');
    }
    
    public function getDateLimiteReliquats()
    {
        return $this->getGroupeFonctionnementEtablissementValeur('jour_mois_limite_reliquats');
    }

    public function isHeuresAutorise()
    {
        return $this->getGroupeFonctionnementEtablissementValeur('gestion_heures');
    }
    /**
     * Retourne une valeur du groupe fonctionnement de l'établissement par son nom
     *
     * @param string $nom
     *
     * @return mixed
     */
    private function getGroupeFonctionnementEtablissementValeur($nom)
    {
        return $this->getValeur($nom, '12_Fonctionnement de l\'Etablissement');
    }

    public function canAfficheDateTraitement()
    {
        return $this->getGroupeDiversValeur('affiche_date_traitement');
    }

    public function getDureeSession()
    {
        return $this->getGroupeDiversValeur('duree_session');
    }
    /**
     * Retourne une valeur du groupe divers par son nom
     *
     * @param string $nom
     *
     * @return mixed
     */
    private function getGroupeDiversValeur($nom) {
        return $this->getValeur($nom, '13_Divers');
    }

    public function isIcalActive() {
        return $this->getGroupeIcalValeur('export_ical');
    }

    public function getIcalSalt()
    {
        return $this->getGroupeIcalValeur('export_ical_salt');
    }
    /**
     * Retourne une valeur du groupe ical par son nom
     *
     * @param string $nom
     *
     * @return mixed
     */
    private function getGroupeIcalValeur($nom) {
        return $this->getValeur($nom, '15_ical');
    }

    public function getHowToConnectUser() {
        return $this->getGroupeAuthentificationValeur('how_to_connect_user');
    }

    public function isUsersExportFromLdap() {
        return $this->getGroupeAuthentificationValeur('export_users_from_ldap');
    }

    /**
     * Retourne une valeur du groupe d'authentification par son nom
     *
     * @param string $nom
     *
     * @return mixed
     */
    private function getGroupeAuthentificationValeur($nom) {
        return $this->getValeur($nom, '04_Authentification');
    }

    /**
     * Retourne la valeur d'une configuration en fonction de son groupe et de son nom
     *
     * @param string $nom
     * @param string $groupe
     *
     * @return mixed
     * @throws \Exception Si la configuration n'existe pas
     */
    private function getValeur($nom, $groupe) {
        if (!isset($this->data[$groupe]) || !isset($this->data[$groupe][$nom])) {
            throw new \Exception('Donnée de configuration inexistante');
        }
        $config = $this->data[$groupe][$nom];

        if ('boolean' === $config['type'] ) {
            return 'TRUE' === $config['valeur'];
        }

        return $config['valeur'];
    }

}
