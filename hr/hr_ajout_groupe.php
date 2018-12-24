<?php declare(strict_types = 1);
defined('_PHP_CONGES') or die('Restricted access');
$gestionGroupes = new \App\ProtoControllers\Groupe\Gestion();

/**
 *
 * Formulaire d'ajout ou de modification d'un groupe
 *
 * @param int $idGroupe
 * @return string
 */
function getForm($idGroupe = NIL_INT)
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
    $return .= getFormChoixEmploye($idGroupe, $data);
    $return .= '</div>';

    $return .= '<div class="col-md-6">';
    $return .= '<h2>' . _('admin_gestion_groupe_resp_responsables') . '</h2>';
    $return .= getFormChoixResponsable($idGroupe, $selectId, $data);
    $return .= '</div>';

    $return .= '<div class="col-md-6 hide" id="' . $DivGrandRespId . '">';
    $return .= '<h2>' . _('admin_gestion_groupe_grand_resp_responsables') . '</h2>';
    $return .= getFormChoixGrandResponsable($idGroupe, $selectId, $data);
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
function getFormChoixEmploye($idGroupe, $data = NULL)
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
function getFormChoixResponsable($idGroupe, $selectId, $data)
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
 * retournes les utilisateurs responsables
 * si $idGroupe existe, marquage des responsables du groupe
 *
 * @param int $idGroupe
 * @return array
 */
function getInfosResponsables($idGroupe = NIL_INT)
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
 * Formulaire de selection des grands responsables d'un groupe
 *
 * @param int $idGroupe
 * @return string
 */
function getFormChoixGrandResponsable($idGroupe,$selectId, $data)
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
 *
 * retournes les utilisateurs responsables
 * si $idGroupe existe, marquage des grands responsables du groupe
 *
 * @param int $idGroupe
 * @return array
 */
function getGrandResponsables($idGroupe = NIL_INT)
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

require_once VIEW_PATH . 'Groupe/Edition.php';
