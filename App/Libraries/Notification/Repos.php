<?php

namespace App\Libraries\Notification;

/**
 * Objet de gestion des notifications
 * 
 * 
 */
Class Repos extends \App\Libraries\ANotification {


    /**
     * {@inheritDoc}
     */
    protected function getData($id) {

        if (empty($id)) {
            return [];
        }
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM heure_repos
                WHERE id_heure =' . $id;

        $data = $sql->query($req)->fetch_array();
        
        $data['jour']   = date('d/m/Y', $data['debut']);
        $data['debut']  = date('H\:i', $data['debut']);
        $data['fin']    = date('H\:i', $data['fin']);
        $data['duree']    = \App\Helpers\Formatter::Timestamp2Duree($data['duree']);

        return $data;
    }

    /**
     * notification d'une nouvelle demande d'heures de repos
     * au responsable du demandeur
     * 
     * @param array $data
     * @return array
     */
    protected function getNotificationDemande() {

        $return['sujet'] = "Demande d'heure de repos";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);
        $responsables = \App\ProtoControllers\Responsable::getResponsablesUtilisateur($this->data['login']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($this->data['login']);
        foreach ($responsables as $responsable) {
            $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($responsable);
        }

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " solicite une demande d'ajout d'heure de repos pour le ". $this->data['jour'] ." de ". $this->data['debut'] ." à ". $this->data['fin'] ." soit ". $this->data['duree'] ." heure(s). Vous devez traiter cette demande";

        $return['config'] = 'mail_new_demande_alerte_resp';
        return $return;
    }

    /**
     * notification d'une première validation 
     * au demandeur d'heures de repos
     * 
     * @param array $data
     * @return array
     */
    protected function getNotificationEmployePremierValidation() {

        $return['sujet'] = "Première validation d'heure de repos";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['userlogin']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['userlogin']);
        $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a validé(e) votre demande d'heure de repos du  ". $this->data['jour'] ." de ". $this->data['debut'] ." à ". $this->data['fin'] .". Il doit maintenant être traité en deuxième validation.";

        $return['config'] = 'mail_prem_valid_conges_alerte_user';
        return $return;
    }
    
    /**
     * notification d'une validation finale
     * au demandeur d'heures de repos
     * 
     * @param array $data
     * @return array
     */
    protected function getNotificationValidationFinale() {

        $return['sujet'] = "Demande d'heure de repos validée";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['userlogin']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['userlogin']);
        $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);


        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a accepté la demande d'heure de repos du ". $this->data['jour'] ." de ". $this->data['debut'] ." à ". $this->data['fin'] .".";

        $return['config'] = 'mail_valid_conges_alerte_user';
        return $return;
    }
    
    /**
     * notification d'un refus
     * au demandeur d'heures de refus
     * 
     * @param array $data
     * @return array
     */
    protected function getNotificationRefus() {

        $return['sujet'] = "Demande d'heure de repos refusée";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['userlogin']);
        $infoResp = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['userlogin']);
        $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);

        $return['message'] = $infoResp['u_prenom'] . " " . $infoResp['u_nom'] . " a refusé(e) votre demande d'heure de repos du ". $this->data['jour'] ." de ". $this->data['debut'] ." à ". $this->data['fin'] .".";

        if(!is_null($this->data['comment_refus'])){
            $return['message'] .= "\nCommentaire : " . $this->data['comment_refus'];
        }
        
        $return['config'] = 'mail_valid_conges_alerte_user';
        return $return;
    }
    
    /**
     * notification d'une annulation par le demandeur
     * à son responsable
     * 
     * @param array $data
     * @return array
     */
    protected function getNotificationAnnulation() {

        $return['sujet'] = "Demande d'heure de repos annulée";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);
        $responsables = \App\ProtoControllers\Responsable::getResponsablesUtilisateur($this->data['login']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($this->data['login']);
        foreach ($responsables as $responsable) {
            $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($responsable);
        }

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a annulé(e) la demande d'heure de repos du  ". $this->data['jour'] ." de ". $this->data['debut'] ." à ". $this->data['fin'] .".";

        $return['config'] = 'mail_supp_demande_alerte_resp';
        return $return;
    }
    
    /**
     * notification d'une première validation
     * au grand responsable du demandeur d'heures de repos
     * 
     * @param array $data
     * @return array
     */
    protected function getNotificationGrandResponsablePremiereValidation() {
        $return['sujet'] = "Demande d'heure de repos";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['userlogin']);
        $grandResponsables = \App\ProtoControllers\Responsable::getLoginGrandResponsableUtilisateur($this->data['login']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($this->data['login']);
        $infoResp = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['userlogin']);
        foreach ($responsables as $responsable) {
            $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($responsable);
        }

    $return['message'] = $infoResp['u_prenom'] . " " . $infoResp['u_nom'] . " a validé(e) la demande d'ajout d'heure de repos de ".$infoUser['u_prenom']." ".$infoUser['u_nom']." pour le ". $this->data['jour'] ." de ". $this->data['debut'] ." à ". $this->data['fin'] ." soit ". $this->data['duree'] ." heure(s). Vous devez traiter cette demande";

        $return['config'] = 'mail_new_demande_alerte_resp';
        return $return;
    }
}
