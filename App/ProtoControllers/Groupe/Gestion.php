<?php

namespace App\ProtoControllers\Groupe;

class Gestion {

    /**
     * 
     * Traite la création ou la modification d'un groupe
     * 
     * @param array $post
     * @param array $errorLst
     * @return int
     */
    protected function postHtmlCommon(array $post, array &$errorLst)
    {
        $user = $_SESSION['userlogin'];
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
    protected function FormData2Array(array $post)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());

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

        if ($config->isDoubleValidationActive() && $post['new_group_double_valid'] == 'Y') {
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
     * 
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

            $rollback = !($updateEmployes && $updateEmployes && $updateEmployes);
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
     * @param type $idGroupe
     * @param type $nom
     * @param type $libelle
     * @param type $isDoubleValidation
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
     * @param type $idGroupe
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
     * @param type $idGroupe
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
     * @param type $idGroupe
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
     * 
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

        $query = $sql->query($req);

        return $sql->insert_id;
    }

    /**
     * affectation des employés dans un groupe
     * 
     * @param type $idGroupe
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
     * Formulaire de la liste des groupes
     * 
     * @return string
     */
    public function getFormListGroupe($message = '')
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());

        $errorsLst = [];
        $return = '';
        $return .= '<h1>' . _('admin_onglet_gestion_groupe') . '</h1>';

        if (!empty($_POST)) {
            if (0 >= (int) $this->postHtmlCommon($_POST, $errorsLst)) {
                $errors = '';
                if (!empty($errorsLst)) {
                    foreach ($errorsLst as $key => $value) {
                        if (is_array($value)) {
                            $value = implode(' / ', $value);
                        }
                        $errors .= '<li>' . $key . ' : ' . $value . '</li>';
                    }
                    $return .= '<div class="alert alert-danger">' . _('erreur_recommencer') . '<ul>' . $errors . '</ul></div>';
                }
            } else {
                $return .= '<div class="alert alert-info">' . _('Groupe supprimé') . '.</div>';
            }
        }

        if ("" !== $message) {
            $return .= '<div class="alert alert-info">' . $message . '.</div>';
        }
        $return .= '<a href=hr_index.php?onglet=ajout_groupe class="btn btn-success pull-right">' . _('admin_groupes_new_groupe') . '</a>';

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
        $childTable .= '<th>' . _('admin_groupes_groupe') . '</th>';
        $childTable .= '<th>' . _('admin_groupes_libelle') . '</th>';
        $childTable .= '<th>' . _('admin_groupes_nb_users') . '</th>';
        if ($config->isDoubleValidationActive()) {
            $childTable .= '<th>' . _('admin_groupes_double_valid') . '</th>';
        }
        $childTable .= '<th></th></tr></thead><tbody>';

        $groupes = new \App\ProtoControllers\Groupe();
        $i = true;
        foreach ($groupes->getListeGroupes(\includes\SQL::singleton()) as $gid => $groupe) {
            $nbUtilisateursGroupe = count(\App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds([$gid]));

            $childTable .= '<tr class="' . ($i ? 'i' : 'p') . '">';
            $childTable .= '<td><b>' . $groupe['g_groupename'] . '</b></td>';
            $childTable .= '<td>' . $groupe['g_comment'] . '</td>';
            $childTable .= '<td>' . $nbUtilisateursGroupe . '</td>';
            if ($config->isDoubleValidationActive()) {
                $childTable .= '<td>' . $groupe['g_double_valid'] . '</td>';
            }
            $childTable .= '<td class="action">';
            $childTable .= '<a href="hr_index.php?onglet=modif_groupe&group=' . $gid . '" title="' . _('form_modif') . '"><i class="fa fa-pencil"></i></a> ';
            $childTable .= '<a href="hr_index.php?onglet=suppr_groupe&group=' . $gid . '" title="' . _('form_supprim') . '"><i class="fa fa-times-circle"></i></a>';
            $childTable .= '</td></tr>';
            $i = !$i;
        }
        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<hr/>';

        return $return;
    }

    /**
     * 
     * Formulaire d'ajout ou de modification d'un groupe
     * 
     * @param int $idGroupe
     * @return string
     */
    public function getForm($idGroupe = NIL_INT)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());

        $return = '';
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $message = '';
        $infosGroupe = [
            'nom' => '',
            'doubleValidation' => '',
            'comment' => ''
        ];
        $data = NULL;

        $errorsLst = [];
        if (!empty($_POST)) {
            if (0 >= (int) $this->postHtmlCommon($_POST, $errorsLst)) {
                $errors = '';
                if (!empty($errorsLst)) {
                    foreach ($errorsLst as $key => $value) {
                        if (is_array($value)) {
                            $value = implode(' / ', $value);
                        }
                        $errors .= '<li>' . $key . ' : ' . $value . '</li>';
                    }
                    $message = '<br><div class="alert alert-danger">' . _('erreur_recommencer') . '<ul>' . $errors . '</ul></div>';
                }
                $data = $this->FormData2Array($_POST);
            } else {
                if (key_exists('_METHOD', $_POST)) {
                    redirect(ROOT_PATH . 'hr/hr_index.php?onglet=liste_groupe&notice=update', false);
                } else {
                    redirect(ROOT_PATH . 'hr/hr_index.php?onglet=liste_groupe&notice=insert', false);
                }
            }
        }

        if (isset($data)) {
            $infosGroupe = [
                'nom' => $data['nom'],
                'comment' => $data['commentaire']
            ];
            if ($config->isDoubleValidationActive()) {
                $infosGroupe['doubleValidation'] = $data['isDoubleValidation'];
            }
        } elseif (NIL_INT !== $idGroupe) {
            $infosGroupe = \App\ProtoControllers\Groupe::getInfosGroupe($idGroupe, \includes\SQL::singleton());
        }

        $selectId = uniqid();
        $DivGrandRespId = uniqid();
        $return .= '<div onload="showDivGroupeGrandResp(\'' . $selectId . '\',\'' . $DivGrandRespId . '\');" class="form-group">';
        if (NIL_INT !== $idGroupe) {
            $return .= '<h1>' . _('admin_modif_groupe_titre') . '</h1>';
        } else {
            $return .= '<h1>' . _('admin_groupes_new_groupe') . '</h1>';
        }

        $return .= $message;
        $return .= '<form method="post" action=""  role="form">';
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
        ]);
        $childTable = '<thead><tr>';
        $childTable .= '<th><b>' . _('Nom du groupe') . '</b></th>';
        $childTable .= '<th>' . _('admin_groupes_libelle') . ' / ' . _('divers_comment_maj_1') . '</th>';
        if ($config->isDoubleValidationActive()) {
            $childTable .= '<th>' . _('admin_groupes_double_valid') . '</th>';
        }
        $childTable .= '</tr></thead><tbody>';
        $childTable .= '<tr>';
        $childTable .= '<td><input class="form-control" type="text" name="new_group_name" size="30" maxlength="50" value="' . $infosGroupe['nom'] . '" required></td>';
        $childTable .= '<td><input class="form-control" type="text" name="new_group_libelle" size="50" maxlength="250" value="' . $infosGroupe['comment'] . '"></td>';
        if ($config->isDoubleValidationActive()) {
            $selectN = $infosGroupe['doubleValidation'] == 'N' ? 'selected="selected"' : '';
            $selectY = $infosGroupe['doubleValidation'] == 'Y' ? 'selected="selected"' : '';
            $childTable .= '<td><select class="form-control" name="new_group_double_valid" id="' . $selectId . '" onchange="showDivGroupeGrandResp(\'' . $selectId . '\',\'' . $DivGrandRespId . '\');"><option value="N" ' . $selectN . '>N</option><option value="Y" ' . $selectY . '>Y</option></select></td>';
        }
        $childTable .= '</tr></tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<hr>';

        $return .= '<div class="row">';
        $return .= '<div class="col-md-6">';
        $return .= '<h2>' . _('admin_gestion_groupe_users_membres') . '</h2>';
        $return .= $this->getFormChoixEmploye($idGroupe, $data);
        $return .= '</div>';

        $return .= '<div class="col-md-6">';
        $return .= '<h2>' . _('admin_gestion_groupe_resp_responsables') . '</h2>';
        $return .= $this->getFormChoixResponsable($idGroupe, $selectId, $data);
        $return .= '</div>';

        $return .= '<div class="col-md-6 hide" id="' . $DivGrandRespId . '">';
        $return .= '<h2>' . _('admin_gestion_groupe_grand_resp_responsables') . '</h2>';
        $return .= $this->getFormChoixGrandResponsable($idGroupe, $selectId, $data);
        $return .= '</div>';
        $return .= '</div>';

        $return .= '</div>';

        $return .= '<div class="form-group">';
        if (NIL_INT !== $idGroupe) {
            $return .= '<input type="hidden" name="_METHOD" value="PUT" />';
            $return .= '<input type="hidden" name="group" value="' . $idGroupe . '" />';
        }
        $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
        $return .= '<a class="btn" href="' . $PHP_SELF . '?onglet=liste_groupe">' . _('form_annul') . '</a>';
        $return .= '</div>';
        $return .= '</form>';

        return $return;
    }

    /**
     * 
     * Formulaire de selection des employés d'un groupe
     * 
     * @param int $id
     * @return string
     */
    protected function getFormChoixEmploye($idGroupe, $data = NULL)
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
    protected function getFormChoixGrandResponsable($idGroupe,$selectId, $data)
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
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());

        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return = '';


        $infosGroupe = \App\ProtoControllers\Groupe::getInfosGroupe($idGroupe, \includes\SQL::singleton());

        $return .= '<form method="post" action="' . $PHP_SELF . '?onglet=liste_groupe"  role="form">';
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
        if ($config->isDoubleValidationActive()) {
            $childTable .= '<th><b>' . _('admin_groupes_double_valid') . '</b></th>';
        }
        $childTable .= '</tr></thead><tbody><tr>';
        $childTable .= '<td>&nbsp;' . $infosGroupe['nom'] . '&nbsp;</td>';
        $childTable .= '<td>&nbsp;' . $infosGroupe['comment'] . '&nbsp;</td>';
        if ($config->isDoubleValidationActive()) {
            $childTable .= '<td>' . $infosGroupe['doubleValidation'] . '</td>';
        }
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
        $return .= '<a class="btn" href="hr_index.php?onglet=liste_groupe">' . _('form_cancel') . '</a>';
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

        if ('Y' == $data['isDoubleValidation']) {
            if ($this->isGrandResponsableEtAutre($data['employes'], $data['responsables'], $data['grandResponsables'])) {
                $errors[] = _('Le grand responsable ne peut pas etre membre ou responsable du groupe');
                $return = false;
            }
            if (!$this->isResponsableGroupeExist($data['responsables']) 
                || !$this->isGrandResponsableGroupeExist($data['grandResponsables'])) {
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
     * 
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
     * 
     * vérifie si l'utilisateur est autorisé à gérer les congés
     * 
     * @return boolean
     */
    protected function isAutorise()
    {
        return \App\ProtoControllers\Utilisateur::isRH($_SESSION['userlogin']);
    }

}
