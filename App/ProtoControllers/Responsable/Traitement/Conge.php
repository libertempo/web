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
    public function getFormDemandes(array $demandes)
    {
        $i=true;
        $Table='';

        foreach ($demandes as $demande) {
            $id = $demande['p_num'];
            $infoUtilisateur = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($demande['p_login'])[$demande['p_login']];
            $solde = \App\ProtoControllers\Utilisateur::getSoldeconge($demande['p_login'],$demande['p_type']);
            $debut = \App\Helpers\Formatter::dateIso2Fr($demande['p_date_deb']);
            $fin = \App\Helpers\Formatter::dateIso2Fr($demande['p_date_fin']);
            if ($demande['p_demi_jour_deb']=="am") {
                $demideb = _('form_am');
            }  else {
                $demideb = _('form_pm');
            }

            if ($demande['p_demi_jour_fin']=="am") {
                $demifin = _('form_am');
            } else {
                $demifin = _('form_pm');
            }

            $Table .= '<tr class="'.($i?'i':'p').'">';
            $Table .= '<td><b>'.$infoUtilisateur['u_nom'].'</b><br>'.$infoUtilisateur['u_prenom'].'</td>';
            $Table .= '<td>'.$debut.'<span class="demi">' . $demideb . '</span></td><td>'.$fin.'<span class="demi">' . $demifin . '</span></td>';
            $Table .= '<td><b>'.floatval($demande['p_nb_jours']).'</b></td><td>'.floatval($solde).'</td>';
            $Table .= '<td>'.$demande['p_commentaire'].'</td>';
            $Table .= '<input type="hidden" name="_METHOD" value="PUT" />';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="1"></td>';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="2"></td>';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="NULL" checked></td>';

            /* Informations pour le positionnement du calendrier */
            list($anneeDebut, $moisDebut) = explode('-', $demande['p_date_deb']);
            $mois = new \DateTimeImmutable($anneeDebut . '-' . $moisDebut . '-01');
            $paramsCalendrier = [
                'mois' => $mois->format('Y-m'),
            ];
            $Table .= '<td><a href="' . ROOT_PATH . 'calendrier.php?' . http_build_query($paramsCalendrier) . '" title="' . _('consulter_calendrier_de_periode') . '"><i class="fa fa-lg fa-calendar" aria-hidden="true"></i></a></td>';
            $Table .= '<td><input class="form-control" type="text" name="comment_refus['.$id.']" size="20" maxlength="100"></td></tr>';
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
            if (\App\ProtoControllers\Responsable::isRespDeUtilisateur($resp, $infoDemandes[$id_conge]['p_login']) || \App\ProtoControllers\Responsable::isRespParDelegation($resp, $infoDemandes[$id_conge]['p_login'])) {
                $return = $this->putResponsable($infoDemandes[$id_conge], $statut, $put, $errorLst);
            } elseif (\App\ProtoControllers\Responsable::isGrandRespDeGroupe($resp, \App\ProtoControllers\Utilisateur::getGroupesId($infoDemandes[$id_conge]['p_login']))) {
                $return = $this->putGrandResponsable($infoDemandes[$id_conge], $statut, $put, $errorLst);
            } else {
                $errorLst[] = _('erreur_pas_responsable_de') . ' ' . $infoDemandes[$id_conge]['p_login'];
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
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $return = NIL_INT;
        $id_conge = $infoDemande['p_num'];
        if ($this->isDemandeTraitable($infoDemande['p_etat'])) { // demande est traitable
            if (\App\Models\Conge::REFUSE === $statut) {
                $return = $this->updateStatutRefus($id_conge, $put['comment_refus'][$id_conge]);
                if ($config->isSendMailRefusUtilisateur()) {
                    alerte_mail($_SESSION['userlogin'], $infoDemande['p_login'], $infoDemande['p_num'], "refus_conges");
                }
                log_action($infoDemande['p_num'], 'refus', '', $infoDemande['p_login'], 'traitement demande ' . $id_conge . ' (' . $infoDemande['p_login'] . ') (' . $infoDemande['p_nb_jours'] . ' jours) : refus');
            } elseif (\App\Models\Conge::ACCEPTE === $statut) {
                if (\App\ProtoControllers\Responsable::isDoubleValGroupe($infoDemande['p_login'])) {
                    $return = $this->updateStatutPremiereValidation($id_conge);
                    if ($config->isSendMailPremierValidationUtilisateur()) {
                        alerte_mail($_SESSION['userlogin'], $infoDemande['p_login'], $infoDemande['p_num'], "valid_conges");
                    }
                    log_action($infoDemande['p_num'], 'valid', $infoDemande['p_login'], 'traitement demande conges ' . $id_conge . ' de ' . $infoDemande['p_login'] . ' première validation');
                } else {
                    $return = $this->putValidationFinale($id_conge);
                    if ($config->isSendMailValidationUtilisateur()) {
                        alerte_mail($_SESSION['userlogin'], $infoDemande['p_login'], $infoDemande['p_num'], "accept_conges");
                    }
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
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        $return = NIL_INT;
        $id_conge = $infoDemande['p_num'];
        if ($this->isDemandeTraitable($infoDemande['p_etat'])) { // demande est traitable
            if (\App\Models\Conge::REFUSE === $statut) {
                $return = $this->updateStatutRefus($id_conge, $put['comment_refus'][$id_conge]);
                if ($config->isSendMailRefusUtilisateur()) {
                    alerte_mail($_SESSION['userlogin'], $infoDemande['p_login'], $infoDemande['p_num'], "refus_conges");
                }
                log_action($infoDemande['p_num'], 'refus', '', $infoDemande['p_login'], 'traitement demande ' . $id_conge . ' (' . $infoDemande['p_login'] . ') (' . $infoDemande['p_nb_jours'] . ' jours) : refus');
            } elseif (\App\Models\Conge::ACCEPTE === $statut) {
                if (\App\ProtoControllers\Responsable::isDoubleValGroupe($infoDemande['p_login'])) {
                    $return = $this->putValidationFinale($id_conge);
                    if ($config->isSendMailValidationUtilisateur()) {
                        alerte_mail($_SESSION['userlogin'], $infoDemande['p_login'], $infoDemande['p_num'], "accept_conges");
                    }
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
        if (0 < $this->updateSoldeReliquatEmploye($demande['p_login'], $demande['p_nb_jours'], $demande['p_type'])) {
            return $this->updateStatutValidationFinale($demande['p_num']);
        } else {
            return NIL_INT;
        }
    }

    protected function updateSoldeReliquatEmploye($user, $duree, $typeId)
    {
        $sql = \includes\SQL::singleton();
        $config = new \App\Libraries\Configuration($sql);
        if (!$config->isReliquatsAutorise()) {
            return $this->updateSoldeUser($user, $duree, $typeId);
        }

        $SoldeReliquat = $this->getReliquatconge($user, $typeId);
        if (0 >= $SoldeReliquat) {
            return $this->updateSoldeUser($user, $duree, $typeId);
        }

        if ($this->isReliquatUtilisable($sql)) {
            $sql->getPdoObj()->begin_transaction();
            if ($SoldeReliquat>=$duree) {
                $updateReliquat = $this->updateReliquatUser($user, $duree, $typeId);
                $updateSolde = $this->updateSoldeUser($user, $duree, $typeId);
            } else {
                $updateReliquat = $this->updateReliquatUser($user, $SoldeReliquat, $typeId);
                $updateSolde = $this->updateSoldeUser($user, $duree, $typeId);
            }
            if (0 < $updateReliquat && 0 < $updateSolde) {
                $sql->getPdoObj()->commit();
                return 1;
            } else {
                $sql->getPdoObj()->rollback();
            }
        } else {
            $sql->getPdoObj()->begin_transaction();
            $updateSolde = $this->updateSoldeUser($user, $SoldeReliquat + $duree, $typeId);
            $updateReliquat = $this->updateReliquatUser($user, $SoldeReliquat, $typeId);
            log_action(0,"reliquat", $user, 'retrait reliquat perdu (-' . $SoldeReliquat . ' jours). (date_limite_reliquat)');
            if (0 < $updateReliquat && 0 < $updateSolde) {
                $sql->getPdoObj()->commit();
                return 1;
            } else {
                $sql->getPdoObj()->rollback();
            }
        }
        return NIL_INT;
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
                SET p_etat = \'' . \App\Models\Conge::STATUT_PREMIERE_VALIDATION . '\'
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
        $comm = htmlentities($comm, ENT_QUOTES | ENT_HTML401);
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE conges_periode
                SET p_etat = \'' . \App\Models\Conge::STATUT_REFUS . '\',
                    p_motif_refus = \'' . \includes\SQL::quote($comm) .'\',
                    p_date_traitement=NOW()
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
                SET p_etat = \'' . \App\Models\Conge::STATUT_VALIDATION_FINALE . '\',
                    p_date_traitement=NOW()
                WHERE p_num = ' . (int) $demandeId;
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
    protected function updateSoldeUser($user, $duree, $typeId)
    {
        $sql = \includes\SQL::singleton();

        $req = 'UPDATE conges_solde_user
                    SET su_solde = su_solde-' .number_format($duree,2) . '
                    WHERE su_login = \''. \includes\SQL::quote($user) .'\'
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
                WHERE su_login = \''. \includes\SQL::quote($user) .'\'
                AND su_abs_id = '. (int) $typeId;
        $query = $sql->query($req);

        return $sql->affected_rows;
    }

     /**
      * {@inheritDoc}
      * @todo après refonte gestion des groupes retirer array_diff
      */
    protected function getIdDemandesResponsable($resp)
    {
        $groupId = \App\ProtoControllers\Responsable::getIdGroupeResp($resp);

        $usersResp = [];
        $usersResp = \App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds($groupId);

        // un utilisateur ne peut etre son propre responsable
        $usersResp = array_diff($usersResp,[$_SESSION['userlogin']]);

        if (empty($usersResp)) {
            return [];
        }

        $ids = [];
        foreach ($usersResp as $user) {
            $ids = array_merge($ids,\App\ProtoControllers\Employe\Conge::getIdDemandesUtilisateur($user));
        }
        return $ids;
    }


    /**
     * Transmet à respN+2 les id des demandes des utilisateurs d'un respN+1 absent
     *
     * @param string $resp login du respN+2
     *
     * @return array $ids
     */
    protected function getIdDemandesResponsableAbsent($resp)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        if (!$config->isGestionResponsableAbsent()) {
            return [];
        }
        $groupesIdResponsable = \App\ProtoControllers\Responsable::getIdGroupeResp($resp);

        $ids = [];
        $usersgroupesIdResponsable = [];
        $usersRespResp = [];
        $usersgroupesIdResponsable = \App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds($groupesIdResponsable);

        foreach ($usersgroupesIdResponsable as $user) {
            if (is_resp($user)) {
                $usersduRespResponsable[] = $user;
            }
        }
        if (empty($usersduRespResponsable)) {
            return [];
        }
        foreach ($usersduRespResponsable as $userduRespResponsable) {
            if (!\App\ProtoControllers\Responsable::isRespAbsent($userduRespResponsable)) {
                continue;
            }
            $allUsersResp = \App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds(\App\ProtoControllers\Responsable::getIdGroupeResp($userduRespResponsable));
            $ids = $this->getIdDemandeDelegable($allUsersResp);
        }
        return $ids;
    }

    /**
     * Retourne les id des demandes délégable
     *
     * @param array $usersRespAbsent
     * @return array $id
     */
    protected function getIdDemandeDelegable($usersRespAbsent)
    {
        $ids = [];
        foreach ($usersRespAbsent as $userResp) {
            $delegation = TRUE;
            $respsUser = \App\ProtoControllers\Responsable::getResponsablesUtilisateur($userResp);
            foreach ($respsUser as $respUser) {
                if (!\App\ProtoControllers\Responsable::isRespAbsent($respUser)) {
                    $delegation = FALSE;
            break;
                }
            }
            if ($delegation) {
                $ids = array_merge($ids, \App\ProtoControllers\Employe\Conge::getidDemandesUtilisateur($userResp));
            }
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
        $req = 'SELECT p_num AS id
                FROM conges_periode
                WHERE p_login IN (\'' . implode('\',\'', $usersResp) . '\')
                AND p_etat = \''. \App\Models\Conge::STATUT_PREMIERE_VALIDATION .'\'';
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

        foreach ($ListeDemande as $demande) {
            $infoDemande[$demande['p_num']] = $demande;
        }

        return $infoDemande;
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
        $req = 'SELECT su_reliquat FROM conges_solde_user
                WHERE su_login = \'' . \includes\SQL::quote($login) . '\'
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
        return ($statut != \App\Models\conge::STATUT_ANNUL || $statut != \App\Models\Conge::STATUT_VALIDATION_FINALE || $statut != \App\Models\Conge::STATUT_REFUS);
    }

   /**
     * verifie si la date limite d'usage des reliquats n'est pas dépassée
     *
     * @param int $findemande date de fin de la demande
     * @return bool
     */
    public function isReliquatUtilisable(\includes\SQL $sql)
    {
        $config = new \App\Libraries\Configuration($sql);
        $jourDemande = date("Y-m-d");
        if (0 === $config->getDateLimiteReliquats()) {
            return true;
        }
        return $jourDemande < $_SESSION['config']['date_limite_reliquats'];
    }

    /**
     * retourne le libellé d'un type d'absence
     *
     * @param int $type
     *
     * @return string $tLabel
     */
    public function getTypeLabel($type)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT ta_libelle FROM conges_type_absence WHERE ta_id = ' . $type;
        $query = $sql->query($req);
        $tLabel = $query->fetch_array()[0];

        return $tLabel;
    }

    /**
     * Retourne le nombre de demande en cours d'un responsable
     *
     * @param $resp
     *
     * @return int
     */
    public function getNbDemandesATraiter($resp)
    {
        $demandesResp = $this->getIdDemandesResponsable($resp);
        $demandesGResp = $this->getIdDemandesGrandResponsable($resp);
        $demandesDeleg = $this->getIdDemandesResponsableAbsent($resp);
        return count($demandesResp) + count($demandesGResp) + count($demandesDeleg);
    }
}
