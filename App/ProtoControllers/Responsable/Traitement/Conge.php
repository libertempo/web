<?php
namespace App\ProtoControllers\Responsable\Traitement;

/**
 * ProtoContrôleur de validation des conges
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina <wouldsmina@tuxfamily.org>
 */
class Conge extends \App\ProtoControllers\Responsable\ATraitement
{
    /**
     * {@inheritDoc}
     */
    public function getForm()
    {
        $return     = '';
        $notice = '';
        $errorsLst  = [];

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

        $demandesResp = $this->getDemandesResponsable($_SESSION['userlogin']);
        $demandesGrandResp = $this->getDemandesGrandResponsable($_SESSION['userlogin']);
        if (empty($demandesResp) && empty($demandesGrandResp) ) {
            $childTable .= '<tr><td colspan="11"><center>' . _('resp_traite_demandes_aucune_demande') . '</center></td></tr>';
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
        $return .= '<div class="form-group"><input type="submit" class="btn btn-success" value="' . _('form_submit') . '" /></div>';
        $return .='</form>';

        return $return;
    }
    
    /**
     * {@inheritDoc}
     */
    protected function getFormDemandes(array $demandes)
    {
        $i=true;
        $Table='';
        
        foreach ( $demandes as $demande ) {
            $id = $demande['p_num'];
            $infoUtilisateur = $this->getDonneesUtilisateur($demande['p_login']);
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
            $Table .= '<td><b>'.$infoUtilisateur['u_nom'].'</b><br>'.$infoUtilisateur['u_prenom'].'</td>';
            $Table .= '<td>'.$debut.'<span class="demi">' . $demideb . '</span></td><td>'.$fin.'<span class="demi">' . $demifin . '</span></td>';
            $Table .= '<td>'.$type.'</td><td><b>'.floatval($demande['p_nb_jours']).'</b></td><td>'.floatval($solde).'</td>';
            $Table .= '<td>'.$demande['p_commentaire'].'</td>';
            $Table .= '<input type="hidden" name="_METHOD" value="PUT" />';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="1"></td>';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="2"></td>';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="NULL" checked></td>';
            $Table .= '<td><input class="form-control" type="text" name="comment_refus['.$id.']" size="20" max="100"></td></tr>';
            $i = !$i;
            }
            
        return $Table;
    }

    /**
     * {@inheritDoc}
     */
    public function put(array $put, $resp, &$notice, array &$errorLst)
    {
        $return = '1';
        $infoDemandes = $this->getInfoDemandes(array_keys($put['demande']));

        foreach ($put['demande'] as $id_conge => $statut) {
            if ($this->isRespDeUtilisateur($resp, $infoDemandes[$id_conge]['p_login'])) {
                $return = $this->putResponsable($infoDemandes[$id_conge], $statut, $put, $errorLst);
            } elseif ($this->isGrandRespDeUtilisateur($resp, $this->getGroupesId($infoDemandes[$id_conge]['p_login']))) {
                $return = $this->putGrandResponsable($infoDemandes[$id_conge], $statut, $put, $errorLst);
            } else {
                $errorLst[] = _('erreur_pas_responsable_de') . ' ' . $infoDemandes['id_conge']['p_login'];
                $return = NIL_INT;
            }
        }
        $notice = _('traitement_effectue');
        return $return;
    }
    
    /**
     * {@inheritDoc}
     */
    protected function putResponsable(array $infoDemande, $statut, array $put, array &$errorLst)
    {
        $return = NIL_INT;
        $id_conge = $infoDemande['p_num'];
        if ($this->isDemandeTraitable($infoDemande['p_etat'])) { // demande est traitable
            if (\App\Models\Conge::REFUSE === $statut) {
                $return = $this->updateStatutRefus($id_conge, $put['comment_refus'][$id_conge]);
                log_action($infoDemande['p_num'], 'refus', '', $infoDemande['p_login'], 'traitement demande ' . $id_conge . ' (' . $infoDemande['p_login'] . ') (' . $infoDemande['p_nb_jours'] . ' jours) : refus');
            } elseif (\App\Models\Conge::ACCEPTE === $statut) {
                if ($this->isDoubleValGroupe($infoDemande['p_login'])) {
                    $return = $this->updateStatutPremiereValidation($id_conge);
                    log_action($infoDemande['p_num'], 'valid', $infoDemande['p_login'], 'traitement demande conges ' . $id_conge . ' de ' . $infoDemande['p_login'] . ' première validation');
                } else {
                    $return = $this->putValidationFinale($id_conge);
                    log_action($infoDemande['p_num'], 'ok', $infoDemande['p_login'], 'traitement demande ' . $id_conge . ' (' . $infoDemande['p_login'] . ') (' . $infoDemande['p_nb_jours'] . ' jours) : OK');
                }
            }
        } else {
            $errorLst[] = _('demande_deja_traite') . ': ' . $infoDemande['p_login'];
            $return = NIL_INT;
        }
        return $return;
    }

    /**
     * {@inheritDoc}
     */
    protected function putGrandResponsable(array $infoDemande, $statut, array $put, array &$errorLst)
    {
        $return = NIL_INT;
        $id_conge = $infoDemande['p_num'];
        if ($this->isDemandeTraitable($infoDemande['p_etat'])) { // demande est traitable
            if (\App\Models\Conge::REFUSE === $statut) {
                $return = $this->updateStatutRefus($id_conge, $put['comment_refus'][$id_conge]);
                log_action($infoDemande['p_num'], 'refus', '', $infoDemande['p_login'], 'traitement demande ' . $id_conge . ' (' . $infoDemande['p_login'] . ') (' . $infoDemande['p_nb_jours'] . ' jours) : refus');
            } elseif (\App\Models\Conge::ACCEPTE === $statut) {
                if ($this->isDoubleValGroupe($infoDemande['p_login'])) {
                    $return = $this->putValidationFinale($id_conge);
                    log_action($infoDemande['p_num'], 'ok', $infoDemande['p_login'], 'traitement demande ' . $id_conge . ' (' . $infoDemande['p_login'] . ') (' . $infoDemande['p_nb_jours'] . ' jours) : OK');
                } else {
                $errorLst[] = _('traitement_non_autorise') . ': ' . $infoDemande['p_login'];
                $return = NIL_INT;
                }
            }
        } else {
            $errorLst[] = _('demande_deja_traite') . ': ' . $infoDemande['p_login'];
            $return = NIL_INT;
        }
        return $return;
    }

    /**
     * Validation finale avec prise en compte des reliquats
     * 
     * @param type $demandeId
     * @return int
     */
    protected function putValidationFinale($demandeId) 
    {
        $demande = $this->getInfoDemandes(explode(" ", $demandeId))[$demandeId];
        if($this->isOptionReliquatActive() && $this->isReliquatUtilisable($demande['p_date_fin']) && 0 < $this->getReliquatconge($demande['p_login'], $demande['p_type'])) {
            $SoldeReliquat = $this->getReliquatconge($demande['p_login'], $demande['p_type']);
        
            if($SoldeReliquat>=$demande['p_nb_jours']) {
                $sql = \includes\SQL::singleton();
                $sql->getPdoObj()->begin_transaction();
                $updateReliquat = $this->updateReliquatUser($demande['p_login'], $demande['p_nb_jours'], $demande['p_type']);
                $updateStatut = $this->updateStatutValidationFinale($demande['p_num']);
                if (0 < $updateReliquat && 0 < $updateStatut) {
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
                $updateReliquat = $this->updateReliquatUser($demande['p_login'], $SoldeReliquat, $demande['p_type']);
                $updateSolde = $this->updateSoldeUser($demande['p_login'], $ResteSolde, $demande['p_type']);
                $updateStatut = $this->updateStatutValidationFinale($demande['p_num']);
                if (0 < $updateReliquat && 0 < $updateStatut && 0 < $updateSolde) {
                    $sql->getPdoObj()->commit();
                } else {
                    $sql->getPdoObj()->rollback();
                    return NIL_INT;
                }
                return 1;
            }
        } else {
            $id = $this->updateSoldeUser($demande['p_login'], $demande['p_nb_jours'], $demande['p_type']);
            $this->updateStatutValidationFinale($demande['p_num']);
            log_action($demande['p_num'],"ok", $demande['p_login'], 'traitement demande ' . $demande['p_num'] . ' (' . $demande['p_login'] . ') (' . $demande['p_nb_jours'] . ' jours) : OK');
        }
    }
    
    /**
     * Première validation de la demande de congé
     * 
     * @param int $demandeId
     * 
     * @return int
     */
    protected function updateStatutPremiereValidation($demandeId)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE conges_periode
                SET p_etat = \'' . \App\Models\Conge::STATUT_VALIDE . '\'
                WHERE p_num = '. (int) $demandeId;
        $query = $sql->query($req);

        return $sql->affected_rows;
    }
    
    /**
     * Refus de la demande de congé
     * 
     * @param int $demandeId
     * @param int $comm
     * 
     * @return int $id 
     */
    protected function updateStatutRefus($demandeId, $comm)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE conges_periode
                SET p_etat = \'' . \App\Models\Conge::STATUT_REFUS . '\',
                    p_motif_refus = \'' . \includes\SQL::quote($comm) .'\'
                WHERE p_num = '. (int) $demandeId;
        $query = $sql->query($req);

        return $sql->affected_rows;
    }
    
    /**
     * Validation finale de la demande de conges
     * 
     * @param int $demandeId
     * 
     * @return int $id 
     */
    protected function updateStatutValidationFinale($demandeId)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE conges_periode
                SET p_etat = \'' . \App\Models\Conge::STATUT_OK . '\'
                WHERE p_num = '. (int) $demandeId;
        $query = $sql->query($req);

        return $sql->affected_rows;
    }
    
    /**
     * Mise a jour du solde (selon le type de congés) du demandeur
     * 
     * @param string $user
     * @param int $duree
     * @param int $typeId
     * 
     * @return int
     */
    protected function updateSoldeUser($user,$duree,$typeId)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE conges_solde_user
                SET su_solde = su_solde-' .number_format($duree,2) . '
                WHERE su_login = \''. $user .'\'
                AND su_abs_id = '. (int) $typeId;
        $query = $sql->query($req);

        return $sql->affected_rows;
    }
    
    /**
     * Mise a jour du reliquat (selon le type de congés) du demandeur
     * 
     * @param string $user
     * @param int $duree
     * @param int $typeId
     * 
     * @return int
     */
    protected function updateReliquatUser($user,$duree,$typeId)
    {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE conges_solde_user
                SET su_reliquat = su_reliquat-' .number_format($duree,2) . '
                WHERE su_login = \''. $user .'\'
                AND su_abs_id = '. (int) $typeId;
        $query = $sql->query($req);

        return $sql->affected_rows;
    }

     /**
      * {@inheritDoc}
      */
    protected function getIdDemandesResponsable($resp)
    { 
        $groupId = $this->getIdGroupeResp($resp);
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
    protected function getIdDemandesGrandResponsable($gResp)
    {
        $groupId = $this->getIdGroupeGrandResponsable($gResp);
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
    protected function getInfoDemandes(array $listId)
    {
        $infoDemande =[];
        
        if (empty($listId)) {
            return [];
        }
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_periode
                WHERE p_num IN (' . implode(',', $listId) . ')
                ORDER BY p_date_deb DESC, p_etat ASC';
        
        $ListeDemande = $sql->query($req)->fetch_all(MYSQLI_ASSOC);
        
        foreach ($ListeDemande as $demande){
            $infoDemande[$demande['p_num']] = $demande;
        }

        return $infoDemande;
    }
    
    /**
     * Retourne le solde de conges (selon le type) d'un utilisateur
     *
     * @param string $login
     * @param int $typeId 
     *
     * @return int $solde
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
     * Retourne le reliquat de conges (selon le type) d'un utilisateur
     *
     * @param string $login
     * @param int $typeId 
     *
     * @return int $rel
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
     * Verifie si la demande n'a pas déja été traité
     * 
     * @param string $statutDb
     * @param string $statut
     * 
     * @return bool
     */
    public function isDemandeTraitable($statut)
    {
        return ($statut != \App\Models\conge::STATUT_ANNUL || $statut != \App\Models\Conge::STATUT_OK || $statut != \App\Models\Conge::STATUT_REFUS);
    }
    
    /**
     * verifie que les reliquats sont autorisées
     *
     * @return bool
     */
    public function isOptionReliquatActive()
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
     * @return bool
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
