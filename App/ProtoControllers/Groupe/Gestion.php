<?php

namespace App\ProtoControllers\Groupe;

class Gestion
{

    /**
     * Traite la création ou la modification d'un groupe
     *
     * @param array $post
     * @param array $errorLst
     * @return int
     */
    public function postHtmlCommon(array $post, array &$errorLst)
    {
        $data = $this->FormData2Array($post);
        $return = 1;

        if (!empty($post['_METHOD'])) {
            switch ($post['_METHOD']) {
                case 'DELETE':
                    if ($this->isAutorise()) {
                        if (NIL_INT !== $this->deleteGroupe($data['id'], $errorLst)) {
                            log_action(0, 'groupe', '', 'groupe ' . $data['nom'] . ' supprimé');
                        } else {
                            return NIL_INT;
                        }
                    } else {
                        $errorLst[] = _('non autorisé');
                        $return = NIL_INT;
                    }
                    break;
                case 'PUT':
                    if ($this->isValid($data, $errorLst) && NIL_INT !== $this->put($data, $errorLst)) {
                        log_action(0, 'groupe', '', 'groupe ' . $data['nom'] . ' modifié');
                    } else {
                        $return = NIL_INT;
                    }
                    break;
            }
        } else {
            if (!$this->isValid($data, $errorLst)) {
                $return = NIL_INT;
            } else {
                if ($this->isNomGroupeExist($data['nom'])) {
                    $errorLst[] = _('Ce nom de groupe existe déjà');
                    $return = NIL_INT;
                } else {
                    if (NIL_INT !== $this->post($data, $errorLst)) {
                        log_action(0, 'groupe', '', 'groupe ' . $data['nom'] . ' créé');
                    } else {
                        return NIL_INT;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * Conversion des données du formulaire pour import en BDD
     *
     * @param array $post
     * @return array $data
     */
    public function FormData2Array(array $post)
    {
        $data = [
            'grandResponsables' => [],
            'responsables' => [],
            'employes' => [],
        ];

        $data['nom'] = htmlentities($post['new_group_name'], ENT_QUOTES | ENT_HTML401);
        $data['commentaire'] = htmlentities($post['new_group_libelle'], ENT_QUOTES | ENT_HTML401);

        if (key_exists('group', $post)) {
            $data['id'] = (int) $post['group'];
        }

        if (key_exists('checkbox_group_users', $post)) {
            foreach (array_keys($post['checkbox_group_users']) as $employe) {
                $data['employes'][] = htmlentities($employe, ENT_QUOTES | ENT_HTML401);
            }
        }

        if (key_exists('checkbox_group_resps', $post)) {
            foreach (array_keys($post['checkbox_group_resps']) as $resp) {
                $data['responsables'][] = htmlentities($resp, ENT_QUOTES | ENT_HTML401);
            }
        }

        if ('Y' === $post['new_group_double_valid']) {
            $data['isDoubleValidation'] = 'Y';

            if (!empty($post['checkbox_group_grand_resps'])) {
                foreach (array_keys($post['checkbox_group_grand_resps']) as $grandresp) {
                    $data['grandResponsables'][] = htmlentities($grandresp, ENT_QUOTES | ENT_HTML401);
                }
            }
        } else {
            $data['isDoubleValidation'] = 'N';
        }

        return $data;
    }

    /**
     * Modifie un groupe
     *
     * @param array $put
     * @param array $errorLst
     */
    protected function put(array $data, array &$errorLst)
    {

        $return = 1;
        $sql = \includes\SQL::singleton();
        $sql->getPdoObj()->begin_transaction();

        if ($this->updateGroupe($data['id'], $data['nom'], $data['commentaire'], $data['isDoubleValidation'])) {
            $updateEmployes = $this->updateEmployeGroupe($data['id'], $data['employes']);
            $updateResponsables = $this->updateResponsableGroupe($data['id'], $data['responsables']);
            $updategrandResponsables = $this->updateGrandResponsableGroupe($data['id'], $data['grandResponsables']);

            $rollback = !($updateEmployes && $updateResponsables && $updategrandResponsables);
        } else {
            $rollback = true;
        }

        if ($rollback) {
            $sql->getPdoObj()->rollback();
            $errorLst[] = _('Une erreur inconnue s\'est produite.');
            $return = NIL_INT;
        }
        $sql->getPdoObj()->commit();

        return $return;
    }

    /**
     *
     * Mise à jour d'un groupe
     *
     * @param int $idGroupe
     * @param string $nom
     * @param string $libelle
     * @param string $isDoubleValidation
     * @return array
     */
    protected function updateGroupe($idGroupe, $nom, $libelle, $isDoubleValidation)
    {
        $sql = \includes\SQL::singleton();

        $req = 'UPDATE conges_groupe
                    SET g_groupename = "' . \includes\SQL::quote($nom) . '",
                        g_comment = "' . \includes\SQL::quote($libelle) . '",
                        g_double_valid = "' . \includes\SQL::quote($isDoubleValidation) . '"
                    WHERE g_gid = ' . $idGroupe;
        return $sql->query($req);
    }

    /**
     *
     * Mise à jour membre d'un groupe
     *
     * @param int $idGroupe
     * @param array $users
     * @return boolean
     */
    protected function updateEmployeGroupe($idGroupe, array $users)
    {
        $delete = $this->deleteEmployesGroupe($idGroupe);
        $insert = $this->insertEmployesGroupe($idGroupe, $users);

        return $delete && $insert;
    }

    /**
     *
     * Mise à jour responsable d'un groupe
     *
     * @param int $idGroupe
     * @param array $resps
     * @return boolean
     */
    protected function updateResponsableGroupe($idGroupe, array $resps)
    {
        $delete = $this->deleteResponsablesGroupe($idGroupe);
        $insert = $this->insertResponsablesGroupe($idGroupe, $resps);

        return $delete && $insert;
    }

    /**
     *
     * Mise à jour grand responsable d'un groupe
     *
     * @param int $idGroupe
     * @param array $grandResps
     * @return boolean
     */
    protected function updateGrandResponsableGroupe($idGroupe, array $grandResps)
    {
        $delete = $this->deleteGrandResponsablesGroupe($idGroupe);
        $insert = $this->insertGrandResponsablesGroupe($idGroupe, $grandResps);

        return $delete && $insert;
    }

    /**
     * Supprime un groupe
     *
     * @param int $idGroupe
     * @param array $errorLst
     * @return int
     */
    protected function deleteGroupe($idGroupe, array &$errorLst)
    {
        $sql = \includes\SQL::singleton();

        $sql->getPdoObj()->begin_transaction();
        $deleteEmployes = $this->deleteEmployesGroupe($idGroupe);
        $deleteResponsables = $this->deleteResponsablesGroupe($idGroupe);
        $deleteGrandResponsables = $this->deleteGrandResponsablesGroupe($idGroupe);

        $req = 'DELETE FROM conges_groupe
                    WHERE g_gid = ' . $idGroupe . ';';
        $deleteGroupe = $sql->query($req);

        $resultat = $deleteEmployes && $deleteResponsables && $deleteGrandResponsables && $deleteGroupe;
        if (!$resultat) {
            $sql->getPdoObj()->rollback();
            $errorLst[] = _('Une erreur inconnue s\'est produite.');
            return NIL_INT;
        }
        $sql->getPdoObj()->commit();

        return 1;
    }

    /**
     * Supprime les employés d'un groupe
     *
     * @param int $idGroupe
     * @return boolean
     */
    protected function deleteEmployesGroupe($idGroupe)
    {
        $sql = \includes\SQL::singleton();

        $req = 'DELETE FROM conges_groupe_users
                    WHERE gu_gid = ' . (int) $idGroupe . ';';

        return $sql->query($req);
    }

    /**
     * Supprime les responsables d'un groupe
     *
     * @param int $idGroupe
     * @return boolean
     */
    protected function deleteResponsablesGroupe($idGroupe)
    {
        $sql = \includes\SQL::singleton();

        $req = 'DELETE FROM conges_groupe_resp
                    WHERE gr_gid = ' . (int) $idGroupe . ';';

        return $sql->query($req);
    }

    /**
     * Supprime les grands responsables d'un groupe
     *
     * @param int $idGroupe
     * @return boolean
     */
    protected function deleteGrandResponsablesGroupe($idGroupe)
    {
        $sql = \includes\SQL::singleton();

        $req = 'DELETE FROM conges_groupe_grd_resp
                    WHERE ggr_gid = ' . (int) $idGroupe . ';';

        return $sql->query($req);
    }

    /**
     * Créer un nouveau groupe
     *
     * @param array $post
     * @param array $errorLst
     */
    protected function post(array $data, array &$errorLst)
    {

        $return = 1;
        $sql = \includes\SQL::singleton();
        $sql->getPdoObj()->begin_transaction();
        $rollback = false;

        $idGroupe = $this->insertGroupe($data['nom'], $data['commentaire'], $data['isDoubleValidation']);

        if (0 < $idGroupe) {
            $insertEmployes = $this->insertEmployesGroupe($idGroupe, $data['employes']);
            $insertReponsables = $this->insertResponsablesGroupe($idGroupe, $data['responsables']);
            $insertGrandResponsables = $this->insertGrandResponsablesGroupe($idGroupe, $data['grandResponsables']);
            $rollback = !($insertEmployes && $insertReponsables && $insertGrandResponsables);
        } else {
            $rollback = true;
        }

        if ($rollback) {
            $sql->getPdoObj()->rollback();
            $errorLst[] = _('Une erreur inconnue s\'est produite.');
            $return = NIL_INT;
        }
        $sql->getPdoObj()->commit();

        return $return;
    }

    /**
     *
     * Mise à jour d'un groupe
     *
     * @param string $nom
     * @param string $libelle
     * @param string $isDoubleValidation
     * @return int
     */
    protected function insertGroupe($nom, $libelle, $isDoubleValidation)
    {
        $sql = \includes\SQL::singleton();

        $req = 'INSERT INTO conges_groupe (g_gid,g_groupename,g_comment,g_double_valid)
                    VALUES  ("",
                        "' . \includes\SQL::quote($nom) . '",
                        "' . \includes\SQL::quote($libelle) . '",
                        "' . \includes\SQL::quote($isDoubleValidation) . '");';

        $sql->query($req);

        return $sql->insert_id;
    }

    /**
     * affectation des employés dans un groupe
     *
     * @param int $idGroupe
     * @param array $users
     * @return boolean
     */
    protected function insertEmployesGroupe($idGroupe, array $users)
    {

        $sql = \includes\SQL::singleton();

        $req = '';
        foreach ($users as $user) {
            $req .='INSERT INTO conges_groupe_users (gu_gid,gu_login)
                        VALUES (' . $idGroupe . ',"' . \includes\SQL::quote($user) . '");';
        }

        $return = $sql->multi_query($req);
        while ($sql->more_results() && $sql->next_result()) {
            ;
        }

        return $return;
    }

    /**
     * affectation des responsables dans un groupe
     *
     * @param int $idGroupe
     * @param array $users
     * @return boolean
     */
    protected function insertResponsablesGroupe($idGroupe, array $resps)
    {

        if (empty($resps)) {
            return true;
        }
        $sql = \includes\SQL::singleton();

        $req = '';
        foreach ($resps as $resp) {
            $req .='INSERT INTO conges_groupe_resp (gr_gid,gr_login)
                        VALUES (' . (int) $idGroupe . ',"' . \includes\SQL::quote($resp) . '");';
        }

        $return = $sql->multi_query($req);
        while ($sql->more_results() && $sql->next_result()) {
            ;
        }

        return $return;
    }

    /**
     * affectation des grands responsables dans un groupe
     *
     * @param int $idGroupe
     * @param array $users
     * @return boolean
     */
    protected function insertGrandResponsablesGroupe($idGroupe, array $grandResps)
    {

        if (empty($grandResps)) {
            return true;
        }

        $sql = \includes\SQL::singleton();

        $req = '';
        foreach ($grandResps as $grandResp) {
            $req .='INSERT INTO conges_groupe_grd_resp (ggr_gid,ggr_login)
                        VALUES (' . (int) $idGroupe . ',"' . \includes\SQL::quote($grandResp) . '");';
        }

        $return = $sql->multi_query($req);
        while ($sql->more_results() && $sql->next_result()) {
            ;
        }

        return $return;
    }

    /**
     *
     * Formulaire de selection des employés d'un groupe
     *
     * @param int $id
     * @return string
     */
    protected function getFormChoixEmploye($idGroupe, $data = null)
    {
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-condensed',
            'table-striped',
            'table-condensed',
        ]);

        $childTable = '<thead>';
        $childTable .= '<tr>';
        $childTable .= '<th></th>';
        $childTable .= '<th>' . _('divers_personne_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_login') . '</th>';
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';
        $i = true;
        foreach ($this->getEmployes($idGroupe) as $login => $info) {
            $inputOption = '';

            if (isset($data)) {
                if (in_array($login, $data['responsables']) || in_array($login, $data['grandResponsables'])) {
                    $inputOption = 'disabled';
                } elseif (in_array($login, $data['employes'])) {
                    $inputOption = 'checked';
                }
            } elseif (\App\ProtoControllers\Groupe::isResponsableGroupe($login, [$idGroupe], \includes\SQL::singleton())) {
                $inputOption = 'disabled';
            } elseif ($info['isDansGroupe']) {
                $inputOption = 'checked';
            }

            $childTable .= '<tr class="' . (($i) ? 'i' : 'p') . '">';
            $childTable .='<td class="histo"><input type="checkbox" id="Emp_' . $login . '" name="checkbox_group_users[' . $login . '] "' . $inputOption . '></td>';
            $childTable .= '<td class="histo">' . $info['nom'] . ' ' . $info['prenom'] . '</td>';
            $childTable .= '<td class="histo">' . $login . '</td>';
            $childTable .= '</tr>';
        }
        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return = ob_get_clean();

        return $return;
    }

    /**
     *
     * Formulaire de selection du responsable d'un groupe
     *
     * @param int $idGroupe
     * @return string
     */
    protected function getFormChoixResponsable($idGroupe, $selectId, $data)
    {
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-condensed',
            'table-striped',
        ]);
        $childTable = '<thead>';

        $childTable .= '<tr>';
        $childTable .= '<th>&nbsp;</th>';
        $childTable .= '<th>' . _('divers_personne_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_login') . '</th>';
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';
        $i = true;
        foreach ($this->getInfosResponsables($idGroupe) as $login => $info) {
            $inputOption = '';

            if (isset($data)) {
                if (in_array($login, $data['grandResponsables'])) {
                    $inputOption = 'disabled';
                } elseif (in_array($login, $data['responsables'])) {
                    $inputOption = 'checked';
                }
            } elseif ($info['isDansGroupe']) {
                $inputOption = 'checked';
            }

            $childTable .= '<tr class="' . (($i) ? 'i' : 'p') . '">';
            $childTable .='<td class="histo"><input type="checkbox" id="Resp_' . $login . '" name="checkbox_group_resps[' . $login . ']" onchange="disableCheckboxGroupe(this,\'' . $selectId . '\');" ' . $inputOption . '></td>';
            $childTable .= '<td class="histo">' . $info['nom'] . ' ' . $info['prenom'] . '</td>';
            $childTable .= '<td class="histo">' . $login . '</td>';
            $childTable .= '</tr>';
        }
        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return = ob_get_clean();

        return $return;
    }

    /**
     *
     * Formulaire de selection des grands responsables d'un groupe
     *
     * @param int $idGroupe
     * @return string
     */
    protected function getFormChoixGrandResponsable($idGroupe, $selectId, $data)
    {
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-condensed',
            'table-striped',
        ]);
        $childTable = '<thead>';

        $childTable .= '<tr>';
        $childTable .= '<th>&nbsp;</th>';
        $childTable .= '<th>' . _('divers_personne_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_login') . '</th>';
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';
        $i = true;
        foreach ($this->getGrandResponsables($idGroupe) as $login => $info) {
            $inputOption = '';

            if (isset($data)) {
                if (in_array($login, $data['grandResponsables'])) {
                    $inputOption = 'checked';
                }
            } elseif ($info['isDansGroupe']) {
                $inputOption = 'checked';
            }

            $childTable .= '<tr class="' . (($i) ? 'i' : 'p') . '">';
            $childTable .='<td class="histo"><input type="checkbox" id="Gres_' . $login . '" name="checkbox_group_grand_resps[' . $login . ']" onchange="disableCheckboxGroupe(this,\'' . $selectId . '\');"' . $inputOption . '></td>';
            $childTable .= '<td class="histo">' . $info['nom'] . ' ' . $info['prenom'] . '</td>';
            $childTable .= '<td class="histo">' . $login . '</td>';
            $childTable .= '</tr>';
        }
        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return = ob_get_clean();

        return $return;
    }

    /**
     * Formulaire de confirmation de suppression d'un groupe
     *
     * @param int $idGroupe
     * @return string
     */
    public function getFormConfirmSuppression($idGroupe)
    {
        $return = '';

        $infosGroupe = \App\ProtoControllers\Groupe::getInfosGroupe($idGroupe, \includes\SQL::singleton());

        $return .= '<form method="post" action="liste_groupe" role="form">';
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-striped',
            'table-condensed'
        ]);
        $childTable = '<thead>';
        $childTable .= '<tr>';
        $childTable .= '<th><b>' . _('admin_groupes_groupe') . '</b></th>';
        $childTable .= '<th><b>' . _('admin_groupes_libelle') . ' / ' . _('divers_comment_maj_1') . '</b></th>';
        $childTable .= '<th><b>' . _('admin_groupes_double_valid') . '</b></th>';
        $childTable .= '</tr></thead><tbody><tr>';
        $childTable .= '<td>&nbsp;' . $infosGroupe['nom'] . '&nbsp;</td>';
        $childTable .= '<td>&nbsp;' . $infosGroupe['comment'] . '&nbsp;</td>';
        $childTable .= '<td>' . $infosGroupe['doubleValidation'] . '</td>';
        $childTable .= '</tr></tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<hr/>';
        $return .= '<input type="hidden" name="_METHOD" value="DELETE" />';
        $return .= '<input type="hidden" name="group" value="' . $idGroupe . '" />';
        $return .= '<input type="hidden" name="new_group_name" value="' . $infosGroupe['nom'] . '" />';
        $return .= '<input type="hidden" name="new_group_libelle" value="' . $infosGroupe['comment'] . '" />';
        $return .= '<input type="hidden" name="new_group_double_valid" value="' . $infosGroupe['doubleValidation'] . '" />';
        $return .= '<input class="btn btn-danger" type="submit" value="' . _('form_supprim') . '">';
        $return .= '<a class="btn" href="liste_groupe">' . _('form_cancel') . '</a>';
        $return .= '</form>';

        return $return;
    }

    /**
     *
     * retournes les utilisateurs responsables
     * si $idGroupe existe, marquage des responsables du groupe
     *
     * @param int $idGroupe
     * @return array
     */
    protected function getInfosResponsables($idGroupe = NIL_INT)
    {
        $responsables = [];

        $infosResps = \App\ProtoControllers\Responsable::getInfosResponsables(\includes\SQL::singleton(),true);
        foreach ($infosResps as $infos) {
            $login = $infos['u_login'];
            $responsables[$login] = [
                'nom' => $infos['u_nom'],
                'prenom' => $infos['u_prenom'],
                'login' => $login,
                'isDansGroupe' => false
            ];

            if (NIL_INT !== $idGroupe) {
                $responsables[$login]['isDansGroupe'] = \App\ProtoControllers\Groupe::isResponsableGroupe($login, [$idGroupe], \includes\SQL::singleton());
            }
        }
        return $responsables;
    }

    /**
     *
     * retournes les utilisateurs responsables
     * si $idGroupe existe, marquage des grands responsables du groupe
     *
     * @param int $idGroupe
     * @return array
     */
    protected function getGrandResponsables($idGroupe = NIL_INT)
    {
        $responsables = [];

        $infosResps = \App\ProtoControllers\Responsable::getInfosResponsables(\includes\SQL::singleton(),true);
        foreach ($infosResps as $infos) {
            $responsables[$infos['u_login']] = [
                'nom' => $infos['u_nom'],
                'prenom' => $infos['u_prenom'],
                'login' => $infos['u_login'],
                'isDansGroupe' => false
            ];

            if (NIL_INT !== $idGroupe) {
                $responsables[$infos['u_login']]['isDansGroupe'] = \App\ProtoControllers\Groupe::isGrandResponsableGroupe($infos['u_login'], [$idGroupe], \includes\SQL::singleton());
            }
        }
        return $responsables;
    }

    /**
     *
     * retournes les utilisateurs
     * si $idGroupe existe, marquage des employés du groupe
     *
     * @param int $idGroupe
     * @return array
     */
    protected function getEmployes($idGroupe = NIL_INT)
    {
        $employes = [];
        $idsUtilisateurs = \App\ProtoControllers\Utilisateur::getListId(true);
        foreach ($idsUtilisateurs as $login) {
            $donnees = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($login);
            $employes[$login] = [
                'nom' => $donnees['u_nom'],
                'prenom' => $donnees['u_prenom'],
                'login' => $donnees['u_login'],
                'isDansGroupe' => false
            ];
            if (NIL_INT != $idGroupe) {
                $employes[$login]['isDansGroupe'] = \App\ProtoControllers\Groupe\Utilisateur::isUtilisateurDansGroupe($login, $idGroupe, \includes\SQL::singleton());
            }
        }
        return $employes;
    }

    /**
     *
     * Teste si le groupe et son contenu sont conformes
     *
     * @param array $data
     * @param array $errors
     * @return boolean
     *
     * @todo activer isResponsableCirculaire() une fois le responsable direct retiré
     *
     */
    protected function isValid(array $data, array &$errors)
    {

        $return = true;

        if (!$this->isAutorise()) {
            $errors[] = _('non autorisé');
            return false;
        }

        if ($this->isNomVide($data['nom'])) {
            $errors[] = _('Le nom du groupe est obligatoire');
            $return = false;
        }

        if (!$this->isEmployeGroupeExist($data['employes'])) {
            $errors[] = _('Le groupe doit contenir au moins un employé');
            $return = false;
        }

        if ($this->isResponsableEtEmploye($data['employes'], $data['responsables'])) {
            $errors[] = _('Le responsable ne peut pas etre membre du groupe');
            $return = false;
        }

        if ('Y' === $data['isDoubleValidation']) {
            if ($this->isGrandResponsableEtAutre($data['employes'], $data['responsables'], $data['grandResponsables'])) {
                $errors[] = _('Le grand responsable ne peut pas etre membre ou responsable du groupe');
                $return = false;
            }
            if (!$this->isResponsableGroupeExist($data['responsables'])
                || !$this->isGrandResponsableGroupeExist($data['grandResponsables'])
            ) {
                $errors[] = _('au moins un responsable et un grand responsable sont obligatoires');
                $return = false;
            }
        }

        return $return;
    }

    /**
     *
     * teste si le nom du groupe est vide
     *
     * @param string $nom
     * @return boolean
     */
    protected function isNomVide($nom)
    {
        return empty($nom);
    }

    /**
     * vérifie si le nom du groupe existe déja
     *
     * @param string $nomGroupe
     * @return boolean
     */
    protected function isNomGroupeExist($nomGroupe)
    {
        $nomsGroupes = [];
        $groupe = new \App\ProtoControllers\Groupe();
        $groupesIds = $groupe->getListeId(\includes\SQL::singleton());
        foreach ($groupesIds as $id) {
            $nomsGroupes[] = $groupe->getInfosGroupe($id, \includes\SQL::singleton())['nom'];
        }
        return in_array($nomGroupe, $nomsGroupes);
    }

    /**
     *
     * vérifie si il y a au moins un employé dans le groupe
     *
     * @param array $users
     * @return boolean
     */
    protected function isEmployeGroupeExist(array $users)
    {
        return !empty($users);
    }

    /**
     *
     * vérifie si le responsable est aussi employé dans le même groupe
     *
     * @param array $employes
     * @param array $responsables
     * @return boolean
     */
    protected function isResponsableEtEmploye(array $employes, array $responsables)
    {
        if (empty($responsables) || empty($employes)) {
            return false;
        }
        return !empty(array_intersect($employes, $responsables));
    }

    /**
     *
     * vérifie si le  grand responsable est aussi employé ou responsable dans le même groupe
     *
     * @param array $employes
     * @param array $responsables
     * @return boolean
     */
    protected function isGrandResponsableEtAutre(array $employes, array $responsables, array $grandResponsables)
    {
        if (empty($grandResponsables)) {
            return false;
        }
        return !empty(array_intersect_assoc($grandResponsables, $employes)) || !empty(array_intersect_assoc($grandResponsables, $responsables));
    }

    /**
     * Vérifie si un employé n'est pas responsable de son responsable
     *
     * @param array $employes
     * @param array $responsables
     * @return boolean
     */
    protected function isResponsableCirculaire(array $employes, array $responsables, array &$errors)
    {
        if (empty($responsables) || empty($employes)) {
            return false;
        }

        $employesResponsable = [];
        foreach ($employes as $employe) {
            if (\App\ProtoControllers\Utilisateur::isResponsable($employe)) {
                $employesResponsable[] = $employe;
            }
        }

        foreach ($employesResponsable as $employeResponsable) {
            foreach ($responsables as $responsable) {
                if (\App\ProtoControllers\Responsable::isRespDeUtilisateur($employeResponsable, $responsable)) {
                    $errors[] = $employeResponsable . _(' est responsable de ') . $responsable . _(' dans un autre groupe');
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     * vérifie si il y a au moins un responsable du groupe
     * obligatoire uniquement en cas de double validation
     *
     * @param array $responsables
     * @return boolean
     */
    protected function isResponsableGroupeExist(array $responsables)
    {
        return !empty($responsables);
    }

    /**
     *
     * vérifie si il y a au moins un grand responsable du groupe
     * uniquement en cas de double validation
     *
     * @param array $grandResponsables
     * @return boolean
     */
    protected function isGrandResponsableGroupeExist(array $grandResponsables)
    {
        return !empty($grandResponsables);
    }

    /**
     * vérifie si l'utilisateur est autorisé à gérer les congés
     *
     * @return boolean
     */
    protected function isAutorise()
    {
        return \App\ProtoControllers\Utilisateur::isRH($_SESSION['userlogin']);
    }
}
