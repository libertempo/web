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
     * {@inheritDoc}
     */
    protected function put(array $put, $resp, &$notice, array &$errorLst)
    {
        foreach ($put['demande'] as $id_heure => $statut){
            $infoDemande = $this->getListeSQL(explode(" ", $id_heure));
            if($this->isDemandeTraitable($infoDemande[0]['statut'], $statut)) {
                if( ($this->isRespDeUser($resp, $infoDemande[0]['login']) || $this->isGrandRespDeUser($resp, $this->getGroupesId($infoDemande[0]['login']))) && $statut == 'STATUT_REFUS') {
                    $id = $this->updateStatutRefus($id_heure, $put['comment_refus'][$id_heure]);
                    log_action(0, '', '', 'Refus de la demande d\'heure additionnelle ' . $id_heure . ' de ' . $infoDemande[0]['login']);
                } elseif( (($this->isRespDeUser($resp, $infoDemande[0]['login']) && !$this->isDoubleValGroupe($infoDemande[0]['login'])) || ($this->isGrandRespDeUser($resp, $this->getGroupesId($infoDemande[0]['login'])) && $this->isDoubleValGroupe($infoDemande[0]['login']))) && $statut == 'STATUT_OK' ) {
                        $id = $this->demandeOk($id_heure);
                        log_action(0, '', '', 'Validation de la demande d\'heure additionnelle ' . $id_heure . ' de ' . $infoDemande[0]['login']);
                } elseif($this->isRespDeUser($resp, $infoDemande[0]['login']) && $this->isDoubleValGroupe($infoDemande[0]['login']) && $statut == 'STATUT_OK' ) {
                        $id = $this->updateStatutValide($id_heure);
                        log_action(0, '', '', 'Demande d\'heure additionnelle ' . $id_heure . ' de ' . $infoDemande[0]['login'] . ' transmise au grand responsable');
                } elseif($statut != "NULL") {
                    $errorLst[] = _('traitement_non_autorise').': '.$infoDemande[0]['login'];
                }
            } else {
                $errorLst[] = _('demande_deja_traite');
            }
        }
        $notice = _('traitement_effectue');
        return NIL_INT;
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

        $req   = 'UPDATE heure_additionnelle
                SET statut = ' . \App\Models\AHeure::STATUT_OK . '
                WHERE id_heure = '. (int) $demande;
        $query = $sql->query($req);

        return $demande;
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

        $req   = 'UPDATE heure_additionnelle
                SET statut = ' . \App\Models\AHeure::STATUT_REFUS . ',
                    comment_refus = \''.$comm.'\'
                WHERE id_heure = '. (int) $demande;
        $query = $sql->query($req);

        return $demande;
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

        $req   = 'UPDATE heure_additionnelle
                SET statut = ' . \App\Models\AHeure::STATUT_VALIDE . '
                WHERE id_heure = '. (int) $demande;
        $query = $sql->query($req);

        return $demande;
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
                SET u_heure_solde = u_heure_solde+' .$user[0]['duree'] . '
                WHERE u_login = \''. $user[0]['login'] .'\'';
        $query = $sql->query($req);

        return $demande;
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

        $return .= '<h1>' . _('traitement_heure_additionnelle_titre') . '</h1>';
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
            $childTable .= '<tr><td colspan="6"><center>' . _('aucun_resultat') . '</center></td></tr>';
        } else {
            if(!empty($demandesResp)) {
                $childTable .= $this->getDemandesTab($demandesResp);
            }
            if (!empty($demandesGrandResp)) {
                $childTable .='<tr align="center"><td class="histo" style="background-color: #CCC;" colspan="11"><i>'._('resp_etat_users_titre_double_valid').'</i></td></tr>';
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
    protected function getDemandesRespId($resp)
    {
        $groupId = []; 
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
                FROM heure_additionnelle
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
    protected function getDemandesGrandRespId($gResp)
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
                FROM heure_additionnelle
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
                FROM heure_additionnelle
                WHERE id_heure IN (' . implode(',', $listId) . ')
                ORDER BY debut DESC, statut ASC';

        return $sql->query($req)->fetch_all(MYSQLI_ASSOC);
    }
}
