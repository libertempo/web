<?php

namespace App\Libraries\Notification;

/**
 * Objet de gestion des notifications
 * 
 * 
 */
Class Additionnelle extends \App\Libraries\ANotification {


    /**
     * récupère les données de l'évenemment
     * 
     * @todo déplacer la requete vers le protocontroller
     * @param int $id
     * 
     * @return array
     */
    protected function getData($id) {

        if (empty($id)) {
            return [];
        }
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM heure_additionnelle
                WHERE id_heure =' . $id;

        $data = $sql->query($req)->fetch_array();
        
        $data['jour']   = date('d/m/Y', $data['debut']);
        $data['debut']  = date('H\:i', $data['debut']);
        $data['fin']    = date('H\:i', $data['fin']);
        $data['duree']    = \App\Helpers\Formatter::Timestamp2Duree($data['duree']);

        return $data;
    }

    /**
     * notification nouvelle demande
     * 
     * @param array $data
     * @return array
     */
    protected function getNotificationDemande() {

        $return['sujet'] = "Demande d'heure additionnelle";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);
        $responsables = \App\ProtoControllers\Responsable::getResponsablesUtilisateur($this->data['login']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($this->data['login']);
        foreach ($responsables as $responsable) {
            $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($responsable);
        }

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " solicite une demande d'ajout d'heure additionnelle pour le ". $this->data['jour'] ." de ". $this->data['debut'] ." à ". $this->data['fin'] ." soit ". $this->data['duree'] ." heure(s). Vous devez traiter cette demande";

        $return['config'] = 'mail_new_demande_alerte_resp';
        return $return;
    }

    protected function getNotificationEmployePremierValidation() {

        $return['sujet'] = "Première validation d'heure additionnelle";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['userlogin']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['userlogin']);
        $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a validé(e) votre demande d'heure additionnelle du  ". $this->data['jour'] ." de ". $this->data['debut'] ." à ". $this->data['fin'] .". Il doit maintenant être traité en deuxième validation.";

        $return['config'] = 'mail_prem_valid_conges_alerte_user';
        return $return;
    }

    protected function getNotificationValidationFinale() {

        $return['sujet'] = "Demande d'heure additionnelle validée";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['userlogin']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['userlogin']);
        $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);


        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a accepté la demande d'heure additionnelle du ". $this->data['jour'] ." de ". $this->data['debut'] ." à ". $this->data['fin'] .".";

        $return['config'] = 'mail_valid_conges_alerte_user';
        return $return;
    }

    protected function getNotificationRefus() {

        $return['sujet'] = "Demande d'heure additionnelle refusée";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['userlogin']);
        $infoResp = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['userlogin']);
        $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);

        $return['message'] = $infoResp['u_prenom'] . " " . $infoResp['u_nom'] . " a refusé(e) votre demande d'heure additionnelle du ". $this->data['jour'] ." de ". $this->data['debut'] ." à ". $this->data['fin'] .".";

        if(!is_null($infoResp['comment_refus'])){
            $return['message'] .= "\nCommentaire : " . $infoResp['comment_refus'];
        }
        
        $return['config'] = 'mail_valid_conges_alerte_user';
        return $return;
    }

    protected function getNotificationAnnulation() {

        $return['sujet'] = "Demande d'heure additionnelle annulée";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);
        $responsables = \App\ProtoControllers\Responsable::getResponsablesUtilisateur($this->data['login']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($this->data['login']);
        foreach ($responsables as $responsable) {
            $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($responsable);
        }

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a annulé(e) la demande d'heure additionnelle du  ". $this->data['jour'] ." de ". $this->data['debut'] ." à ". $this->data['fin'] .".";

        $return['config'] = 'mail_supp_demande_alerte_resp';
        return $return;
    }
    
    protected function getNotificationGrandResponsablePremiereValidation() {
        $return['sujet'] = "Demande d'heure additionnelle";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['userlogin']);
        $grandResponsables = \App\ProtoControllers\Responsable::getLoginGrandResponsableUtilisateur($this->data['login']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($this->data['login']);
        $infoResp = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['userlogin']);
        foreach ($responsables as $responsable) {
            $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($responsable);
        }

    $return['message'] = $infoResp['u_prenom'] . " " . $infoResp['u_nom'] . " a validé(e) la demande d'ajout d'heure additionnelle de ".$infoUser['u_prenom']." ".$infoUser['u_nom']." pour le ". $this->data['jour'] ." de ". $this->data['debut'] ." à ". $this->data['fin'] ." soit ". $this->data['duree'] ." heure(s). Vous devez traiter cette demande";

        $return['config'] = 'mail_new_demande_alerte_resp';
        return $return;
    }
}
