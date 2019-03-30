<?php declare(strict_types = 1);

defined('_PHP_CONGES') or die('Restricted access');
echo getFormUser();

/**
 * formulaire d'ajout / modification d'un utilisateur
 *
 * @return string
 */
function getFormUser()
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
        'groupesId' => [],
    ];

    if (!empty($_POST)) {
        $formValue = static::dataForm2Array($_POST, $sql, $config);
        if (static::postFormUtilisateur($formValue, $errorsLst, $notice)) {
            redirect(ROOT_PATH . 'hr/page_principale?notice=' . $notice, false);
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


    $return .= '<h1>' . _('Nouvel Utilisateur') . '</h1>';
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
        $childTable .= "<td><input class=\"form-control\" type=\"password\" name=\"new_password1\" size=\"10\" maxlength=\"15\" value=\"\" autocomplete=\"off\"></td>" ;
        $childTable .= "<td><input class=\"form-control\" type=\"password\" name=\"new_password2\" size=\"10\" maxlength=\"15\" value=\"\" autocomplete=\"off\"></td>" ;
    }
    $childTable .= '</tr></tbody>';
    $childTable .= '<script type="text/javascript">generateTimePicker("' . $soldeHeureId . '");</script>';
    $table->addChild($childTable);
    ob_start();
    $table->render();
    $return .= ob_get_clean();
    $return .= '<br><hr>';

    $return .= static::getFormUserSoldes($formValue);
    $return .= '<br><hr>';

    $return .= getFormUserGroupes($formValue);
    $return .= '<hr>';

    $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
    $return .= ' <a class="btn btn-default" href="page_principale">' . _('form_cancel') . '</a>';
    $return .= '</form>';

    return $return;
}

/**
 * formulaire de gestion des soldes d'un utilisateur
 *
 * @param array $data
 *
 * @return string
 *
 */
function getFormUserSoldes($data)
{
    $sql = \includes\SQL::singleton();
    $config = new \App\Libraries\Configuration($sql);
    $typeAbsencesConges = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges');
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
function getFormUserGroupes($data)
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
