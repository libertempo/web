<?php
namespace App\ProtoControllers\HautResponsable;

/**
 * ProtoContrôleur de gestion des utilisateurs, en attendant la migration vers le MVC REST
 *
 * @since  1.11
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina <wouldsmina@gmail.com>
 */
class Utilisateur
{

    /**
     * gestion des utilisateurs
     *
     * @return string
     * @access public
     * @static
     */
    public static function getFormListeUsers($message)
    {
        $sql = \includes\SQL::singleton();
        $config = new \App\Libraries\Configuration($sql);
        $return = '';

        if (NIL_INT !== $message) {
            $return .= '<div class="alert alert-info">' . $message . '.</div>';
        }
        $return .= '<a href="' . ROOT_PATH . 'hr/hr_index.php?onglet=ajout_user" style="float:right" class="btn btn-success">' . _('admin_onglet_add_user') . '</a>';
        $return .= '<h1>' . _('admin_onglet_gestion_user') . '</h1>';

        $typeAbsencesConges = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges');
        $typeAbsencesExceptionnels = [];

        if ($config->isCongesExceptionnelsActive()) {
            $typeAbsencesExceptionnels = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges_exceptionnels');
        }

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
        $childTable .= '<th>' .  _('user') . '</th>';
        $childTable .= '<th>' . _('divers_quotite_maj_1') . '</th>';
        foreach ($typeAbsencesConges as $infoType) {
            $childTable .= '<th>' . $infoType['libelle'] . ' / ' . _('divers_an') . '</th>';
            $childTable .= '<th>' . _('divers_solde') . ' ' . $infoType['libelle'] . '</th>';
        }

        foreach ($typeAbsencesExceptionnels as $infoType) {
            $childTable .= '<th>' . _('divers_solde') . ' ' . $infoType['libelle'] . '</th>';
        }

        if ($config->isHeuresAutorise()) {
            $childTable .= '<th>' . _('divers_solde') . ' ' . _('heures') . '</th>';
        }

        $childTable .= '<th></th>';
        $childTable .= '<th></th>';
        if (($config->getHowToConnectUser() == "dbconges")) {
            $childTable .= '<th></th>';
        }
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';

        $infoUsers = \App\ProtoControllers\Utilisateur::getDonneesTousUtilisateurs($config);
        asort($infoUsers);
        uasort($infoUsers, ['self','sortParActif']);
        $i = true;
        foreach ($infoUsers as $login => $infosUser) {

            $childTable .= '<tr class="' . (($infosUser['u_is_active']=='Y') ? 'actif' : 'inactif') . '">';
            $childTable .= '<td class="utilisateur"><strong>' . $infosUser['u_nom'] . ' ' . $infosUser['u_prenom'] . '</strong>';
            $childTable .= '<span class="login">' . $login . '</span>';
            $childTable .= '<span class="mail">' . $infosUser['u_email'] . '</span>';

            $rights = [];
            if ($infosUser['u_is_active'] == 'N') {
                $rights[] = 'inactif';
            }
            if ($infosUser['u_is_admin'] == 'Y') {
                $rights[] = 'administrateur';
            }
            if ($infosUser['u_is_resp'] == 'Y') {
                $rights[] = 'responsable';
            }
            if ($infosUser['u_is_hr'] == 'Y') {
                $rights[] = 'RH';
            }

            if (count($rights) > 0) {
                $childTable .= '<span class="rights">' . implode(', ', $rights) . '</span>';
            }

            $responsables = \App\ProtoControllers\Responsable::getResponsablesUtilisateur($login);
            $childTable .= '<span class="responsable"> responsables : <strong>' . implode(', ', $responsables) . '</strong></span>';

            $childTable .= '</td><td>' . $infosUser['u_quotite'] . ' %</td>';

            $soldesByType = \App\ProtoControllers\Utilisateur::getSoldesEmploye($sql, $config, $login);

            foreach ($typeAbsencesConges as $congesId => $infoType) {
                if (isset($soldesByType[$congesId])) {
                    $childTable .= '<td>' . $soldesByType[$congesId]['su_nb_an'] . '</td>';
                    $childTable .= '<td>' . $soldesByType[$congesId]['su_solde'] . '</td>';
                } else {
                    $childTable .= '<td>0</td>';
                    $childTable .= '<td>0</td>';
                }
            }

            foreach ($typeAbsencesExceptionnels as $congesId => $infoType) {
                if (isset($soldesByType[$congesId])) {
                    $childTable .= '<td>' . $soldesByType[$congesId]['su_solde'] . '</td>';
                } else {
                    $childTable .= '<td>0</td>';
                }
            }
            if ($config->isHeuresAutorise()) {
                $childTable .= '<td>' . \App\Helpers\Formatter::timestamp2Duree($infosUser['u_heure_solde']) . '</td>';
            }

            $childTable .= "<td><a href=\"hr_index.php?onglet=traite_user&user_login=$login\" title=\""._('resp_etat_users_afficher')."\"><i class=\"fa fa-eye\"></i></a></td>" ;
            $childTable .= "<td><a href=\"../edition/edit_user.php?user_login=$login\" target=\"_blank\" title=\""._('resp_etat_users_imprim')."\"><i class=\"fa fa-file-text\"></i></a></td>";
            $childTable .= '<td><a href="hr_index.php?onglet=modif_user&login=' . $login . '" title="' . _('form_modif') . '"><i class="fa fa-pencil"></i></a></td>';
            $childTable .= '<td><a href="hr_index.php?onglet=suppr_user&login=' . $login . '" title="' . _('form_supprim') . '"><i class="fa fa-times-circle"></i></a></td>';
            $childTable .= '</tr>';
            $i = !$i;
        }

        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<br>';

        return $return;
    }

    /**
     * formulaire d'ajout / modification d'un utilisateur
     *
     * @param int $userId
     *
     * @return string
     */
    public static function getFormUser($userId = NIL_INT)
    {
        $return    = '';
        $message   = '';
        $errorsLst = [];
        $notice    = '';
        $sql = \includes\SQL::singleton();
        $config = new \App\Libraries\Configuration($sql);
        $formValue = [
            'login' => '',
            'nom' => '',
            'prenom' => '',
            'quotite' => '100',
            'soldeHeure' => '00:00',
            'isResp' => 'N',
            'isAdmin' => 'N',
            'isHR' => 'N',
            'isActive' => 'Y',
            'email' => '',
            'pwd1' => '',
            'pwd2' => '',
        ];

        if (NIL_INT !== $userId) {
            $userInfo = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($userId);
            $formValue = [
                'login' => $userInfo['u_login'],
                'nom' => $userInfo['u_nom'],
                'prenom' => $userInfo['u_prenom'],
                'quotite' => $userInfo['u_quotite'],
                'soldeHeure' => \App\Helpers\Formatter::Timestamp2Duree($userInfo['u_heure_solde']),
                'isResp' => $userInfo['u_is_resp'],
                'isAdmin' => $userInfo['u_is_admin'],
                'isHR' => $userInfo['u_is_hr'],
                'isActive' => $userInfo['u_is_active'],
                'email' => $userInfo['u_email'],
                'pwd1' => '',
                'pwd2' => '',
            ];
        }
        if (!empty($_POST)) {
            $formValue = static::dataForm2Array($_POST, $sql, $config);
            if (static::postFormUtilisateur($formValue, $errorsLst, $notice)) {
                redirect(ROOT_PATH . 'hr/hr_index.php?onglet=page_principale&notice=' . $notice, false);
            } else {
                if (!empty($errorsLst)) {
                    $errors = '';
                    foreach ($errorsLst as $key => $value) {
                        $errors .= '<li>' . $key . ' : ' . $value . '</li>';
                    }
                    $message = '<div class="alert alert-danger">' . _('erreur_recommencer') . ' :<ul>' . $errors . '</ul></div>';
                }
            }
        }

        if (NIL_INT !== $userId) {
            $return .= '<h1>' . _('Modification utilisateur') . '</h1>';
        } else {
            $return .= '<h1>' . _('Nouvel Utilisateur') . '</h1>';
        }
        $return .= $message;

        $return .= '<form id="manageUser" action="" method="post" accept-charset="UTF-8"
enctype="application/x-www-form-urlencoded" class="form-group">';
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
        $childTable .= '<th>' . _('Identifiant') . '</th>';
        $childTable .= '<th>' . _('Nom') . '</th>';
        $childTable .= '<th>' . _('Prénom') . '</th>';
        $childTable .= '<th>' . _('Quotité') . '</th>';
        if ($config->isHeuresAutorise()) {
            $childTable .= '<th>' . _('solde d\'heure') . '</th>';
        }
        $childTable .= '<th>' . _('Responsable?') . '</th>';
        $childTable .= '<th>' . _('Administrateur?') . '</th>';
        $childTable .= '<th>' . _('Haut responsable?') . '</th>';
        $childTable .= '<th>' . _('activé?') . '</th>';
        if (!$config->isUsersExportFromLdap()) {
            $childTable .= '<th>' . _('Email') . '</th>';
        }
        if ($config->getHowToConnectUser() == "dbconges") {
            $childTable .= '<th>' . _('mot de passe') . '</th>';
            $childTable .= '<th>' . _('ressaisir mot de passe') . '</th>';
        }
        $childTable .= '</tr></thead><tbody>';
        $soldeHeureId = uniqid();
        $readOnly = '';
        $optLdap = '';
        if ($config->isUsersExportFromLdap()) {
            $readOnly = 'readonly';
            $optLdap = 'onkeyup="searchLdapUser()" autocomplete="off"';
        }

        $childTable .= '<tr class="update-line">';

        $childTable .= "<td><input class=\"form-control\" type=\"text\" name=\"new_login\" size=\"10\" maxlength=\"99\" value=\"".$formValue['login']."\" " . $readOnly . " required></td>" ;
        $childTable .= "<td><input class=\"form-control\" type=\"text\" id=\"new_nom\" name=\"new_nom\" size=\"10\" maxlength=\"30\" value=\"".$formValue['nom']."\" " . $optLdap . " required>
                        <ul class=\"suggestions\" id=\"suggestions\"></ul></td>" ;
        $childTable .= "<td><input class=\"form-control\" type=\"text\" name=\"new_prenom\" size=\"10\" maxlength=\"30\" value=\"".$formValue['prenom']."\" " . $readOnly . " required></td>" ;
        $childTable .= "<td><input class=\"form-control\" type=\"text\" name=\"new_quotite\" size=\"3\" maxlength=\"3\" value=\"".$formValue['quotite']."\" required></td>" ;

        if ($config->isHeuresAutorise()) {
            $childTable .= "<td><input class=\"form-control\" type=\"text\" name=\"new_solde_heure\" id=\"" . $soldeHeureId . "\" size=\"6\" maxlength=\"6\" value=\"".$formValue['soldeHeure']."\"></td>" ;
        } else {
            $childTable .= "<input class=\"form-control\" type=\"hidden\" name=\"new_solde_heure\" id=\"" . $soldeHeureId . "\" size=\"6\" maxlength=\"6\" value=\"0\">" ;
        }
        $childTable .= "<td><select class=\"form-control\" name=\"new_is_resp\" >
                        <option value=\"N\" " . ($formValue['isResp'] == 'N' ? 'selected' : '') . ">N</option>
                        <option value=\"Y\" " . ($formValue['isResp'] == 'Y' ? 'selected' : '') . ">Y</option></select></td>" ;
        $childTable .= "<td><select class=\"form-control\" name=\"new_is_admin\" >
                        <option value=\"N\" " . ($formValue['isAdmin'] == 'N' ? 'selected' : '') . ">N</option>
                        <option value=\"Y\" " . ($formValue['isAdmin'] == 'Y' ? 'selected' : '') . ">Y</option></select></td>" ;
        $childTable .= "<td><select class=\"form-control\" name=\"new_is_hr\" >
                        <option value=\"N\" " . ($formValue['isHR'] == 'N' ? 'selected' : '') . ">N</option>
                        <option value=\"Y\" " . ($formValue['isHR'] == 'Y' ? 'selected' : '') . ">Y</option></select></td>" ;
        $childTable .= "<td><select class=\"form-control\" name=\"new_is_active\" >
                        <option value=\"Y\" " . ($formValue['isActive'] == 'Y' ? 'selected' : '') . ">Y</option>
                        <option value=\"N\" " . ($formValue['isActive'] == 'N' ? 'selected' : '') . ">N</option></select></td>" ;

        if (!$config->isUsersExportFromLdap()) {
            $childTable .= "<td><input class=\"form-control\" type=\"text\" name=\"new_email\" size=\"10\" maxlength=\"99\" value=\"".$formValue['email']."\"></td>" ;
        }
        if ($config->getHowToConnectUser() == "dbconges") {
            $childTable .= "<td><input class=\"form-control\" type=\"password\" name=\"new_password1\" size=\"10\" maxlength=\"15\" value=\"\" autocomplete=\"off\" required></td>" ;
            $childTable .= "<td><input class=\"form-control\" type=\"password\" name=\"new_password2\" size=\"10\" maxlength=\"15\" value=\"\" autocomplete=\"off\" required></td>" ;
        }
        $childTable .= '</tr></tbody>';
        $childTable .= '<script type="text/javascript">generateTimePicker("' . $soldeHeureId . '");</script>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<br><hr>';

        $return .= static::getFormUserSoldes($formValue, $userId);
        $return .= '<br><hr>';

        if (NIL_INT !== $userId) {
            $return .= '<input type="hidden" name="_METHOD" value="PUT" />';
            $return .= '<input type="hidden" name="old_login" value="' . $userId . '" />';
        } else {
            $return .= \App\ProtoControllers\HautResponsable\Utilisateur::getFormUserGroupes($formValue);
            $return .= '<hr>';
        }
        
        $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
        $return .= ' <a class="btn btn-default" href="hr_index.php?onglet=page_principale">' . _('form_cancel') . '</a>';
        $return .= '</form>';

        return $return;
    }

    /**
     * formulaire de gestion des soldes d'un utilisateur
     * 
     * @param array $data
     * @param int $userId
     * 
     * @return string 
     * 
     */
    private static function getFormUserSoldes($data, $userId)
    {
        $sql = \includes\SQL::singleton();
        $config = new \App\Libraries\Configuration($sql);
        $typeAbsencesConges = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges');
        if (NIL_INT !== $userId) {
            $soldesByType = \App\ProtoControllers\Utilisateur::getSoldesEmploye($sql, $config, $userId);
            foreach ($soldesByType as $typeId => $infos) {
                $data['joursAn'][$typeId] = $infos['su_nb_an'];
                $data['soldes'][$typeId] = $infos['su_solde'];
                $data['reliquat'][$typeId] = $infos['su_reliquat'];
            }
        }
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
        $childTable .= '<th colspan=3><h4>' . _('Soldes') . '</h4></th>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<th></th>';
        $childTable .= '<th>' . _('admin_new_users_nb_par_an') . '</th>';
        $childTable .= '<th>' . _('divers_solde') . '</th>';
        $childTable .= '<th>' . _('Reliquat') . '</th>';
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';

        $i = true;
        foreach ($typeAbsencesConges as $typeId => $infoType) {
            $childTable .= '<tr class="'.($i?'i':'p').'">';
            $joursAn = ( isset($data['joursAn'][$typeId]) ? $data['joursAn'][$typeId] : 0 );
            $solde = ( isset($data['soldes'][$typeId]) ? $data['soldes'][$typeId] : 0 );
            $reliquat = ( isset($data['reliquat'][$typeId]) ? $data['reliquat'][$typeId] : 0 );
            $childTable .= '<td>' . $infoType['libelle'] . '</td>';
            $childTable .= "<td><input class=\"form-control\" type=\"text\" name=\"tab_new_jours_an[$typeId]\" size=\"5\" maxlength=\"5\" value=\"$joursAn\"></td>" ;
            $childTable .= "<td><input class=\"form-control\" type=\"text\" name=\"tab_new_solde[$typeId]\" size=\"5\" maxlength=\"5\" value=\"$solde\"></td>" ;
            $childTable .= "<td><input class=\"form-control\" type=\"text\" name=\"tab_new_reliquat[$typeId]\" size=\"5\" maxlength=\"5\" value=\"$reliquat\"></td>" ;
            $childTable .= '</tr>';
            $i = !$i;
        }
        if ($config->isCongesExceptionnelsActive()) {
            $typeAbsencesExceptionnels = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges_exceptionnels');
            foreach ($typeAbsencesExceptionnels as $typeId => $infoType) {
                $childTable .= '<tr class="'.($i?'i':'p').'">';
                $solde = ( isset($data['soldes'][$typeId]) ? $data['soldes'][$typeId] : 0 );
                $childTable .= '<td>'.  $infoType['libelle'] . '</td>';
                $childTable .= "<td><input type=\"hidden\" name=\"tab_new_jours_an[$typeId]\" size=\"5\" maxlength=\"5\" value=\"0\"></td>" ;
                $childTable .= "<td><input class=\"form-control\" type=\"text\" name=\"tab_new_solde[$typeId]\" size=\"5\" maxlength=\"5\" value=\"$solde\"></td>" ;
                $childTable .= "<td><input type=\"hidden\" name=\"tab_new_reliquat[$typeId]\" size=\"5\" maxlength=\"5\" value=\"0\"></td>" ;
                $childTable .= '</tr>';
                $i = !$i;
            }
        }
        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return = ob_get_clean();
        $return .= '<br>';

        return $return;
    }

    /**
     * Formulaire d'affectation aux groupes pour un nouvel utilisateur
     * 
     * @param array $data
     * @return string
     */
    private static function getFormUserGroupes($data)
    {
        $sql = \includes\SQL::singleton();
        $return = '';
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
        $childTable .= '<th colspan=3><h4>' . _('Groupes') . '</h4></th>';
        $childTable .= '</tr>';

        $childTable .= '<tr>';
        $childTable .= '<th>&nbsp;</th>';
        $childTable .= '<th>&nbsp;' . _('Nom') . '</th>';
        $childTable .= '<th>&nbsp;' . _('Description') . '</th>';
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';

        $groupes = \App\ProtoControllers\Groupe::getListeGroupes($sql);

        $i = true;
        foreach ($groupes as $groupeId => $groupeInfos) {
            $checkbox="<input type=\"checkbox\" name=\"checkbox_user_groups[$groupeId]\" value=\"$groupeId\">";
            if (in_array($groupeId, $data['groupesId'])) {
                $checkbox="<input type=\"checkbox\" name=\"checkbox_user_groups[$groupeId]\" value=\"$groupeId\" checked>";
            }

            $childTable .= '<tr class="'.($i ? 'i' : 'p').'">';
            $childTable .= '<td>' . $checkbox . '</td>';
            $childTable .= '<td>&nbsp;' . $groupeInfos['g_groupename'] . '&nbsp</td>';
            $childTable .= '<td>&nbsp;' . $groupeInfos['g_comment'] . '&nbsp;</td>';
            $childTable .= '</tr>';
            $i = !$i;
        }
        $childTable .= '<tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();

        return $return;
    }

    /**
     * Formulaire de confirmation de suppression d'un utilisateur
     * 
     * @param string $login
     * @return string
     */
    public static function getFormDeleteUser($login)
    {
        $donneesUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($login);
        $return   = '';
        $message   = '';
        $errorsLst = [];
        $notice    = '';

        if (!empty($_POST)) {
            $formValue = 
                    [
                        'login' => $_POST['new_login'],
                        '_METHOD' => $_POST['_METHOD'],
                    ];

            if (static::postFormUtilisateur($formValue, $errorsLst, $notice)) {
                redirect(ROOT_PATH . 'hr/hr_index.php?onglet=page_principale&notice=' . $notice, false);
            } else {
                if (!empty($errorsLst)) {
                    $errors = '';
                    foreach ($errorsLst as $key => $value) {
                        $errors .= '<li>' . $key . ' : ' . $value . '</li>';
                    }
                    $message = '<div class="alert alert-danger">' . _('erreur_recommencer') . ' :<ul>' . $errors . '</ul></div>';
                }
            }
        }

        $return .= '<form action="" method="post" accept-charset="UTF-8"
enctype="application/x-www-form-urlencoded" class="form-group">';
        $return .= '<div class="alert alert-danger">' . _('êtes vous sur de vouloir supprimer cet utilisateur') . '</div>';
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
        $childTable .= '<th>' . _('divers_login_maj_1') . '</th>';
        $childTable .= '<th>'. _('divers_nom_maj_1') . '</th>';
        $childTable .= '<th>' . _('divers_prenom_maj_1') . '</th>';
        $childTable .= '</tr>';
        $childTable .= '</thead>';
        $childTable .= '<tbody>';
        $childTable .= '<td>' . $donneesUser["u_login"] . '</td>';
        $childTable .= '<td>' . $donneesUser["u_nom"] . '</td>';
        $childTable .= '<td>' . $donneesUser["u_prenom"] . '</td>';
        $childTable .= '</tr>';
        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '<br>';
        $return .= '<input type="hidden" name="_METHOD" value="DELETE" />';
        $return .= '<input type="hidden" name="new_login" value="' . $login . '" />';
        $return .= '<input class="btn btn-danger" type="submit" value="' . _('form_supprim') . '">';
        $return .= '<a class="btn" href="hr_index.php?onglet=page_principale">' . _('form_cancel') . '</a>';
        $return .= '</form>';

        return $return;
    }

    /**
     * Nettoyage des données postés par le formulaire
     * 
     * @param type $htmlPost
     * @param \includes\SQL $sql
     * @param \App\Libraries\Configuration $config
     * 
     * @return type
     */
    public static function dataForm2Array($htmlPost, \includes\SQL $sql, \App\Libraries\Configuration $config)
    {
        $data['login'] = htmlentities($htmlPost['new_login'], ENT_QUOTES | ENT_HTML401);
        $data['oldLogin'] = key_exists('old_login', $htmlPost)
                ? htmlentities($htmlPost['old_login'], ENT_QUOTES | ENT_HTML401)
                : htmlentities($htmlPost['new_login'], ENT_QUOTES | ENT_HTML401);
        $data['nom'] = htmlentities($htmlPost['new_nom'], ENT_QUOTES | ENT_HTML401);
        $data['prenom'] = htmlentities($htmlPost['new_prenom'], ENT_QUOTES | ENT_HTML401);
        $data['quotite'] = (int) $htmlPost['new_quotite'];
        $data['soldeHeure'] = htmlentities($htmlPost['new_solde_heure'], ENT_QUOTES | ENT_HTML401);
        $data['isActive'] = $htmlPost['new_is_active'] === 'N' ? 'N' : 'Y';
        $data['isResp'] = $htmlPost['new_is_resp'] === 'Y' ? 'Y' : 'N';
        $data['isAdmin'] = $htmlPost['new_is_admin'] === 'Y' ? 'Y' : 'N';
        $data['isHR'] = $htmlPost['new_is_hr'] === 'Y' ? 'Y' : 'N';

        if (!$config->isUsersExportFromLdap()) {
            $data['email'] = htmlentities($htmlPost['new_email'], ENT_QUOTES | ENT_HTML401);
        } else {
            $ldap = new \App\Libraries\Ldap();
            $data['email'] = $ldap->getEmailUser($data['login']);
        }

        if ($config->getHowToConnectUser() == "dbconges") {
            $data['pwd1'] = $htmlPost['new_password1'] == "" ? "" : md5($htmlPost['new_password1']);
            $data['pwd2'] = $htmlPost['new_password2'] == "" ? "" : md5($htmlPost['new_password2']);
        } else {
            $data['pwd1'] = md5(uniqid('', true));
            $data['pwd2'] = md5('none');
        }

        if (array_key_exists('_METHOD', $htmlPost)) {
            $data['_METHOD'] = htmlentities($htmlPost['_METHOD'], ENT_QUOTES | ENT_HTML401);
        }

        foreach ($htmlPost['tab_new_jours_an'] as $typeId => $joursAn) {
            $tmp = htmlentities($joursAn, ENT_QUOTES | ENT_HTML401);
            $data['joursAn'][$typeId] = strtr(\App\Helpers\Formatter::roundToHalf($tmp),",",".");
        }
        foreach ($htmlPost['tab_new_solde'] as $typeId => $solde) {
            $tmp = htmlentities($solde, ENT_QUOTES | ENT_HTML401);
            $data['soldes'][$typeId] = strtr(\App\Helpers\Formatter::roundToHalf($tmp),",",".");
        }
        foreach ($htmlPost['tab_new_reliquat'] as $typeId => $reliquat) {
            $tmp = htmlentities($reliquat, ENT_QUOTES | ENT_HTML401);
            $data['reliquats'][$typeId] = strtr(\App\Helpers\Formatter::roundToHalf($tmp),",",".");
        }
        $data['groupesId'] = array_key_exists('checkbox_user_groups', $htmlPost) ? array_keys($htmlPost['checkbox_user_groups']) : [];

        return $data;
    }

    /**
     * Tri les tableaux, d'abord par activité, puis par ordre lexicographique
     *
     * @return int {-1, 0, 1}
     */
    public static function sortParActif (array $a, array $b)
    {
        if ($a['u_is_active'] == 'Y' && $b['u_is_active'] == 'N') {
            return -1; // $a est avant $b
        } elseif ($a['u_is_active'] == 'N' && $b['u_is_active'] == 'Y') {
            return 1; // $a est derrière $b
        }

        return strnatcmp($a['u_nom'], $b['u_nom']);
    }

    /**
     * Traite la suppression, la creation ou la modification d'un utilisateur
     *
     * @param array $post
     * @param array &$errors
     * @param string $notice
     *
     * @return int
     */
    private static function postFormUtilisateur(array $post, array &$errors, &$notice)
    {
        $return = false;
        if (!\App\ProtoControllers\Utilisateur::isRH($_SESSION['userlogin'])) {
            $errors[] = _('non autorisé');
            return $return;
        }
        
        if (!empty($post['_METHOD'])) {
            switch ($post['_METHOD']) {
                case 'DELETE':
                    $return = static::deleteUtilisateur($post['login'], $errors);
                    if ($return) {
                        $notice = "deleted";
                        log_action(0, '', $post['login'], 'utilisateur ' . $post['login'] . ' supprimé');
                    }
                    return $return;
                case 'PUT':
                    if (!empty($_GET['login'])) {
                        $return = static::putUtilisateur($post, $errors);
                    }
                    if ($return) {
                        $notice = "modified";
                        log_action(0, '', $post['login'], 'utilisateur ' . $post['login'] . ' modifié');
                    }
                    return $return;
            }
        } else {
                $return = static::insertUtilisateur($post, $errors);
                if ($return) {
                    $notice = "inserted";
                    log_action(0, '', $post['login'], 'utilisateur ' . $post['login'] . ' ajouté');
                }
            return $return;
        }
    }

    /**
     * Controle la conformité du formulaire de création
     * 
     * @param aray $data
     * @param array $errors
     * @param \includes\SQL $sql
     * @param \App\Libraries\Configuration $config
     * 
     * @return boolean
     */
    private static function isFormInsertValide($data, &$errors, \includes\SQL $sql, \App\Libraries\Configuration $config)
    {
        $return = true;
        $users = \App\ProtoControllers\Utilisateur::getListId(false, true);
        if (in_array($data['login'], $users)) {
            $errors[] = _('Cet identifiant existe déja.');
            $return = false;
        }

        if ($config->getHowToConnectUser() == 'dbconges') {
            if ($data['pwd1'] == '' || strcmp($data['pwd1'], $data['pwd2'])!=0 ) {
                $errors[] = _('Saisie du mot de passe incorrect');
                $return = false;
            }
        }

        return $return && static::isFormValide($data, $errors, $sql, $config);
    }

    /**
     * Controle la conformité du formulaire de mise à jour
     * 
     * @param array $data
     * @param array $errors
     * @param \includes\SQL $sql
     * @param \App\Libraries\Configuration $config
     * @return boolean
     */
    private static function isFormUpdateValide($data, &$errors, \includes\SQL $sql, \App\Libraries\Configuration $config)
    {
        $return = true;
        $users = \App\ProtoControllers\Utilisateur::getListId(false, true);
        if (in_array($data['login'], $users) && $data['login'] != $data['oldLogin']) {
            $errors[] = _('Cet identifiant existe déja.');
            $return = false;
        }

        $groupesId = \App\ProtoControllers\Groupe::getListeId($sql);
        if ('N' === $data['isResp'] 
                && (\App\ProtoControllers\Groupe::isResponsableGroupe($data['login'], $groupesId, $sql) 
                || \App\ProtoControllers\Groupe::isGrandResponsableGroupe($data['login'], $groupesId, $sql))) {
            $errors[] = _('Cette utilisateur est responsable d\'au moins un groupe');
            $return = false;
        }

        if ($config->getHowToConnectUser() == 'dbconges') {
            if ($data['pwd1'] != '' && strcmp($data['pwd1'], $data['pwd2'])!=0 ) {
                $errors[] = _('Saisie du mot de passe incorrect');
                $return =  false;
            }
        }

        return $return && static::isFormValide($data, $errors, $sql, $config);
    }

    /**
     * Controle la conformité du formulaire (création et mise à jour)
     * 
     * @param array $data
     * @param array $errors
     * @param \includes\SQL $sql
     * @param \App\Libraries\Configuration $config
     * @return boolean
     */
    public static function isFormValide($data, &$errors, \includes\SQL $sql, \App\Libraries\Configuration $config)
    {
        $return = true;

        if (!preg_match('/^[a-z.\d_-]{2,30}$/i', $data['login'])) {
            $errors[] = _('Identifiant incorrect.');
            $return = false;
        }

        if ('' == $data['nom']) {
            $errors[] = _('Veuillez saisir un nom');
            $return = false;
        }

        if ('' == $data['prenom']) {
            $errors[] = _('Veuillez saisir un prenom');
            $return = false;
        }

        if (0 >= $data['quotite'] || 100 < $data['quotite']) {
            $errors[] = _('quotité incorrect');
            $return = false;
        }

        if ($config->isHeuresAutorise()) {
            if (!\App\Helpers\Formatter::isHourFormat($data['soldeHeure'])) {
                $errors[] = _('Format du solde d\'heure incorrect');
                $return = false;
            }
        }

        if (!$config->isUsersExportFromLdap()) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = _('Format de l\'adresse email incorrect');
                $return = false;
            }
        }

        foreach ($data['joursAn'] as $typeId => $joursAn) {
            if (!is_numeric($joursAn)) {
                $errors[] = _('nombre de jours par an incorrect');
                $return = false;
            break;
            }
        }

        foreach ($data['soldes'] as $typeId => $solde) {
            if (!is_numeric($solde)) {
                $errors[] = _('solde incorrect');
                $return = false;
            break;
            }
        }

        foreach ($data['reliquats'] as $typeId => $reliquat) {
            if (!is_numeric($reliquat)) {
                $errors[] = _('reliquat incorrect');
                $return = false;
            break;
            }
        }

        return $return;
    }

    /**
     * Supprime un utilisateur
     * 
     * @param string $user
     * @param array $errors
     * 
     * @return boolean
     */
    private static function deleteUtilisateur($user, &$errors)
    {
        $sql = \includes\SQL::singleton();
        if (!static::isDeletable($user, $sql)) {
            $errors[] = _('Suppression impossible : cette utilisateur est responsable d\'un groupe');
            return false;
        }

        $sql->getPdoObj()->begin_transaction();

        $req = 'DELETE FROM conges_echange_rtt WHERE e_login = "' . $user . '"';
        $sql->query($req);

        $req = 'DELETE FROM conges_edition_papier WHERE ep_login = "' . $user . '"';
        $sql->query($req);

        $req = 'DELETE FROM conges_groupe_users WHERE gu_login = "' . $user . '"';
        $sql->query($req);

        $req = 'DELETE FROM conges_periode WHERE p_login = "' . $user . '"';
        $sql->query($req);

        $req = 'DELETE FROM conges_solde_user WHERE su_login = "' . $user . '"';
        $sql->query($req);

        $req = 'DELETE FROM heure_additionnelle WHERE login = "' . $user . '"';
        $sql->query($req);

        $req = 'DELETE FROM heure_repos WHERE login = "' . $user . '"';
        $sql->query($req);

        $req = 'DELETE FROM conges_users WHERE u_login = "' . $user . '"';
        $commit = $sql->query($req);

        if ($commit) {
            return $sql->getPdoObj()->commit();
        }
        $sql->getPdoObj()->rollback();

        return $commit;
    }

    /**
     * Controle la possibilité de supprimer un utilisateur
     * 
     * @param string $user
     * @param \includes\SQL $sql
     * 
     * @return boolean
     */
    public static function isDeletable($user, \includes\SQL $sql)
    {
        $req = 'SELECT EXISTS (
                    SELECT gr_login, ggr_login
                    FROM conges_groupe_resp, conges_groupe_grd_resp
                    WHERE conges_groupe_resp.gr_login = "' . $user . '"
                    OR conges_groupe_grd_resp.ggr_login = "' . $user . '"
                )';
        $query = $sql->query($req);
        return 0 >= (int) $query->fetch_array()[0];
    }

    /**
     * Création d'un nouvel utilisateur
     * 
     * @param array $data
     * @param array $errors
     * @return boolean
     */
    private static function insertUtilisateur($data, &$errors)
    {
        $sql = \includes\SQL::singleton();
        $config = new \App\Libraries\Configuration($sql);
        if (!static::isFormInsertValide($data, $errors, $sql, $config)) {
            return false;
        }

        $sql->getPdoObj()->begin_transaction();
        $insertInfos = static::insertInfosUtilisateur($data, $sql);
        $insertEmail = static::insertEmailUtilisateur($data, $sql);
        $insertSoldes = static::insertSoldeUtilisateur($data, $sql);
        $insertGroupes = true;
        if (!empty($data|'groupesId')) {
            $insertGroupes = static::insertGroupesUtilisateur($data, $sql);
        }
        if($insertInfos && $insertEmail && $insertSoldes && $insertGroupes) {
            return $sql->getPdoObj()->commit();
        }
        
        $sql->getPdoObj()->rollback();
        return false;
    }

    private static function insertInfosUtilisateur($data, \includes\SQL $sql)
    {
        $req = "INSERT INTO conges_users SET
                    u_login='" . $data['login'] . "', 
                    u_nom='" . $data['nom'] . "', 
                    u_prenom='" . $data['prenom'] . "', 
                    u_is_resp='" . $data['isResp'] . "', 
                    u_is_admin='" . $data['isAdmin'] . "', 
                    planning_id = 0, 
                    u_is_hr='" . $data['isHR'] . "',
                    u_passwd='" . $data['pwd1'] . "', 
                    u_quotite=" . $data['quotite'] . ",
                    u_heure_solde=" . \App\Helpers\Formatter::hour2Time($data['soldeHeure']) . ",
                    date_inscription = '" . date('Y-m-d H:i') . "';";

        return $sql->query($req);
    }

    private static function insertEmailUtilisateur($data, \includes\SQL $sql)
    {
        $req = "INSERT INTO conges_users SET
                u_email = '" . $data['email'] . "';";

        return $sql->query($req);
    }

    private static function insertSoldeUtilisateur($data, \includes\SQL $sql)
    {
        $config = new \App\Libraries\Configuration($sql);
        $typeAbsencesConges = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges');

        foreach ($typeAbsencesConges as $typeId => $info) {
            $valuesStd[] = "('" . $data['login'] . "' ,"
                                . $typeId . ", "
                                . $data['joursAn'][$typeId] . ", " 
                                . $data['soldes'][$typeId] . ", " 
                                . $data['reliquats'][$typeId] . ")" ;
        }
        $req = "INSERT INTO conges_solde_user (su_login, su_abs_id, su_nb_an, su_solde, su_reliquat) VALUES " . implode(",", $valuesStd);
        $returnStd = $sql->query($req);

        if ($config->isCongesExceptionnelsActive()) {
            $typeAbsencesExceptionnels = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges_exceptionnels');
            foreach ($typeAbsencesExceptionnels as $typeId => $info) {
                $valuesExc[] = "('" . $data['login'] . "' ," 
                                    . $typeId . ", 0, " 
                                    . $data['soldes'][$typeId] . ", 0)" ;

            }
            $req = "INSERT INTO conges_solde_user (su_login, su_abs_id, su_nb_an, su_solde, su_reliquat) VALUES " . implode(",", $valuesExc);
            $returnExc = $sql->query($req);
        }

        return $returnStd && $returnExc;
    }

    private static function insertGroupesUtilisateur($data, \includes\SQL $sql)
    {
        foreach ($data['groupesId'] as $gid) {
            $values[] = "(" . $gid . ", '" . $data['login'] . "')"  ;
        }
        $req = "INSERT INTO conges_groupe_users (gu_gid, gu_login) VALUES " . implode(",", $values)  ;

        return $sql->query($req);
    }

    /**
     * Mise à jour d'un utilisateur
     * 
     * @param array $data
     * @param array $errors
     * @return boolean
     */
    private static function putUtilisateur($data, &$errors)
    {
        $sql = \includes\SQL::singleton();
        $config = new \App\Libraries\Configuration($sql);
        if (!static::isFormUpdateValide($data, $errors, $sql, $config)) {
            return false;
        }

        $sql->getPdoObj()->begin_transaction();
        $userUpdate = static::updateInfosUtilisateur($data, $sql);
        $soldesUpdate = static::updateSoldeUtilisateur($data, $sql);
        $pwdUpdate = true;
        if ('' != $data['pwd1']) {
            $pwdUpdate = static::updatePasswordUtilisateur($data, $sql);
        }

        $emailUpdate = static::updateEmailUtilisateur($data, $sql);
        $loginUpdate = true;
        if ($data['oldLogin'] != $data['login']) {
            $loginUpdate = static::updateLoginUtilisateur($data, $sql);
        }

        if ($userUpdate && $soldesUpdate && $pwdUpdate && $emailUpdate && $loginUpdate) {
            return $sql->getPdoObj()->commit();
        }

        $sql->getPdoObj()->rollback();
        return false;
    }

    private static function updateInfosUtilisateur($data, \includes\SQL $sql)
    {
        $req = 'UPDATE conges_users 
                SET u_nom="' . $data['nom'] . '",
                    u_prenom="' . $data['prenom'] . '", 
                    u_is_resp="' . $data['isResp'] . '", 
                    u_heure_solde='. \App\Helpers\Formatter::hour2Time($data['soldeHeure']) . ',
                    u_is_admin="' . $data['isAdmin'] . '",
                    u_is_hr="' . $data['isHR'] . '",
                    u_is_active="' . $data['isActive'].'",
                    u_quotite="' . $data['quotite'] . '"
                    WHERE u_login="' . $data['oldLogin'] . '"' ;
        return $sql->query($req);
    }

    private static function updateSoldeUtilisateur($data, \includes\SQL $sql)
    {
        $config = new \App\Libraries\Configuration($sql);
        $typeAbsencesConges = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges');
        foreach ($typeAbsencesConges as $typeId => $info) {
            $valuesStd[] = '(\'' . $data['joursAn'][$typeId] . '\', \'' 
                                . $data['soldes'][$typeId] . '\', \'' 
                                . $data['reliquats'][$typeId] . '\', "' 
                                . $data['oldLogin'] . '", ' 
                                . (int) $typeId . ')';
        }
        $req = 'REPLACE INTO conges_solde_user (su_nb_an, su_solde, su_reliquat, su_login, su_abs_id) VALUES ' . implode(",", $valuesStd);
        $returnStd = $sql->query($req);

        $returnExc = true;
        if ($config->isCongesExceptionnelsActive()) {
            $typeAbsencesExceptionnels = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges_exceptionnels');
            foreach ($typeAbsencesExceptionnels as $typeId => $info) {
                $valuesExc[] = '(0, \'' 
                                . $data['soldes'][$typeId] . '\', 0, "' 
                                . $data['oldLogin'] . '", ' 
                                . (int) $typeId . ')';
            }
            $req = 'REPLACE INTO conges_solde_user (su_nb_an, su_solde, su_reliquat, su_login, su_abs_id) VALUES ' . implode(",", $valuesExc);
            $returnExc = $sql->query($req);
        }
        
        return $returnStd && $returnExc;
    }

    private static function updateLoginUtilisateur($data, \includes\SQL $sql)
    {
        $req = 'UPDATE conges_echange_rtt 
                SET e_login="' . $data['login'] . '"
                WHERE e_login="' . $data['oldLogin'] . '" ';
        $sql->query($req);

        // update table edition_papier
        $req = 'UPDATE conges_edition_papier 
                SET ep_login="' . $data['login'] . '" 
                WHERE ep_login="' . $data['oldLogin'] . '" ';
        $sql->query($req);

        // update table groupe_grd_resp
        $req = 'UPDATE conges_groupe_grd_resp 
                SET ggr_login= "' . $data['login'] . '"
                WHERE ggr_login="' . $data['oldLogin'] . '"  ';
        $sql->query($req);

        // update table groupe_resp
        $req = 'UPDATE conges_groupe_resp 
                SET gr_login="' . $data['login'] . '" 
                WHERE gr_login="' . $data['oldLogin'] . '" ';
        $sql->query($req);

        // update table conges_groupe_users
        $req = 'UPDATE conges_groupe_users 
                SET gu_login="' . $data['login'] . '" 
                WHERE gu_login="' . $data['oldLogin'] . '" ';
        $sql->query($req);

        // update table periode
        $req = 'UPDATE conges_periode 
                SET p_login="' . $data['login'] . '" 
                WHERE p_login="' . $data['oldLogin'] . '" ';
        $sql->query($req);

        $req = 'UPDATE conges_solde_user
                SET su_login="' . $data['login'] . '" 
                WHERE su_login="' . $data['oldLogin'] . '" ' ;
        $sql->query($req);

        $req = 'UPDATE heure_additionnelle
                SET login="' . $data['login'] . '" 
                WHERE login="' . $data['oldLogin'] . '" ' ;
        $sql->query($req);

        $req = 'UPDATE heure_repos
                SET login="' . $data['login'] . '" 
                WHERE login="' . $data['oldLogin'] . '" ' ;
        $sql->query($req);

        $req = 'UPDATE conges_user 
                SET u_login="' . $data['login'] . '"
                WHERE u_login="' . $data['oldLogin'] . '" ';

        return $sql->query($req);
    }

    private static function updateEmailUtilisateur($data, \includes\SQL $sql)
    {
        $req = 'UPDATE conges_users 
                SET u_email = "'. $data['email'] . '" 
                    WHERE u_login="' . $data['oldLogin'] . '"' ;
        return $sql->query($req);
    }

    private static function updatePasswordUtilisateur($data, \includes\SQL $sql)
    {
        $req = 'UPDATE conges_users 
                SET u_passwd = "' . $data['pwd1'] . '" 
                    WHERE u_login="' . $data['oldLogin'] . '"' ;
        return $sql->query($req);
    }
}