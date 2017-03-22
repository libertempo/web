<?php
namespace App\ProtoControllers\Responsable\Traitement;

use \App\Models\AHeure;

/**
 * ProtoContrôleur de validation d'heures additionnelles
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina <wouldsmina@tuxfamily.org>
 */
class Additionnelle extends \App\ProtoControllers\Responsable\ATraitement
{
    /**
     * Traite les demandes
     *
     * @param array  $put
     * @param string $resp
     * @param string $notice
     * @param array $errorLst
     *
     * @return int
     */
    public function put(array $put, $resp, &$notice, array &$errorLst)
    {
        $return = '1';
        $infoDemandes = $this->getInfoDemandes(array_keys($put['demande']));

        foreach ($put['demande'] as $id_heure => $statut) {
            if (\App\ProtoControllers\Responsable::isRespDeUtilisateur($resp, $infoDemandes[$id_heure]['login'])) {
                $return = $this->putResponsable($infoDemandes[$id_heure], $statut, $put, $errorLst);
            } elseif (\App\ProtoControllers\Responsable::isGrandRespDeGroupe($resp, \App\ProtoControllers\Utilisateur::getGroupesId($infoDemandes[$id_heure]['login']))) {
                $return = $this->putGrandResponsable($infoDemandes[$id_heure], $statut, $put, $errorLst);
            } else {
                $errorLst[] = _('erreur_pas_responsable_de') . ' ' . $infoDemandes[$id_heure]['login'];
                $return = NIL_INT;
            }
        }
        $notice = _('traitement_effectue');
        return $return;
    }

    /**
     * {@inheritDoc}
     */
    protected function putResponsable(array $infoDemande, $statut, array $put, array &$errors)
    {
        $localError = [];
        $return = NIL_INT;
        $id_heure = $infoDemande['id_heure'];
        if ($this->isDemandeTraitable($infoDemande['statut'])) { // demande est traitable
            if (\App\Models\AHeure::REFUSE === $statut) {
                $return = $this->updateStatutRefus($id_heure, $put['comment_refus'][$id_heure]);
                log_action(0, '', '', 'Refus de la demande d\'heure additionnelle ' . $id_heure . ' de ' . $infoDemande['login']);
            } elseif (\App\Models\AHeure::ACCEPTE === $statut) {
                if (\App\ProtoControllers\Responsable::isDoubleValGroupe($infoDemande['login'])) {
                    $return = $this->updateStatutPremiereValidation($id_heure);
                    log_action(0, '', '', 'Demande d\'heure additionnelle ' . $id_heure . ' de ' . $infoDemande['login'] . ' transmise au grand responsable');
                } else {
                    $return = $this->putValidationFinale($id_heure);
                    log_action(0, '', '', 'Validation de la demande d\'heure additionnelle ' . $id_heure . ' de ' . $infoDemande['login']);
                }
            }
        } else {
            $localError[] = _('demande_deja_traite') . ': ' . $infoDemande['login'];
            $return = NIL_INT;
        }
        if( 0 < $return) {
            $notif = new \App\Libraries\Notification\Additionnelle($id_heure);
            if (!$notif->send()) {
                $localError[] = _('erreur_envoi_mail') . ': ' . $infoDemande['login'];
                $return = NIL_INT;
            }
        }
        $errors = array_merge($errors, $localError);
        return $return;
    }

    /**
     * {@inheritDoc}
     */
    protected function putGrandResponsable(array $infoDemande, $statut, array $put, array &$errors)
    {
        $localError = [];
        $return = NIL_INT;
        $id_heure = $infoDemande['id_heure'];
        if ($this->isDemandeTraitable($infoDemande['statut'])) { // demande est traitable
            if (\App\Models\AHeure::REFUSE === $statut) {
                $return = $this->updateStatutRefus($id_heure, $put['comment_refus'][$id_heure]);
                log_action(0, '', '', 'Refus de la demande d\'heure additionnelle ' . $id_heure . ' de ' . $infoDemande['login']);
            } elseif (\App\Models\AHeure::ACCEPTE === $statut) {
                if (\App\ProtoControllers\Responsable::isDoubleValGroupe($infoDemande['login'])) {
                    $return = $this->putValidationFinale($id_heure);
                    log_action(0, '', '', 'Validation de la demande d\'heure additionnelle ' . $id_heure . ' de ' . $infoDemande['login']);
                } else {
                $localError[] = _('traitement_non_autorise') . ': ' . $infoDemande['login'];
                }
            }
        } else {
            $localError[] = _('demande_deja_traite') . ': ' . $infoDemande['login'];
            $return = NIL_INT;
        }
        if( 0 < $return) {
            $notif = new \App\Libraries\Notification\Additionnelle($id_heure);
            if (!$notif->send()) {
                $localError[] = _('erreur_envoi_mail') . ': ' . $infoDemande['login'];
                $return = NIL_INT;
            }
        }
        $errors = array_merge($errors, $localError);
        return $return;
    }

    /**
     * Mise a jour du statut de la demande d'heure
     *
     * @param int $demandeId
     *
     * @return int
     */
    protected function updateStatutValidationFinale($demandeId)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE heure_additionnelle
                SET statut = ' . \App\Models\AHeure::STATUT_VALIDATION_FINALE . '
                WHERE id_heure = '. (int) $demandeId;
        $query = $sql->query($req);

        return $sql->affected_rows;
    }


    /**
     * Refus de la demande d'heure
     *
     * @param int $demandeId
     * @param int $comment
     *
     * @return int
     */
    protected function updateStatutRefus($demandeId, $comment)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE heure_additionnelle
                SET statut = ' . \App\Models\AHeure::STATUT_REFUS . ',
                    comment_refus = \''.\includes\SQL::quote($comment).'\'
                WHERE id_heure = '. (int) $demandeId;
        $query = $sql->query($req);

        return $sql->affected_rows;
    }

    /**
     * Première validation de la demande d'heure
     *
     * @param int $demandeId
     *
     * @return int
     */
    protected function updateStatutPremiereValidation($demandeId)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE heure_additionnelle
                SET statut = ' . \App\Models\AHeure::STATUT_PREMIERE_VALIDATION . '
                WHERE id_heure = '. (int) $demandeId;
        $query = $sql->query($req);

        return $sql->affected_rows;
    }

    /**
     * Ajout de la demande additionnelle au solde d'heure du demandeur
     *
     * @param int $demandeId
     *
     * @return int
     */
    protected function updateSolde($demandeId)
    {
        $user = $this->getInfoDemandes([$demandeId]);
        $sql = \includes\SQL::singleton();
        $req   = 'UPDATE conges_users
                SET u_heure_solde = u_heure_solde+' .$user[$demandeId]['duree'] . '
                WHERE u_login = \''. $user[$demandeId]['login'] .'\'';
        $query = $sql->query($req);

        return $sql->affected_rows;
    }

    /**
     * {@inheritDoc}
     */
    public function getForm()
    {
        $return     = '';
        $notice = '';
        $errorsLst  = [];
        $i = true;

        $return .= '<h1>' . _('traitement_heure_additionnelle_titre') . '</h1>';

        if (!empty($_POST)) {
            if (0 >= (int) $this->post($_POST, $notice, $errorsLst)) {
                $errors = '';
                if (!empty($errorsLst)) {
                    foreach ($errorsLst as $key => $value) {
                        if (is_array($value)) {
                            $value = implode(' / ', $value);
                        }
                        $errors .= '<li>' . $key . ' : ' . $value . '</li>';
                    }
                    $return .= '<br><div class="alert alert-danger">' . _('erreur_recommencer') . '<ul>' . $errors . '</ul></div>';
                } elseif(!empty($notice)) {
                    $return .= '<br><div class="alert alert-info">' .  $notice . '.</div>';

                }
            }
        }

        $return .= '<form action="" method="post" class="form-group">';
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-striped',
            'table-condensed'
        ]);
        $childTable = '<thead><tr><th>' . _('divers_nom_maj_1') . '<br>' . _('divers_prenom_maj_1') . '</th>';
        $childTable .= '<th>' . _('jour') . '</th>';
        $childTable .= '<th>' . _('divers_debut_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_fin_maj_1') . '</th>';
        $childTable .= '<th>' . _('duree') . '</th>';
        $childTable .= '<th>' . _('divers_solde') . '</th>';
        $childTable .= '<th>' . _('divers_comment_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_accepter_maj_1') . '</th><th>' . _('divers_refuser_maj_1') . '</th><th>' . _('resp_traite_demandes_attente') . '</th><th></th>';
        $childTable .= '<th>' . _('resp_traite_demandes_motif_refus') . '</th>';
        $childTable .= '</tr></thead><tbody>';

        $demandesResp = $this->getDemandesResponsable($_SESSION['userlogin']);
        $demandesGrandResp = $this->getDemandesGrandResponsable($_SESSION['userlogin']);
        if (empty($demandesResp) && empty($demandesGrandResp) ) {
            $childTable .= '<tr><td colspan="11"><center>' . _('aucune_demande') . '</center></td></tr>';
        } else {
            if(!empty($demandesResp)) {
                $childTable .= $this->getFormDemandes($demandesResp);
            }
            if (!empty($demandesGrandResp)) {
                $childTable .='<tr align="center"><td class="histo" style="background-color: #CCC;" colspan="11"><i>'._('resp_etat_users_titre_double_valid').'</i></td></tr>';
                $childTable .= $this->getFormDemandes($demandesGrandResp);

            }
        }

        $childTable .= '</tbody>';

        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        if (!empty($demandesResp) || !empty($demandesGrandResp) ) {
            $return .= '<div class="form-group"><input type="submit" class="btn btn-success" value="' . _('form_submit') . '" /></div>';
        }
        $return .='</form>';

        return $return;
    }


     /**
      * {@inheritDoc}
      */
    protected function getIdDemandesResponsable($resp)
    {
        $groupId = \App\ProtoControllers\Responsable::getIdGroupeResp($resp);

        $usersResp = [];
        $usersResp = \App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds($groupId);

        $usersRespDirect = \App\ProtoControllers\Responsable::getUsersRespDirect($resp);
        $usersResp = array_merge($usersResp,$usersRespDirect);
        $usersResp = array_diff($usersResp,[$_SESSION['userlogin']]);
        
        if (empty($usersResp)) {
            return [];
        }

        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT id_heure AS id
                FROM heure_additionnelle
                WHERE login IN (\'' . implode('\',\'', $usersResp) . '\')
                AND statut = '.AHeure::STATUT_DEMANDE;
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }
        return $ids;
    }

     /**
      * {@inheritDoc}
      */
    protected function getIdDemandesGrandResponsable($gResp)
    {
        $groupId = \App\ProtoControllers\Responsable::getIdGroupeGrandResponsable($gResp);
        if (empty($groupId)) {
            return [];
        }

        $usersResp = \App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds($groupId);
        if (empty($usersResp)) {
            return [];
        }

        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT id_heure AS id
                FROM heure_additionnelle
                WHERE login IN (\'' . implode('\',\'', $usersResp) . '\')
                AND statut = '.AHeure::STATUT_PREMIERE_VALIDATION;
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }
        return $ids;
    }

    /**
     * {@inheritDoc}
     */
    protected function getInfoDemandes(array $listId)
    {
        $infoDemande =[];

        if (empty($listId)) {
            return [];
        }
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM heure_additionnelle
                WHERE id_heure IN (' . implode(',', $listId) . ')
                ORDER BY debut DESC, statut ASC';

        $ListeDemande = $sql->query($req)->fetch_all(MYSQLI_ASSOC);
        foreach ($ListeDemande as $demande){
            $infoDemande[$demande['id_heure']] = $demande;
        }

        return $infoDemande;
    }
}
