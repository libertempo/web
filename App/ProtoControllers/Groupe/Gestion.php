<?php
namespace App\ProtoControllers\Groupe;

class Gestion {

    /**
     * 
     * Traite la création ou la modification d'un groupe
     * 
     * @param array $post
     * @param string $notice
     * @param array $errorLst
     * @return int
     */
    protected function postHtmlCommon(array $post, &$notice, array &$errorLst)
    {
        $user = $_SESSION['userlogin'];
        $data = $this->dataModel2Db($post);

        if (!empty($post['_METHOD'])) {
            switch ($post['_METHOD']) {
                case 'DELETE':
                    if($this->isAutorisee()){
                        $return = $this->delete($data['id'], $errorLst, $notice);
                    } else {
                        $errorLst[] = _('non autorisée');
                    }
                    break;
                case 'PUT':
                    if (!$this->isTraitable($data,$errorLst)) {
                        $return = NIL_INT;
                    } else {
                        $return = $this->put($data, $errorLst, $notice);
                    }
                    break;
            }
        } else {
            if (!$this->isTraitable($data,$errorLst)) {
                $return = NIL_INT;
            } else {
                if($this->isNomGroupeExist($data['nom'])){
                    $errorLst[] = _('erreur_nom_groupe_existe');
                    $return = NIL_INT;
                } else {
                    $return = $this->post($data, $errorLst, $notice);
                }
            }
        }
        return $return;
    }

    protected function dataModel2Db($post) {

        $data = [];
        $data['grandResponsables'] = [];
        $data['responsables'] = [];
        $data['employes'] = [];
        if(key_exists('group', $post)) {
            $data['id'] = (int) $post['group'];
        }
        $data['nom'] = htmlentities($post['new_group_name'], ENT_QUOTES | ENT_HTML401);
        $data['commentaire'] = htmlentities($post['new_group_libelle'], ENT_QUOTES | ENT_HTML401);
        if($_SESSION['config']['double_validation_conges']){
            switch ($post['new_group_double_valid']) {
                case 'Y':
                    $data['isDoubleValidation'] = 'Y';
                    break;

                default:
                    $data['isDoubleValidation'] = 'N';
                    break;
            }
        } else {
            $data['isDoubleValidation'] = 'N';
        }

        if(!empty($post['checkbox_group_users'])){
            foreach ($post['checkbox_group_users'] as $user => $value) {
                $data['employes'][] = htmlentities($user, ENT_QUOTES | ENT_HTML401);
            }
        }
        
        if(!empty($post['checkbox_group_resps'])){
            foreach ($post['checkbox_group_resps'] as $resp => $value) {
                $data['responsables'][] = $resp;
            }
        }
        
        if('Y' == $data['isDoubleValidation']){
            if(!empty($post['checkbox_group_grand_resps'])){
                foreach ($post['checkbox_group_grand_resps'] as $grandresp => $value) {
                    $data['grandResponsables'][] = $grandresp;
                }
            }
        }
        
        return $data;
    }
    /**
     * 
     * Modifie un groupe
     * 
     * @param array $put
     * @param array $errorLst
     * @param string $notice
     */
    public function put(array $data, array &$errorLst, &$notice)
    {

        $return = 1;
        $sql = \includes\SQL::singleton();
        $sql->getPdoObj()->begin_transaction();
        $rollback = false;
        
        if($this->updateGroupe($data['id'], $data['nom'], $data['commentaire'], $data['isDoubleValidation'])){
            $updateEmployes = $this->updateEmployeGroupe($data['id'], $data['employes']);
            $updateResponsables = $this->updateResponsableGroupe($data['id'], $data['responsables']);
            $updategrandResponsables = $this->updateGrandResponsableGroupe($data['id'], $data['grandResponsables']);
            $rollback = !($updateEmployes && $updateEmployes && $updateEmployes);
        } else {
            $rollback = true;
        }

        if ($rollback) {
            $sql->getPdoObj()->rollback();
            $errorLst[] = _('erreur_inconnue');
            $return = NIL_INT;
        }
        $sql->getPdoObj()->commit();
        
        return $return;
    }

    /**
     * 
     * Créer un nouveau groupe
     * 
     * @param array $post
     * @param array $errorLst
     * @param string $notice
     */
    public function post(array $data, array &$errorLst, &$notice) {

        $return = 1;
        $sql = \includes\SQL::singleton();
        $sql->getPdoObj()->begin_transaction();
        $rollback = false;

        $id = $this->insertGroupe($data['nom'], $data['commentaire'], $data['isDoubleValidation']);
        
        if(0 < $id){
            $insertEmployes = $this->insertEmployesGroupe($id, $data['employes']);
            $insertReponsables = $this->insertResponsableGroupe($id, $data['responsables']);
            $insertGrandResponsables = $this->insertGrandResponsableGroupe($id, $data['grandResponsables']);
            $rollback = !($insertEmployes && $insertReponsables && $insertGrandResponsables);
        } else {
            $rollback = true;
        }

        if ($rollback) {
            $sql->getPdoObj()->rollback();
            $errorLst[] = _('erreur_inconnue');
            $return = NIL_INT;
        }
        $sql->getPdoObj()->commit();
        
        return $return;
    }

    /**
     * 
     * Mise à jour d'un groupe
     * 
     * @param type $id
     * @param type $nom
     * @param type $libelle
     * @param type $isDoubleValidation
     * @return type
     */
    protected function updateGroupe($id,$nom,$libelle,$isDoubleValidation) {
        $sql = \includes\SQL::singleton();

        $req   = 'UPDATE conges_groupe 
                    SET g_groupename = "' . $nom . '",
                        g_comment = "'.$libelle.'",
                        g_double_valid = "'.$isDoubleValidation.'"
                    WHERE g_gid = ' . $id;
        return $sql->query($req);
    }

    /**
     * 
     * Mise à jour membre d'un groupe
     * 
     * @param type $id
     * @param array $users
     * @return type
     */
    protected function updateEmployeGroupe($id,array $users) {
        $sql = \includes\SQL::singleton();

        $req = 'DELETE FROM conges_groupe_users 
                    WHERE gu_gid = ' . $id .';';

        foreach ($users as $user) {
            $req .='INSERT INTO conges_groupe_users (gu_gid,gu_login) 
                        VALUES (' . $id . ',"' . $user . '");';
        }
        $return = $sql->multi_query($req);
        while ($sql->more_results() && $sql->next_result()) {;}

        return $return;
    }

    /**
     * 
     * Mise à jour responsable d'un groupe
     * 
     * @param type $id
     * @param array $resps
     * @return type
     */
    protected function updateResponsableGroupe($id,array $resps) {
        $sql = \includes\SQL::singleton();

        $req = 'DELETE FROM conges_groupe_resp 
                    WHERE gr_gid = ' . $id .';';

        foreach ($resps as $resp) {
            $req .='INSERT INTO conges_groupe_resp (gr_gid,gr_login) 
                        VALUES (' . $id . ',"' . $resp . '");';
        }
        $return = $sql->multi_query($req);
        while ($sql->more_results() && $sql->next_result()) {;}

        return $return;
    }

    /**
     * 
     * Mise à jour grand responsable d'un groupe
     * 
     * @param type $id
     * @param array $grandResps
     * @return type
     */
    protected function updateGrandResponsableGroupe($id, array $grandResps) {
        $sql = \includes\SQL::singleton();

        $req = 'DELETE FROM conges_groupe_grd_resp 
                    WHERE ggr_gid = ' . $id .';';

        foreach ($grandResps as $grandResp) {
            $req .='INSERT INTO conges_groupe_grd_resp (ggr_gid,ggr_login) 
                        VALUES (' . $id . ',"' . $grandResp . '");';
        }
        
        $return = $sql->multi_query($req);
        while ($sql->more_results() && $sql->next_result()) {;}

        return $return;
    }

    protected function insertGroupe($nom, $libelle, $isDoubleValidation) {
        $sql = \includes\SQL::singleton();

        $req   = 'INSERT INTO conges_groupe (g_gid,g_groupename,g_comment,g_double_valid)
                    VALUES  ("",
                        "' . $nom . '",
                        "'.$libelle.'",
                        "'.$isDoubleValidation.'");';
                
        $query = $sql->query($req);

        return $sql->insert_id;
    }

    protected function insertEmployesGroupe($id, array $users) {
        
        $sql = \includes\SQL::singleton();

        $req = '';
        foreach ($users as $user) {
            $req .='INSERT INTO conges_groupe_users (gu_gid,gu_login) 
                        VALUES (' . $id . ',"' . $user . '");';
        }
    
        $return = $sql->multi_query($req);
        while ($sql->more_results() && $sql->next_result()) {;}

        return $return;
    }

    protected function insertResponsableGroupe($id, array $resps) {

        if (empty($resps)){
            return true;
        }
        $sql = \includes\SQL::singleton();

        $req = '';
        foreach ($resps as $resp) {
            $req .='INSERT INTO conges_groupe_resp (gr_gid,gr_login) 
                        VALUES (' . $id . ',"' . $resp . '");';
        }
    
        $return = $sql->multi_query($req);
        while ($sql->more_results() && $sql->next_result()) {;}

        return $return;
    }

    protected function insertGrandResponsableGroupe($id, array $grandresps) {

        if (empty($resps)){
            return true;
        }

        $sql = \includes\SQL::singleton();

        $req = '';
        foreach ($grandresps as $grandresp) {
            $req .='INSERT INTO conges_groupe_grd_resp (ggr_gid,ggr_login) 
                        VALUES (' . $id . ',"' . $grandresp . '");';
        }
    
        $return = $sql->multi_query($req);
        while ($sql->more_results() && $sql->next_result()) {;}

        return $return;
    }

    /**
     * 
     * Formulaire de la liste des groupes
     * 
     * @return string
     */
    public function getFormListGroupe(){
        $session = session_id();
        $return = '';
        $return .= '<h1>' . _('admin_onglet_gestion_groupe') . '</h1>';
        $return .= '<h2>' . _('admin_gestion_groupe_etat') . '</h2>';
        $return .= '<a href=admin_index.php?session='. $session.'&onglet=ajout_group class="btn btn-success pull-right">' . _('admin_groupes_new_groupe') . '</a>';

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
        if($_SESSION['config']['double_validation_conges']) {
            $childTable .= '<th>' . _('admin_groupes_double_valid') . '</th>';
        }
        $childTable .= '<th></th></tr></thead><tbody>';

        $groupes = new \App\ProtoControllers\Groupe();
        $i = true;
        foreach ($groupes->getListe() as $gid => $groupe) {
            $nbUtilisateursGroupe = count(\App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds([$gid]));

            $childTable .= '<tr class="' . ($i ? 'i' : 'p') . '">';
            $childTable .= '<td><b>' . $groupe['g_groupename'] .'</b></td>';
            $childTable .= '<td>' . $groupe['g_comment'] . '</td>';
            $childTable .= '<td>' . $nbUtilisateursGroupe . '</td>';
            if($_SESSION['config']['double_validation_conges']) {
                $childTable .= '<td>' . $groupe['g_double_valid'] . '</td>';
            }
            $childTable .= '<td class="action">';
            $childTable .= '<a href="admin_index.php?onglet=modif_group&session=' . $session . '&group=' . $gid . '" title="' . _('form_modif') . '"><i class="fa fa-pencil"></i></a> ';
            $childTable .= '<a href="admin_index.php?onglet=suppr_group&session=' . $session . '&group=' . $gid . '" title="' . _('form_supprim') . '"><i class="fa fa-times-circle"></i></a>';
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
     * @param int $id
     * @return string
     */
    public function getForm($id = NIL_INT){
        $notice = '';
        $errorsLst  = [];
        $return = '';
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $session  = session_id();
        
        $infosGroupe = [
            'nom' => '',
            'doubleValidation' => '',
            'comment' => ''
        ];
        if (NIL_INT !== $id) {
            $infosGroupe = \App\ProtoControllers\Groupe::getInfosGroupe($id);
        }

        if (!empty($_POST)) {
            if (0 >= (int) $this->postHtmlCommon($_POST, $notice, $errorsLst)) {
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
                $infosGroupe =  [
                    'nom' => $_POST['new_group_name'],
                    'doubleValidation' => $_POST['new_group_double_valid'],
                    'comment' => $_POST['new_group_libelle']
                ];
            } else {
                log_action(0, 'groupe', '', 'groupe traité avec succès');
                redirect(ROOT_PATH . 'admin/admin_index.php?session='. session_id() . '&onglet=admin-group', false);
            }
        }

        $selectId = uniqid();
        $DivGrandRespId = uniqid();
        $return .= "<script>    
            function divGrandResp() { 
            if(document.getElementById('$selectId').value=='Y') { 
                document.getElementById('$DivGrandRespId').style.display='block'; 
            } else {
                document.getElementById('$DivGrandRespId').style.display='none'; 
            }
                return false;
        }   
        </script>";
        $return .= '<div class="form-group">';
        if (NIL_INT !== $id) {
            $return .= '<h1>' . _('admin_modif_groupe_titre') . '</h1>';
        } else {
            $return .= '<h1>' . _('admin_groupes_new_groupe') . '</h1>';
        }
        
        $return .= $notice;
        $return .= '<form method="post" action=""  role="form">';
        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
        ]);
        $childTable = '<thead><tr>';
        $childTable .= '<th><b>' . _('Nom du groupe') . '</b></th>';
        $childTable .= '<th>' . _('admin_groupes_libelle') . ' / ' . _('divers_comment_maj_1') . '</th>';
        if($_SESSION['config']['double_validation_conges']) {
            $childTable .= '<th>' . _('admin_groupes_double_valid') . '</th>';
        }
        $childTable .= '</tr></thead><tbody>';
        $childTable .= '<tr>';
        $childTable .= '<td><input class="form-control" type="text" name="new_group_name" size="30" maxlength="50" value="'. $infosGroupe['nom'].'" ></td>';
        $childTable .= '<td><input class="form-control" type="text" name="new_group_libelle" size="50" maxlength="250" value="'. $infosGroupe['comment'].'"></td>';
        if($_SESSION['config']['double_validation_conges']) {
            $selectN = $infosGroupe['doubleValidation'] == 'N' ? 'selected="selected"':'';
            $selectY = $infosGroupe['doubleValidation'] == 'Y' ? 'selected="selected"':'';
            $childTable .= '<td><select class="form-control" name="new_group_double_valid" id="'. $selectId .'" onchange="showDivGroupeGrandResp(\''. $selectId .'\',\''. $DivGrandRespId .'\');"><option value="N" '.$selectN.'>N</option><option value="Y" '.$selectY .'>Y</option></select></td>';
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
        $return .= $this->getFormChoixEmploye($id);
        $return .= '</div>';

        $return .= '<div class="col-md-6">';
        $return .= '<h2>' . _('admin_gestion_groupe_resp_responsables') . '</h2>';
        $return .= $this->getFormChoixResponsable($id);
        $return .= '</div>';

        $divGrandRespCache = $infosGroupe['doubleValidation'] == 'Y' ? 'style="display:block;"':'style="display:none;"';

        $return .= '<div class="col-md-6"  ' . $divGrandRespCache . ' id="'. $DivGrandRespId .'">';
        $return .= '<h2>' . _('admin_gestion_groupe_grand_resp_responsables') . '</h2>';
        $return .= $this->getFormChoixGrandResponsable($id);
        $return .= '</div>';
        $return .= '</div>';
        
        $return .= '</div>';
        
        $return .= '<div class="form-group">';
        if (NIL_INT !== $id) {
            $return .= '<input type="hidden" name="_METHOD" value="PUT" />';
            $return .= '<input type="hidden" name="group" value="' . $id . '" />';
        }
        $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
        $return .= '<a class="btn" href="' . $PHP_SELF . '?session=' . $session . '&onglet=admin-group">' . _('form_annul') . '</a>';
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
    public function getFormChoixEmploye($id) {
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
        foreach ($this->getEmployes($id) as $login => $info){
            $childTable .= '<tr class="' . (($i) ? 'i' :'p') . '">';
            $childTable .='<td class="histo"><input type="checkbox" name="checkbox_group_users['.$login.'] "' . (($info['isDansGroupe']) ? 'checked' : '') . '></td>';
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
     * @param int $id
     * @return string
     */
    public function getFormChoixResponsable($id) {
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
        foreach ($this->getResponsables($id) as $login => $info){
            $childTable .= '<tr class="' . (($i) ? 'i' :'p') . '">';
            $childTable .='<td class="histo"><input type="checkbox" name="checkbox_group_resps['.$login.']"' . (($info['isDansGroupe']) ? 'checked' : '') . '></td>';
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
     * @param int $id
     * @return string
     */
    public function getFormChoixGrandResponsable($id) {
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
        foreach ($this->getGrandResponsables($id) as $login => $info){
            $childTable .= '<tr class="' . (($i) ? 'i' :'p') . '">';
            $childTable .='<td class="histo"><input type="checkbox" name="checkbox_group_grand_resps['.$login.']"' . (($info['isDansGroupe']) ? 'checked' : '') . '></td>';
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
     * retournes les utilisateurs responsables
     * si $id, marquage des responsables du groupe $id
     * 
     * @param int $id
     * @return array
     */
    public function getResponsables($id = NIL_INT){
        $infoResponsables = [];
        
        $respsLogin = \App\ProtoControllers\Responsable::getListResponsable();
        foreach ($respsLogin as $login) {
            $donnees = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($login);
            $responsables[$login] = [
                'nom' => $donnees['u_nom'],
                'prenom' => $donnees['u_prenom'],
                'login' => $donnees['u_login'],
                'isDansGroupe' => false
            ];
            
            if(NIL_INT !== $id){
                $responsables[$login]['isDansGroupe'] = \App\ProtoControllers\Groupe::isResponsableGroupe($login,[$id]);
            }
        }
        return $responsables;
    }

    /**
     * 
     * retournes les utilisateurs responsables
     * si $id, marquage des grands responsables du groupe $id
     * 
     * @param int $id
     * @return array
     */
    public function getGrandResponsables($id = NIL_INT){
        $infoResponsables = [];
        
        $respsLogin = \App\ProtoControllers\Responsable::getListResponsable();
        foreach ($respsLogin as $login) {
            $donnees = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($login);
            $responsables[$login] = [
                'nom' => $donnees['u_nom'],
                'prenom' => $donnees['u_prenom'],
                'login' => $donnees['u_login'],
                'isDansGroupe' => false
            ];
            
            if(NIL_INT !== $id){
                $responsables[$login]['isDansGroupe'] = \App\ProtoControllers\Groupe::isGrandResponsableGroupe($login,[$id]);
            }
        }
        return $responsables;
    }

    /**
     * 
     * retournes les utilisateurs 
     * si $id, marquage des employés du groupe $id
     * 
     * @param int $idGroupe
     * @return array
     */
    public function getEmployes($idGroupe = NIL_INT){
        $infoUtilisateurs = [];
        $idsUtilisateurs = \App\ProtoControllers\Utilisateur::getListId();
        foreach ($idsUtilisateurs as $login) {
            $donnees = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($login);
            $employes[$login] = [
                'nom' => $donnees['u_nom'],
                'prenom' => $donnees['u_prenom'],
                'login' => $donnees['u_login'],
                'isDansGroupe' => false
            ];
            if(NIL_INT != $idGroupe){
                $employes[$login]['isDansGroupe'] = \App\ProtoControllers\Groupe\Utilisateur::isUtilisateurDansGroupe($login,$idGroupe);
            }
        }
        return $employes;
    }

    public function isTraitable($data,array &$errors) {
        
        $return = true;
        
        if(!$this->isAutorisee()){
            $errors[] = _('non autorisée');
            return false;
        }

        if($this->isNomVide($data['nom'])) {
            $errors[] = _('erreur_nom_vide');
            $return = false;
        }
        if(!$this->isEmployeGroupeExist($data['employes'])){
            $errors[] = _('erreur_groupe_employe');
            $return = false;
        }

        if($this->isResponsableEtEmploye($data['employes'],$data['responsables'])){
            $errors[] = _('erreur_responsable_employe_groupe_interdit');
            $return = false;
        }
        if($this->isResponsableCirculaire($data['employes'],$data['responsables'])){
            $errors[] = _('erreur_responsabilité circulaire');
            $return = false;
        }
        if('Y' == $data['isDoubleValidation']){
            if(!$this->isResponsableGroupeExist($data['responsables']) || !$this->isGrandResponsableGroupeExist($data['grandResponsables'])){
                $errors[] = _('erreur un responsable et un grand responsable sont obligatoire');
                $return = false;
            }
        }
        
        return $return;
    }

    protected function isNomVide($nom) {
        return empty($nom);
    }

    public function isNomGroupeExist($nomGroupe) {
        $nomsGroupes = [];
        $groupe = new \App\ProtoControllers\Groupe();
        $groupesIds = $groupe->getListeId();
        foreach ($groupesIds as $id) {
            $nomsGroupes[] = $groupe->getInfosGroupe($id)['nom'];
        }
        return in_array($nomGroupe, $nomsGroupes);
    }
    
    public function isEmployeGroupeExist(array $users) {
        return !empty($users);
    }
    
    public function isResponsableEtEmploye(array $employes, array $responsables) {
        if (empty($responsables) || empty($employes)){
            return false;
        }
        return !empty(array_intersect_assoc($employes, $responsables));
    }
    
    public function isResponsableCirculaire(array $employes, array $responsables) {
        $return = false;
        if (empty($responsables) || empty($employes)){
            return $return;
        }
        
        foreach ($responsables as $responsable) {
            foreach ($employes as $employe) {
                if ($employe == $responsable){
                    $return = true;
                break;
                }
            }
        }
    
        return $return;
    }
    
    public function isResponsableGroupeExist(array $responsables) {
        return !empty($responsables);
    }
    
    public function isGrandResponsableGroupeExist(array $grandResponsables) {
        return !empty($grandResponsables);
    }
    
    protected function isAutorisee() {
        return is_admin($_SESSION['userlogin']);
    }
}
