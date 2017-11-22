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
                WHERE id_heure =' . (int) $id;

        $data = $sql->query($req)->fetch_array();
        
        $data['jour']   = date('d/m/Y', $data['debut']);
        $data['debut']  = date('H\:i', $data['debut']);
        $data['fin']    = date('H\:i', $data['fin']);
        $data['duree']    = \App\Helpers\Formatter::Timestamp2Duree($data['duree']);

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    protected function getContenuDemande($data) {

        $return['sujet'] = "[CONGES] Demande d'heure de repos";
        $return['expediteur']['mail'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($data['login']);
        $responsables = \App\ProtoControllers\Responsable::getResponsablesUtilisateur($data['login']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($data['login'])[$data['login']];
        $return['expediteur']['nom'] = $infoUser['u_nom']." ".$infoUser['u_prenom'];

        foreach ($responsables as $responsable) {
            $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($responsable);
        }

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " solicite une demande d'ajout d'heure de repos pour le ". $data['jour'] ." de ". $data['debut'] ." à ". $data['fin'] ." soit ". $data['duree'] ." heure(s). Vous devez traiter cette demande";

        $return['config'] = 'mail_new_demande_alerte_resp';
        return $return;
    }

    /**
     * {@inheritDoc}
     */
    protected function getContenuEmployePremierValidation($data) {

        $return['sujet'] = "[CONGES] Première validation d'heure de repos";
        $return['expediteur']['mail'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['userlogin']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['userlogin'])[$_SESSION['userlogin']];
        $return['expediteur']['nom'] = $infoUser['u_nom']." ".$infoUser['u_prenom'];

        $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($data['login']);

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a validé(e) votre demande d'heure de repos du  ". $data['jour'] ." de ". $data['debut'] ." à ". $data['fin'] .". Il doit maintenant être traité en deuxième validation.";

        $return['config'] = 'mail_prem_valid_conges_alerte_user';
        return $return;
    }
    
    /**
     * {@inheritDoc}
     */
    protected function getContenuValidationFinale($data) {

        $return['sujet'] = "[CONGES] Demande d'heure de repos validée";
        $return['expediteur']['mail'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['userlogin']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['userlogin'])[$_SESSION['userlogin']];
        $return['expediteur']['nom'] = $infoUser['u_nom']." ".$infoUser['u_prenom'];
        $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($data['login']);

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a accepté la demande d'heure de repos du ". $data['jour'] ." de ". $data['debut'] ." à ". $data['fin'] .".";

        $return['config'] = 'mail_valid_conges_alerte_user';
        return $return;
    }
    
    /**
     * {@inheritDoc}
     */
    protected function getContenuRefus($data) {

        $return['sujet'] = "[CONGES] Demande d'heure de repos refusée";
        $return['expediteur']['mail'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['userlogin']);
        $infoResp = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['userlogin'])[$_SESSION['userlogin']];
        $return['expediteur']['nom'] = $infoResp['u_nom']." ".$infoResp['u_prenom'];
        $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($data['login']);

        $return['message'] = $infoResp['u_prenom'] . " " . $infoResp['u_nom'] . " a refusé(e) votre demande d'heure de repos du ". $data['jour'] ." de ". $data['debut'] ." à ". $data['fin'] .".";

        if (!is_null($data['comment_refus'])){
            $return['message'] .= "\nCommentaire : " . $data['comment_refus'];
        }
        
        $return['config'] = 'mail_valid_conges_alerte_user';
        return $return;
    }
    
    /**
     * {@inheritDoc}
     */
    protected function getContenuAnnulation($data) {

        $return['sujet'] = "[CONGES] Demande d'heure de repos annulée";
        $return['expediteur']['mail'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($data['login']);
        $responsables = \App\ProtoControllers\Responsable::getResponsablesUtilisateur($data['login']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($data['login'])[$data['login']];
        $return['expediteur']['nom'] = $infoUser['u_nom']." ".$infoUser['u_prenom'];
        foreach ($responsables as $responsable) {
            $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($responsable);
        }

        $return['message'] = $infoUser['u_prenom'] . " " . $infoUser['u_nom'] . " a annulé(e) la demande d'heure de repos du  ". $data['jour'] ." de ". $data['debut'] ." à ". $data['fin'] .".";

        $return['config'] = 'mail_supp_demande_alerte_resp';
        return $return;
    }
    
    /**
     * {@inheritDoc}
     */
    protected function getContenuGrandResponsablePremiereValidation($data) {
        $return['sujet'] = "[CONGES] Demande d'heure de repos";
        $return['expediteur']['mail'] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($_SESSION['userlogin']);
        $grandResponsables = \App\ProtoControllers\Responsable::getLoginGrandResponsableUtilisateur($data['login']);
        $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($data['login'])[$data['login']];
        $infoResp = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($_SESSION['userlogin'])[$_SESSION['userlogin']];
        $return['expediteur']['nom'] = $infoResp['u_nom']." ".$infoResp['u_prenom'];

        foreach ($grandResponsables as $responsable) {
            $return['destinataire'][] = \App\ProtoControllers\Utilisateur::getEmailUtilisateur($responsable);
        }

    $return['message'] = $infoResp['u_prenom'] . " " . $infoResp['u_nom'] . " a validé(e) la demande d'ajout d'heure de repos de ".$infoUser['u_prenom']." ".$infoUser['u_nom']." pour le ". $data['jour'] ." de ". $data['debut'] ." à ". $data['fin'] ." soit ". $data['duree'] ." heure(s). Vous devez traiter cette demande";

        $return['config'] = 'mail_new_demande_alerte_resp';
        return $return;
    }
}
