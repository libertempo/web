<?php

defined('_PHP_CONGES') or die('Restricted access');
echo nouvelleAbsenceModule($onglet);

/**
 * Encapsule le comportement du module de nouvelle absence
 *
 * @param string $onglet Nom de l'onglet à afficher
 *
 * @return void
 * @access public
 * @static
 */
function nouvelleAbsenceModule($onglet)
{
    // on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
    init_tab_jours_feries();
    $return = '';

    // si le user peut saisir ses demandes et qu'il vient d'en saisir une ...

    $new_demande_conges = getpost_variable('new_demande_conges', 0);

    if ($new_demande_conges == 1 && $_SESSION['config']['user_saisie_demande']) {
        $new_debut         = htmlentities(getpost_variable('new_debut'), ENT_QUOTES | ENT_HTML401);
        $new_demi_jour_deb = htmlentities(getpost_variable('new_demi_jour_deb'), ENT_QUOTES | ENT_HTML401);
        $new_fin           = htmlentities(getpost_variable('new_fin'), ENT_QUOTES | ENT_HTML401);
        $new_demi_jour_fin = htmlentities(getpost_variable('new_demi_jour_fin'), ENT_QUOTES | ENT_HTML401);
        $new_comment       = htmlentities(getpost_variable('new_comment'), ENT_QUOTES | ENT_HTML401);
        $new_type          = htmlentities(getpost_variable('new_type'), ENT_QUOTES | ENT_HTML401);

        $user_login = $_SESSION['userlogin'];

        if ($_SESSION['config']['disable_saise_champ_nb_jours_pris']) {
            $new_nb_jours = compter($user_login, '', $new_debut, $new_fin, $new_demi_jour_deb, $new_demi_jour_fin, $new_comment);
        } else {
            $new_nb_jours = htmlentities(getpost_variable('new_nb_jours'), ENT_QUOTES | ENT_HTML401);
        }

        $return .= new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type);
    } else {
        $year_calendrier_saisie_debut = (int) getpost_variable('year_calendrier_saisie_debut', date('Y'));
        $mois_calendrier_saisie_debut = (int) getpost_variable('mois_calendrier_saisie_debut', date('m'));
        $year_calendrier_saisie_fin   = (int) getpost_variable('year_calendrier_saisie_fin', date('Y'));
        $mois_calendrier_saisie_fin   = (int) getpost_variable('mois_calendrier_saisie_fin', date('m'));

        /**************************/
        /* Nouvelle Demande */
        /**************************/
        /* Génération du datePicker et de ses options */
        $daysOfWeekDisabled = [];
        $datesDisabled      = [];
        if ((false == $_SESSION['config']['dimanche_travail'])
            && (false == $_SESSION['config']['samedi_travail'])
        ) {
            $daysOfWeekDisabled = [0, 6];
        } else {
            if (false == $_SESSION['config']['dimanche_travail']) {
                $daysOfWeekDisabled = [0];
            }
            if (false == $_SESSION['config']['samedi_travail']) {
                $daysOfWeekDisabled = [6];
            }
        }

        if (is_array($_SESSION["tab_j_feries"])) {
            foreach ($_SESSION["tab_j_feries"] as $date) {
                $datesDisabled[] = \App\Helpers\Formatter::dateIso2Fr($date);
            }
        }

        if (is_array($_SESSION["tab_j_fermeture"])) {
            foreach ($_SESSION["tab_j_fermeture"] as $date) {
                $datesDisabled[] = \App\Helpers\Formatter::dateIso2Fr($date);
            }
        }
        $startDate = ($_SESSION['config']['interdit_saisie_periode_date_passee']) ? 'd' : '';

        $datePickerOpts = [
            'daysOfWeekDisabled' => $daysOfWeekDisabled,
            'datesDisabled'      => $datesDisabled,
            'startDate'          => $startDate,
        ];
        $return .= '<script>generateDatePicker(' . json_encode($datePickerOpts) . ');</script>';
        $return .= '<h1>' . _('divers_nouvelle_absence') . '</h1>';

        //affiche le formulaire de saisie d'une nouvelle demande de conges
        $return .= saisie_nouveau_conges2($_SESSION['userlogin'], $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet);
    }
    return $return;
}

// verifie les parametre de la nouvelle demande :si ok : enregistre la demande dans table conges_periode
function new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type)
{
    //conversion des dates
    $new_debut = convert_date($new_debut);
    $new_fin   = convert_date($new_fin);
    $return    = '';

    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $session  = session_id();

    // verif validité des valeurs saisies
    $valid = verif_saisie_new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment);

    // verifie que le solde de conges sera encore positif après validation
    if ($_SESSION['config']['solde_toujours_positif']) {
        $valid = $valid && verif_solde_user($_SESSION['userlogin'], $new_type, $new_nb_jours);
    }

    if ($valid) {
        if (in_array(get_type_abs($new_type), array('conges', 'conges_exceptionnels'))) {
            $resp_du_user = get_tab_resp_du_user($_SESSION['userlogin']);
            if ((1 === count($resp_du_user) && isset($resp_du_user['conges'])) || empty($resp_du_user)) {
                $new_etat = 'ok';
                soustrait_solde_et_reliquat_user($_SESSION['userlogin'], "", $new_nb_jours, $new_type, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin);
            } else {
                $new_etat = 'demande';
            }
        } else {
            $new_etat = 'ok';
        }

        $new_comment = addslashes($new_comment);

        $periode_num = insert_dans_periode($_SESSION['userlogin'], $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $new_type, $new_etat, 0);

        if ($periode_num != 0) {
            $return .= schars(_('form_modif_ok')) . ' !<br><br>.';
            //envoi d'un mail d'alerte au responsable (si demandé dans config de php_conges)
            if ($_SESSION['config']['mail_new_demande_alerte_resp']) {
                if (in_array(get_type_abs($new_type), array('absences'))) {
                    alerte_mail($_SESSION['userlogin'], ":responsable:", $periode_num, "new_absence_conges");
                } else {
                    alerte_mail($_SESSION['userlogin'], ":responsable:", $periode_num, "new_demande");
                }
            }
        } else {
            $return .= schars(_('form_modif_not_ok')) . ' !<br><br>.';
        }
    } else {
        $return .= schars(_('resp_traite_user_valeurs_not_ok')) . ' !<br><br>.';
    }

    $return .= '<a class="btn" href="' . $PHP_SELF . '?session=' . $session . '">' . _('form_retour') . '</a>';

    return $return;
}

// renvoit le type d'absence (conges ou absence) d'une absence
function get_type_abs($_type_abs_id)
{
    $sql_abs    = 'SELECT ta_type FROM conges_type_absence WHERE ta_id="' . \includes\SQL::quote($_type_abs_id) . '"';
    $ReqLog_abs = \includes\SQL::query($sql_abs);

    if ($resultat_abs = $ReqLog_abs->fetch_array()) {
        return $resultat_abs["ta_type"];
    } else {
        return "";
    }

}

function verif_solde_user($user_login, $type_conges, $nb_jours)
{
    $verif = true;
    // on ne tient compte du solde que pour les absences de type conges (conges avec solde annuel)
    if (get_type_abs($type_conges) == "conges") {
        // recup du solde de conges de type $type_conges pour le user de login $user_login
        $select_solde        = 'SELECT su_solde FROM conges_solde_user WHERE su_login="' . \includes\SQL::quote($user_login) . '" AND su_abs_id=' . \includes\SQL::quote($type_conges);
        $ReqLog_solde_conges = \includes\SQL::query($select_solde);
        $resultat_solde      = $ReqLog_solde_conges->fetch_array();
        $sql_solde_user      = $resultat_solde["su_solde"];

        // recup du nombre de jours de conges de type $type_conges pour le user de login $user_login qui sont à valider par son resp ou le grd resp
        $select_solde_a_valider        = 'SELECT SUM(p_nb_jours) FROM conges_periode WHERE p_login="' . \includes\SQL::quote($user_login) . '" AND p_type=' . \includes\SQL::quote($type_conges) . ' AND (p_etat=\'demande\' OR p_etat=\'valid\') ';
        $ReqLog_solde_conges_a_valider = \includes\SQL::query($select_solde_a_valider);
        $resultat_solde_a_valider      = $ReqLog_solde_conges_a_valider->fetch_array();
        $sql_solde_user_a_valider      = $resultat_solde_a_valider["SUM(p_nb_jours)"];
        if ($sql_solde_user_a_valider == null) {
            $sql_solde_user_a_valider = 0;
        }

        // vérification du solde de jours de type $type_conges
        if ($sql_solde_user < $nb_jours + $sql_solde_user_a_valider) {
            echo '<p class="bg-danger">' . schars(_('verif_solde_erreur_part_1')) . ' (' . (float) schars($nb_jours) . ') ' . schars(_('verif_solde_erreur_part_2')) . ' (' . (float) schars($sql_solde_user) . ') ' . schars(_('verif_solde_erreur_part_3')) . ' (' . (float) schars($sql_solde_user_a_valider) . ')</p>' . "\n";
            $verif = false;
        }
    }
    return $verif;
}
