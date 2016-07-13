<?php
namespace App\ProtoControllers\Responsable\Traitement;

use \App\Models\Conge;

/**
 * ProtoContrôleur de validation des conges
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina <wouldsmina@tuxfamily.org>
 */
class Conge extends \App\ProtoControllers\Responsable\ATraitement
{
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

        $return .= '<h1>' . _('resp_traite_demandes_titre_tableau_1') . '</h1>';
        $return .= '<form action="" method="post" class="form-group">';
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-striped',
            'table-condensed'
        ]);
        $childTable = '<thead><tr><th>' . _('divers_nom_maj_1') . '<br>' . _('divers_prenom_maj_1') .  '</th>';
        $childTable .= '<th>' . _('divers_debut_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_fin_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_type_maj_1') . '</th>';
        $childTable .= '<th>' . _('resp_traite_demandes_nb_jours') . '</th>';
        $childTable .= '<th>' . _('divers_solde') . '</th>';
        $childTable .= '<th>' . _('divers_comment_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_accepter_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_refuser_maj_1') . '</th>';
        $childTable .= '<th>' . _('resp_traite_demandes_attente') . '</th>';
        $childTable .= '<th>' . _('resp_traite_demandes_motif_refus') . '</th>';
        $childTable .= '</tr></thead><tbody>';

        $demandesResp = $this->getDemandesResp($_SESSION['userlogin']);
        $demandesGrandResp = $this->getDemandesGrandResp($_SESSION['userlogin']);
        if (empty($demandesResp) && empty($demandesGrandResp) ) {
            $childTable .= '<tr><td colspan="6"><center>' . _('resp_traite_demandes_aucune_demande') . '</center></td></tr>';
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
    
    protected function getDemandesTab(array $demandes)
    {
        $i=true;
        $Table='';
        
        foreach ( $demandes as $demande ) {
            $id = $demande['p_num'];
            $nom = $this->getNom($demande['p_login']);
            $prenom = $this->getPrenom($demande['p_login']);
            $solde = $this->getSoldeconge($demande['p_login'],$demande['p_type']);
            $type = $this->getTypeLabel($demande['p_type']);
            $debut = \App\Helpers\Formatter::dateIso2Fr($demande['p_date_deb']);
            $fin = \App\Helpers\Formatter::dateIso2Fr($demande['p_date_fin']);
            if($demande['p_demi_jour_deb']=="am") {
                $demideb = _('form_am');
            }  else {
                $demideb = _('form_pm');
            }
            
            if($demande['p_demi_jour_fin']=="am") {
                $demifin = _('form_am');
            } else {
                $demifin = _('form_pm');
            }
            
            $Table .= '<tr class="'.($i?'i':'p').'">';
            $Table .= '<td><b>'.$nom.'</b><br>'.$prenom.'</td>';
            $Table .= '<td>'.$debut.'<span class="demi">' . $demideb . '</span></td><td>'.$fin.'<span class="demi">' . $demifin . '</span></td>';
            $Table .= '<td>'.$type.'</td><td><b>'.floatval($demande['p_nb_jours']).'</b></td><td>'.floatval($solde).'</td>';
            $Table .= '<td>'.$demande['p_commentaire'].'</td>';
            $Table .= '<input type="hidden" name="_METHOD" value="PUT" />';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="STATUT_OK"></td>';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="STATUT_REFUS"></td>';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="NULL" checked></td>';
            $Table .= '<td><input class="form-control" type="text" name="comment_refus['.$id.']" size="20" max="100"></td></tr>';
            $i = !$i;
            }
            
        return $Table;
    }
    
    /**
     * {@inheritDoc}
     */
    protected function put(array $put, $resp, &$notice, array &$errorLst)
    {
        foreach ($put['demande'] as $id_conge => $statut){
            $infoDemande = $this->getListeSQL(explode(" ", $id_conge));
            if($this->isDemandeTraitable($infoDemande[0]['p_etat'], $statut)) {
                if( ($this->isRespDeUser($resp, $infoDemande[0]['p_login']) || $this->isGrandRespDeUser($resp, $this->getGroupesId($infoDemande[0]['p_login']))) && $statut == 'STATUT_REFUS') {
                    $id = $this->updateStatutRefus($id_conge, $put['comment_refus'][$id_conge]);
                    log_action($infoDemande[0]['p_num'],"refus", $infoDemande[0]['p_login'], 'traitement demande ' . $id . ' (' . $infoDemande[0]['p_login'] . ') (' . $infoDemande[0]['p_nb_jours'] . ' jours) : refus');
                } elseif( (($this->isRespDeUser($resp, $infoDemande[0]['p_login']) && !$this->isDoubleValGroupe($infoDemande[0]['p_login'])) || ($this->isGrandRespDeUser($resp, $this->getGroupesId($infoDemande[0]['p_login'])) && $this->isDoubleValGroupe($infoDemande[0]['p_login']))) && $statut == 'STATUT_OK' ) {
                    if($this->isReliquatAutorise() && $this->isReliquatUtilisable($infoDemande[0]['p_date_fin']) && 0 < $this->getReliquatconge($infoDemande[0]['p_login'], $infoDemande[0]['p_type'])) {
                        $id = $this->gestionSoldeReliquat($infoDemande[0]);
                    } else {
                        $id = $this->updateSoldeUser($infoDemande[0]['p_login'], $infoDemande[0]['p_nb_jours'], $infoDemande[0]['p_type']);
                        $this->updateStatutOk($infoDemande[0]['p_num']);                    }
                    log_action($infoDemande[0]['p_num'],"ok", $infoDemande[0]['p_login'], 'traitement demande ' . $id . ' (' . $infoDemande[0]['p_login'] . ') (' . $infoDemande[0]['p_nb_jours'] . ' jours) : OK');
                } elseif($this->isRespDeUser($resp, $infoDemande[0]['p_login']) && $this->isDoubleValGroupe($infoDemande[0]['p_login']) && $statut == 'STATUT_OK' ) {
                        $id = $this->updateStatutValide($id_conge);
                        log_action($infoDemande[0]['p_num'], 'valid', $infoDemande[0]['p_login'], 'traitement dmande conges ' . $id . ' de ' . $infoDemande[0]['login'] . ' première validation');
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

    
    protected function gestionSoldeReliquat($demande) 
    {
        $SoldeReliquat = $this->getReliquatconge($demande['p_login'], $demande['p_type']);
        
        if($SoldeReliquat>=$demande['p_nb_jours']) {
            $sql = \includes\SQL::singleton();
            $sql->getPdoObj()->begin_transaction();
            $idReliquat = $this->updateReliquatUser($demande['p_login'], $demande['p_nb_jours'], $demande['p_type']);
            $idStatut = $this->updateStatutOk($demande['p_num']);
            if (0 < $idReliquat && 0 < $idStatut) {
                $sql->getPdoObj()->commit();
            } else {
                $sql->getPdoObj()->rollback();
                return NIL_INT;
            }
            return $demande['p_num'];
        } else {
            $ResteSolde = $demande['p_nb_jours'] - $SoldeReliquat;
            $sql = \includes\SQL::singleton();
            $sql->getPdoObj()->begin_transaction();
            $idReliquat = $this->updateReliquatUser($demande['p_login'], $SoldeReliquat, $demande['p_type']);
            $idSolde = $this->updateSoldeUser($demande['p_login'], $ResteSolde, $demande['p_type']);
            $idStatut = $this->updateStatutOk($demande['p_num']);
            if (0 < $idReliquat && 0 < $idStatut && 0 < $idSolde) {
                $sql->getPdoObj()->commit();
            } else {
                $sql->getPdoObj()->rollback();
                return NIL_INT;
            }
            return 1;
        }
    }
    /**
     * Première validation de la demande
     * 
     * @param int $demande
     * @param int $statut
     * 
     * @return int $id 
     */
    protected function updateStatutValide($demande)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE conges_periode
                SET p_etat = \'' . \App\Models\Conge::STATUT_VALIDE . '\'
                WHERE p_num = '. (int) $demande;
        $query = $sql->query($req);

        return $demande;
    }
    
    /**
     * Refus de la demande
     * 
     * @param int $demande
     * @param int $comm
     * 
     * @return int $id 
     */
    protected function updateStatutRefus($demande, $comm)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE conges_periode
                SET p_etat = \'' . \App\Models\Conge::STATUT_REFUS . '\',
                    p_motif_refus = \'' . \includes\SQL::quote($comm) .'\'
                WHERE p_num = '. (int) $demande;
        $query = $sql->query($req);

        return $demande;
    }
    
    /**
     * Validation finale de la demande
     * 
     * @param int $demande
     * 
     * @return int $id 
     */
    protected function updateStatutOk($demande)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE conges_periode
                SET p_etat = \'' . \App\Models\Conge::STATUT_OK . '\'
                WHERE p_num = '. (int) $demande;
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
    protected function updateSoldeUser($user,$duree,$typeId)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE conges_solde_user
                SET su_solde = su_solde-' .number_format($duree,2) . '
                WHERE su_login = \''. $user .'\'
                AND su_abs_id = '. (int) $typeId;
        $query = $sql->query($req);

        return 1;
    }
    
    /**
     * Mise a jour du reliquat du demandeur
     * 
     * @param int $demande
     * 
     * @return int $demande
     */
    protected function updateReliquatUser($user,$duree,$typeId)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE conges_solde_user
                SET su_reliquat = su_reliquat-' .number_format($duree,2) . '
                WHERE su_login = \''. $user .'\'
                AND su_abs_id = '. (int) $typeId;
        $query = $sql->query($req);

        return 1;
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
        $req = 'SELECT p_num AS id
                FROM conges_periode
                WHERE p_login IN (\'' . implode(',', $usersResp) . '\')
                AND p_etat = \''. \App\Models\Conge::STATUT_DEMANDE.'\'';
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
        $req = 'SELECT p_num AS id
                FROM conges_periode
                WHERE p_login IN (\'' . implode(',', $usersResp) . '\')
                AND p_etat = \''. \App\Models\Conge::STATUT_VALIDE .'\'';
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
                FROM conges_periode
                WHERE p_num IN (' . implode(',', $listId) . ')
                ORDER BY p_date_deb DESC, p_etat ASC';

        return $sql->query($req)->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Retourne le solde de conges d'un utilisateur
     *
     * @param string $login
     * @param int $typeId 
     *
     * @return int
     */
    public function getSoldeconge($login, $typeId)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT su_solde FROM conges_solde_user WHERE su_login = \''.$login.'\'
                AND su_abs_id ='. (int) $typeId;
        $query = $sql->query($req);
        $solde = $query->fetch_array()[0];

        return $solde;
    }
    
    /**
     * Retourne le reliquat de conges d'un utilisateur
     *
     * @param string $login
     * @param int $typeId 
     *
     * @return int
     */
    public function getReliquatconge($login, $typeId)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT su_reliquat FROM conges_solde_user WHERE su_login = \''.$login.'\'
                AND su_abs_id ='. (int) $typeId;
        $query = $sql->query($req);
        $rel = $query->fetch_array()[0];

        return $rel;
    }
    
    /**
     * verifie que les reliquats sont autorisées
     *
     * @return boolean
     */
    public function isReliquatAutorise()
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT conf_valeur
                    FROM conges_config
                    WHERE conf_nom = "autorise_reliquats_exercice"';
        $query = $sql->query($req);

        return $query->fetch_array()[0];
    }
    
   /**
     * verifie si la date limite d'usage des reliquats n'est pas dépassée
     *
     * @param int $findemande date de fin de la demande
     * @return boolean
     */
    public function isReliquatUtilisable($findemande)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT conf_valeur
                    FROM conges_config
                    WHERE conf_nom = "jour_mois_limite_reliquats"';
        $query = $sql->query($req);
        
        $dlimite = $query->fetch_array()[0];
        if ($dlimite == 0) {
            return true;
        }
        return $findemande < $dlimite;
    }
    
    
    
    /**
     * verifie si la date limite d'usage des reliquats n'est pas dépassée
     *
     * @param int $type
     * 
     * @return string $tLabel
     */
    public function getTypeLabel($type)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT ta_libelle FROM conges_type_absence WHERE ta_id = '.$type;
        $query = $sql->query($req);
        $tLabel = $query->fetch_array()[0];

        return $tLabel;
    }
}

