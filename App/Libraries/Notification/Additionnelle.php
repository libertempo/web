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

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " solicite une demande d'ajout d'heure additionnelle pour le ... du ... au ... soit ... heure(s). Vous devez traiter cette demande";

        return $return;
    }

    private function getNotificationEmployePremierValidation() {

        $return['sujet'] = "Première validation d'heure additionnelle";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['login']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['login']);
        $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a validé votre demande d'heure additionnelle du ... . Il doit maintenant être traité en deuxième validation.";

        return $return;
    }

    private function getNotificationValidationFinale() {

        $return['sujet'] = "Demande d'heure additionnelle validé";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['login']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['login']);
        $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);


        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a accepté la demande d'heure additionnelle du ... de ... à ... .";

        return $return;
    }

    private function getNotificationRefus() {

        $return['sujet'] = "Demande d'heure additionnelle refusé";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['login']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['login']);
        $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a refusé la demande d'heure additionnelle du ... de ... à ... .";

        return $return;
    }

    private function getNotificationAnnulation() {

        $return['sujet'] = "Demande d'heure additionnelle annulée";
        $return['expediteur'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($this->data['login']);
        $responsables = \App\ProtoControllers\Responsable::getResponsablesUtilisateur($this->data['login']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($this->data['login']);
        foreach ($responsables as $responsable) {
            $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($responsable);
        }

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a annulée la demande d'heure additionnelle du ... de ... à ... commentaire_refus.";

        return $return;
    }
}
