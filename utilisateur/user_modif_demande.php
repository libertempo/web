<?php
defined('_PHP_CONGES') or die('Restricted access');
echo modificationAbsenceModule();

/**
 * Encapsule le comportement du module de modification d'absence
 *
 *
 * @return void
 * @access public
 * @static
 */
function modificationAbsenceModule()
{
    $user_login        = $_SESSION['userlogin'];
    $p_num             = getpost_variable('p_num');
    $onglet            = getpost_variable('onglet');
    $p_num_to_update   = getpost_variable('p_num_to_update');
    $p_etat            = getpost_variable('p_etat');
    $new_debut         = getpost_variable('new_debut');
    $new_demi_jour_deb = getpost_variable('new_demi_jour_deb');
    $new_fin           = getpost_variable('new_fin');
    $new_demi_jour_fin = getpost_variable('new_demi_jour_fin');
    $new_comment       = getpost_variable('new_comment');
    $return            = '';

    //conversion des dates
    $new_debut = convert_date($new_debut);
    $new_fin   = convert_date($new_fin);

    if ($_SESSION['config']['disable_saise_champ_nb_jours_pris']) {
        $new_nb_jours = compter($user_login, $p_num_to_update, $new_debut, $new_fin, $new_demi_jour_deb, $new_demi_jour_fin, $new_comment);
    } else {
        $new_nb_jours = getpost_variable('new_nb_jours');
    }

    /*************************************/
    // TITRE
    $return .= '<h1>' . _('user_modif_demande_titre') . '</h1>';

    if ($p_num != "") {
        $return .= confirmer($p_num, $onglet);
    } else {
        if ($p_num_to_update != "") {
            $return .= modifier($p_num_to_update, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $p_etat, $onglet);
        } else {
            // renvoit sur la page principale .
            redirect(ROOT_PATH . 'utilisateur/user_index.php', false);
        }
    }

    return $return;
}

function confirmer($p_num, $onglet)
{
    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $session  = session_id();
    $return   = '';

    // Récupération des informations
    $sql1    = 'SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_etat, p_num FROM conges_periode where p_num = "' . \includes\SQL::quote($p_num) . '"';
    $ReqLog1 = \includes\SQL::query($sql1);

    // AFFICHAGE TABLEAU

    $return .= '<form NAME="dem_conges" action="' . $PHP_SELF . '" method="POST">';
    $return .= '<table class="table table-responsive">';
    $return .= '<thead>';
    // affichage première ligne : titres
    $return .= '<tr>';
    $return .= '<td>' . _('divers_debut_maj_1') . '</td>';
    $return .= '<td>' . _('divers_fin_maj_1') . '</td>';
    $return .= '<td>' . _('divers_nb_jours_maj_1') . '</td>';
    $return .= '<td>' . _('divers_comment_maj_1') . '</td>';
    $return .= '</tr>';
    $return .= '</thead>';
    $return .= '<tbody>';
    // affichage 2ieme ligne : valeurs actuelles
    $return .= '<tr>';
    while ($resultat1 = $ReqLog1->fetch_array()) {
        $sql_date_deb = eng_date_to_fr($resultat1["p_date_deb"]);

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

        $sql_nb_jours    = $resultat1["p_nb_jours"];
        $aff_nb_jours    = affiche_decimal($sql_nb_jours);
        $sql_commentaire = $resultat1["p_commentaire"];
        $sql_etat        = $resultat1["p_etat"];

        $return .= '<td>' . $sql_date_deb . '_' . $demi_j_deb . '</td><td>' . $sql_date_fin . '_' . $demi_j_fin . '</td><td>' . $aff_nb_jours . '</td><td>' . $sql_commentaire . '</td>';

        $compte = "";
        if ($_SESSION['config']['rempli_auto_champ_nb_jours_pris']) {
            $compte = 'onChange="compter_jours();return false;"';
        }

        $text_debut = "<input class=\"form-control date\" type=\"text\" name=\"new_debut\" size=\"10\" maxlength=\"30\" value=\"" . revert_date($sql_date_deb) . "\">";
        if ($sql_demi_jour_deb == "am") {
            $radio_deb_am = "<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"am\" checked>&nbsp;" . _('form_am');
            $radio_deb_pm = "<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"pm\">&nbsp;" . _('form_pm');
        } else {
            $radio_deb_am = "<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"am\">" . _('form_am');
            $radio_deb_pm = "<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"pm\" checked>" . _('form_pm');
        }
        $text_fin = "<input class=\"form-control date\" type=\"text\" name=\"new_fin\" size=\"10\" maxlength=\"30\" value=\"" . revert_date($sql_date_fin) . "\">";
        if ($sql_demi_jour_fin == "am") {
            $radio_fin_am = "<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"am\" checked>" . _('form_am');
            $radio_fin_pm = "<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"pm\">" . _('form_pm');
        } else {
            $radio_fin_am = "<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"am\">" . _('form_am');
            $radio_fin_pm = "<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"pm\" checked>" . _('form_pm');
        }
        if ($_SESSION['config']['disable_saise_champ_nb_jours_pris']) {
            $text_nb_jours = "<input class=\"form-control\" type=\"text\" name=\"new_nb_jours\" size=\"5\" maxlength=\"30\" value=\"$sql_nb_jours\" style=\"background-color: #D4D4D4; \" readonly=\"readonly\">";
        } else {
            $text_nb_jours = "<input class=\"form-control\" type=\"text\" name=\"new_nb_jours\" size=\"5\" maxlength=\"30\" value=\"$sql_nb_jours\">";
        }

        $text_commentaire = "<input class=\"form-control\" type=\"text\" name=\"new_comment\" size=\"15\" maxlength=\"30\" value=\"$sql_commentaire\">";
    }
    $return .= '</tr>';

    // affichage 3ieme ligne : saisie des nouvelles valeurs
    $return .= '<tr>';
    $return .= '<td>' . $text_debut . '<br>' . $radio_deb_am . '/' . $radio_deb_pm . '</td><td>' . $text_fin . '<br>' . $radio_fin_am . '/' . $radio_fin_pm . '</td><td>' . $text_nb_jours . '</td><td>' . $text_commentaire . '</td>';
    $return .= '</tr>';

    $return .= '</tbody>';
    $return .= '</table>';
    $return .= '<hr/>';
    $return .= '<input type="hidden" name="p_num_to_update" value="' . $p_num . '">';
    $return .= '<input type="hidden" name="p_etat" value="' . $sql_etat . '">';
    $return .= '<input type="hidden" name="session" value="' . $session . '">';
    $return .= '<input type="hidden" name="user_login" value="' . $_SESSION['userlogin'] . '">';
    $return .= '<input type="hidden" name="onglet" value="' . $onglet . '">';
    $return .= '<p id="comment_nbj" style="color:red">&nbsp;</p>';
    $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
    $return .= '<a class="btn" href="' . $PHP_SELF . '?session=' . $session . '&onglet=liste_conge">' . _('form_cancel') . '</a>';
    $return .= '</form>';

    return $return;
}

function modifier($p_num_to_update, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $p_etat, $onglet)
{
    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $session  = session_id();
    $return   = '';
    $VerifNb  = verif_saisie_decimal($new_nb_jours);
    $sql1     = "UPDATE conges_periode
            SET p_date_deb='$new_debut', p_demi_jour_deb='$new_demi_jour_deb', p_date_fin='$new_fin', p_demi_jour_fin='$new_demi_jour_fin', p_nb_jours='$new_nb_jours', p_commentaire='$new_comment', ";
    if ($p_etat == "demande") {
        $sql1 = $sql1 . " p_date_demande=NOW() ";
    } else {
        $sql1 = $sql1 . " p_date_traitement=NOW() ";
    }

    $sql1 = $sql1 . "    WHERE p_num='$p_num_to_update' AND p_login='" . $_SESSION['userlogin'] . "' ;";

    $result = \includes\SQL::query($sql1);

    if ($_SESSION['config']['mail_modif_demande_alerte_resp']) {
        alerte_mail($_SESSION['userlogin'], ":responsable:", $p_num_to_update, "modif_demande_conges");
    }
    $comment_log = "modification de demande num $p_num_to_update ($new_nb_jours jour(s)) ( de $new_debut $new_demi_jour_deb a $new_fin $new_demi_jour_fin) ($new_comment)";
    log_action($p_num_to_update, "$p_etat", $_SESSION['userlogin'], $comment_log);

    $return .= _('form_modif_ok') . '<br><br>';
    /* APPEL D'UNE AUTRE PAGE */
    $return .= '<form action="' . ROOT_PATH . 'utilisateur/user_index.php?session=' . $session . '&onglet=liste_conge" method="POST">';
    $return .= '<input class="btn" type="submit" value="' . _('form_submit') . '">';
    $return .= '</form>';

    return $return;

}
