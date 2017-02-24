<?php

defined('_PHP_CONGES') or die('Restricted access');
echo suppressionAbsenceModule();

/**
 * Encapsule le comportement du module de suppression d'absence
 *
 *
 * @return void
 * @access public
 * @static
 */
function suppressionAbsenceModule()
{
    $p_num           = getpost_variable('p_num');
    $onglet          = getpost_variable('onglet');
    $p_num_to_delete = getpost_variable('p_num_to_delete');
    $return          = '';
    /*************************************/

    // TITRE
    $return .= '<h1>' . _('user_suppr_demande_titre') . '</h1>';
    $return .= '<br>';

    if ($p_num != "") {
        $return .= confirmerSuppression($p_num, $onglet);
    } else {
        if ($p_num_to_delete != "") {
            $return .= suppression($p_num_to_delete, $onglet);
        } else {
            // renvoit sur la page principale .
            redirect(ROOT_PATH . 'utilisateur/user_index.php', false);
        }
    }

    return $return;
}

function suppression($p_num_to_delete, $onglet)
{
    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $session  = session_id();
    $return   = '';

    if ($_SESSION['config']['mail_supp_demande_alerte_resp']) {
        alerte_mail($_SESSION['userlogin'], ":responsable:", $p_num_to_delete, "supp_demande_conges");
    }

    $sql_delete    = 'DELETE FROM conges_periode WHERE p_num = ' . \includes\SQL::quote($p_num_to_delete) . ' AND p_login="' . \includes\SQL::quote($_SESSION['userlogin']) . '";';
    $result_delete = \includes\SQL::query($sql_delete);

    $comment_log = "suppression de demande num $p_num_to_delete";
    log_action($p_num_to_delete, "", $_SESSION['userlogin'], $comment_log);

    if ($result_delete) {
        $return .= _('form_modif_ok') . "<br><br> \n";
    } else {
        $return .= _('form_modif_not_ok') . "<br><br> \n";
    }

    /* APPEL D'UNE AUTRE PAGE */
    $return .= '<form action="' . ROOT_PATH . 'utilisateur/user_index.php?session=' . $session . '&onglet=liste_conge" method="POST">';
    $return .= '<input class="btn" type="submit" value="' . _('form_submit') . '">';
    $return .= '</form>';
    $return .= '<a href="">';

    return $return;
}

function confirmerSuppression($p_num, $onglet)
{
    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $session  = session_id();
    $return   = '';

    // Récupération des informations
    $sql1 = 'SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_num FROM conges_periode WHERE p_num = "' . \includes\SQL::quote($p_num) . '"';
    //printf("sql1 = %s<br>\n", $sql1);
    $ReqLog1 = \includes\SQL::query($sql1);

    // AFFICHAGE TABLEAU
    $return .= '<form action="' . $PHP_SELF . '" method="POST">';
    $return .= '<table class="table table-responsive table-condensed">';
    $return .= '<thead>';
    $return .= '<tr>';
    $return .= '<th>' . _('divers_debut_maj_1') . '</th>';
    $return .= '<th>' . _('divers_fin_maj_1') . '</th>';
    $return .= '<th>' . _('divers_nb_jours_maj_1') . '</th>';
    $return .= '<th>' . _('divers_comment_maj_1') . '</th>';
    $return .= '<th>' . _('divers_type_maj_1') . '</th>';
    $return .= '</tr>';
    $return .= '</thead>';
    $return .= '<tbody>';
    $return .= '<tr>';
    while ($resultat1 = $ReqLog1->fetch_array()) {
        $sql_date_deb      = eng_date_to_fr($resultat1["p_date_deb"]);
        $sql_demi_jour_deb = $resultat1["p_demi_jour_deb"];
        if ($sql_demi_jour_deb == "am") {
            $demi_j_deb = _('divers_am_short');
        } else {
            $demi_j_deb = _('divers_pm_short');
        }

        $sql_date_fin      = eng_date_to_fr($resultat1["p_date_fin"]);
        $sql_demi_jour_fin = $resultat1["p_demi_jour_fin"];
        if ($sql_demi_jour_fin == "am") {
            $demi_j_fin = _('divers_am_short');
        } else {
            $demi_j_fin = _('divers_pm_short');
        }

        $sql_nb_jours = affiche_decimal($resultat1["p_nb_jours"]);
        //$sql_type=$resultat1["p_type"];
        $sql_type    = get_libelle_abs($resultat1["p_type"]);
        $sql_comment = $resultat1["p_commentaire"];

        $return .= '<td>' . $sql_date_deb . '_' . $demi_j_deb . '</td>';
        $return .= '<td>' . $sql_date_fin . '_' . $demi_j_fin . '</td>';
        $return .= '<td>' . $sql_nb_jours . '</td>';
        $return .= '<td>' . $sql_comment . '</td>';
        $return .= '<td>' . $sql_type . '</td>';
    }
    $return .= '</tr>';
    $return .= '</tbody>';
    $return .= '</table>';
    $return .= '<hr/>';
    $return .= '<input type="hidden" name="p_num_to_delete" value="' . $p_num . '">';
    $return .= '<input type="hidden" name="session" value="' . $session . '">';
    $return .= '<input type="hidden" name="onglet" value="' . $onglet . '">';
    $return .= '<input class="btn btn-danger" type="submit" value="' . _('form_supprim') . '">';
    $return .= '<a class="btn" href="' . $PHP_SELF . '?session=' . $session . '&onglet=liste_conge">' . _('form_cancel') . '</a>';
    $return .= '</form>';

    return $return;
}

// renvoit le libelle d une absence (conges ou absence) d une absence
function get_libelle_abs($_type_abs_id)
{
    $sql_abs    = 'SELECT ta_libelle FROM conges_type_absence WHERE ta_id="' . \includes\SQL::quote($_type_abs_id) . '"';
    $ReqLog_abs = \includes\SQL::query($sql_abs);
    if ($resultat_abs = $ReqLog_abs->fetch_array()) {
        return $resultat_abs['ta_libelle'];
    } else {
        return "";
    }
}
