<?php

namespace App\Libraries\Notification;

/**
 * Objet de gestion des notifications
 * 
 * 
 */
Class Repos extends \App\Libraries\ANotification {

    /**
     * récupère les données de l'évenemment
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
                FROM heure_repos
                WHERE id_heure =' . $id;

        $data = $sql->query($req)->fetch_array();


        return $data;
    }

    /**
     * notification nouvelle demande
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

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a solicité une demande d'heure de repos dans l'application de gestion des congés.";

        return $return;
    }

    private function getNotificationEmployePremierValidation() {

        $return['sujet'] = "Première validation d'heure de repos";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['userlogin']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['userlogin']);
        $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a validé (première validation) une demande d'heure de repos pour vous dans l'application de gestion des congés. Il doit maintenant être accepté en deuxième validation.";

        return $return;
    }

    private function getNotificationValidationFinale() {

        $return['sujet'] = "Demande d'heure de repos validé";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['userlogin']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['userlogin']);
        $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);


        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a accepté une demande d'heure de repos pour vous dans l'application de gestion des congés.";

        return $return;
    }

    private function getNotificationRefus() {

        $return['sujet'] = "Demande d'heure de repos refusé";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['userlogin']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['userlogin']);
        $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a refusé une demande d'heure de repos pour vous dans l'application de gestion des congés.";

        return $return;
    }

    private function getNotificationAnnulation() {

        $return['sujet'] = "Demande d'heure de repos annulée";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);
        $responsables = \App\ProtoControllers\Responsable::getResponsablesUtilisateur($this->data['login']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($this->data['login']);
        foreach ($responsables as $responsable) {
            $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($responsable);
        }

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a annulée une demande d'heure de repos.";

        return $return;
    }
}
