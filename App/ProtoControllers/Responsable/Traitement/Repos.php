<?php
namespace App\ProtoControllers\Responsable\Traitement;

use App\Models\AHeure;

/**
 * ProtoContrôleur de validation d'heures additionnelles
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina <wouldsmina@tuxfamily.org>
 */
class Repos extends \App\ProtoControllers\Responsable\ATraitement
{
    /**
     * {@inheritDoc}
     */
    protected function put(array $put, $resp, &$notice, array &$errorLst)
    {
        $return ='1';
        $infoDemande = $this->getListeSQL(array_keys($put['demande']));

        foreach ($put['demande'] as $id_heure => $statut){
            if($this->isDemandeTraitable($infoDemande[$id_heure]['statut'], $statut)) {
                if( ($this->isRespDeUser($resp, $infoDemande[$id_heure]['login']) || $this->isGrandRespDeUser($resp, $this->getGroupesId($infoDemande[$id_heure]['login']))) && $statut == 'STATUT_REFUS') {
                    $id = $this->updateStatutRefus($id_heure, $put['comment_refus'][$id_heure]);
                    log_action(0, '', '', 'Refus de la demande d\'heure de repos ' . $id_heure . ' de ' . $infoDemande[$id_heure]['login']);
                } elseif( (($this->isRespDeUser($resp, $infoDemande[$id_heure]['login']) && !$this->isDoubleValGroupe($infoDemande[$id_heure]['login'])) || ($this->isGrandRespDeUser($resp, $this->getGroupesId($infoDemande[$id_heure]['login'])) && $this->isDoubleValGroupe($infoDemande[$id_heure]['login']))) && $statut == 'STATUT_OK' ) {
                        $id = $this->demandeOk($id_heure);
                        log_action(0, '', '', 'Validation de la demande d\'heure de repos ' . $id_heure . ' de ' . $infoDemande[$id_heure]['login']);
                } elseif($this->isRespDeUser($resp, $infoDemande[$id_heure]['login']) && $this->isDoubleValGroupe($infoDemande[$id_heure]['login']) && $statut == 'STATUT_OK' ) {
                        $id = $this->updateStatutValide($id_heure);
                        log_action(0, '', '', 'Demande d\'heure de repos ' . $id_heure . ' de ' . $infoDemande[$id_heure]['login'] . ' transmise au grand responsable');
                } elseif($statut != "NULL") {
                    $errorLst[] = _('traitement_non_autorise').': '.$infoDemande[$id_heure]['login'];
                }
            } else {
                $errorLst[] = _('demande_deja_traite');
                $return = NIL_INT;
            }
        }
        $notice = _('traitement_effectue');
        return $return;
    }

    /**
     * Mise a jour du statut de la demande d'heure
     * 
     * @param int $demande
     * @param int $statut
     * 
     * @return int $id 
     */
    protected function updateStatutOk($demande)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE heure_repos
                SET statut = ' . \App\Models\AHeure::STATUT_OK . '
                WHERE id_heure = '. (int) $demande;
        $query = $sql->query($req);

        return $sql->affected_rows;
    }
    
    /**
     * Refus de la demande d'heure
     * 
     * @param int $demande
     * @param int $comm
     * 
     * @return int $id 
     */
    protected function updateStatutRefus($demande, $comm)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE heure_repos
                SET statut = ' . \App\Models\AHeure::STATUT_REFUS . ',
                    comment_refus = \'' . \includes\SQL::quote($comm) . '\'
                WHERE id_heure = '. (int) $demande;
        $query = $sql->query($req);

        return $sql->affected_rows;
    }
    
    /**
     * Première validation de la demande d'heure
     * 
     * @param int $demande
     * @param int $comm
     * 
     * @return int $id 
     */
    protected function updateStatutValide($demande)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE heure_repos
                SET statut = ' . \App\Models\AHeure::STATUT_VALIDE . '
                WHERE id_heure = '. (int) $demande;
        $query = $sql->query($req);

        return $sql->affected_rows;
    }

    /**
     * Mise a jour du solde du demandeur
     * 
     * @param int $demande
     * 
     * @return int $demande
     */
    protected function updateSolde($demande)
    {
        $user = $this->getListeSQL(explode(" ",$demande));
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE conges_users
                SET u_heure_solde = u_heure_solde-' .$user[0]['duree'] . '
                WHERE u_login = \''. $user[0]['login'] .'\'';
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
                    $return .= '<div class="alert alert-danger">' . _('erreur_recommencer') . '<ul>' . $errors . '</ul></div>';
                } elseif(!empty($notice)) {
                    $return .= '<div class="alert alert-info">' .  $notice . '.</div>';

                }
            }
        }

        $return .= '<h1>' . _('traitement_heure_repos_titre') . '</h1>';
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
        $childTable .= '<th>' . _('divers_accepter_maj_1') . '</th><th>' . _('divers_refuser_maj_1') . '</th><th>' . _('resp_traite_demandes_attente') . '</th>';
        $childTable .= '<th>' . _('resp_traite_demandes_motif_refus') . '</th>';
        $childTable .= '</tr></thead><tbody>';
        
        $demandesResp = $this->getDemandesResp($_SESSION['userlogin']);
        $demandesGrandResp = $this->getDemandesGrandResp($_SESSION['userlogin']);
        if (empty($demandesResp) && empty($demandesGrandResp) ) {
            $childTable .= '<tr><td colspan="11"><center>' . _('aucun_resultat') . '</center></td></tr>';
        } else {
            if(!empty($demandesResp)) {
                $childTable .= $this->getDemandesTab($demandesResp);
            }
            if (!empty($demandesGrandResp)) {
                $childTable .= $this->getDemandesTab($demandesGrandResp);

            }
        }

        $childTable .= '</tbody>';

        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<div class="form-group"><input type="submit" class="btn btn-success" value="' . _('form_submit') . '" /></div>';
        $return .='</form>';

        return $return;
    }
    
     /**
      * {@inheritDoc}
      */
    protected function getIdDemandesResponsable($resp)
    {
        $groupId = $this->getGroupeRespId($resp);
        if (empty($groupId)) {
            return [];
        }

        $usersResp = [];
        $usersResp = $this->getUsersGroupe($groupId);
        if (empty($usersResp)) {
            return [];
        }

        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT id_heure AS id
                FROM heure_repos
                WHERE login IN (\'' . implode(',', $usersResp) . '\')
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
        $groupId = $this->getGroupeGrandRespId($gResp);
        if (empty($groupId)) {
            return [];
        }
        
        $usersResp = $this->getUsersGroupe($groupId);
        if (empty($usersResp)) {
            return [];
        }
        
        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT id_heure AS id
                FROM heure_repos
                WHERE login IN (\'' . implode(',', $usersResp) . '\')
                AND statut = '.AHeure::STATUT_VALIDE;
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }
        return $ids;
    }

    /**
     * {@inheritDoc}
     */
    protected function getListeSQL(array $listId)
    {
        if (empty($listId)) {
            return [];
        }
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM heure_repos
                WHERE id_heure IN (' . implode(',', $listId) . ')
                ORDER BY debut DESC, statut ASC';

        $ListeDemande = $sql->query($req)->fetch_all(MYSQLI_ASSOC);
        
        foreach ($ListeDemande as $demande){
            $infoDemande[$demande['p_num']] = $demande;
        }

        return $infoDemande;
    }
}
