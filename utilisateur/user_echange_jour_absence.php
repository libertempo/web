<?php

defined('_PHP_CONGES') or die('Restricted access');
echo echangeJourAbsenceModule($onglet);
/**
 * Encapsule le comportement du module d'échange d'absence
 *
 * @param string $onglet Nom de l'onglet à afficher
 *
 * @return void
 * @access public
 * @static
 */
function echangeJourAbsenceModule($onglet)
{
    $return = '';
    init_tab_jours_feries();

    $new_echange_rtt = getpost_variable('new_echange_rtt', 0);

    if ($new_echange_rtt == 1 && $_SESSION['config']['user_echange_rtt']) {

        $new_debut                = getpost_variable('new_debut');
        $new_fin                  = getpost_variable('new_fin');
        $new_comment              = getpost_variable('new_comment');
        $moment_absence_ordinaire = getpost_variable('moment_absence_ordinaire');
        $moment_absence_souhaitee = getpost_variable('moment_absence_souhaitee');

        $return .= echange_absence_rtt($onglet, $new_debut, $new_fin, $new_comment, $moment_absence_ordinaire, $moment_absence_souhaitee);
    } else {

        $year_calendrier_saisie_debut = getpost_variable('year_calendrier_saisie_debut', date('Y'));
        $mois_calendrier_saisie_debut = getpost_variable('mois_calendrier_saisie_debut', date('m'));
        $year_calendrier_saisie_fin   = getpost_variable('year_calendrier_saisie_fin', date('Y'));
        $mois_calendrier_saisie_fin   = getpost_variable('mois_calendrier_saisie_fin', date('m'));

        $return .= '<h1>' . _('user_echange_rtt') . '</h1>';
        if (!hasUserPlanning($_SESSION['userlogin'])) {
            $return .= '<div class="alert alert-danger">' . _('aucun_planning_associe_utilisateur') . '</div>';

        } else {
            //affiche le formulaire de saisie d'une nouvelle demande de conges
            $return .= saisie_echange_rtt($_SESSION['userlogin'], $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet);
        }
    }

    return $return;
}

function echange_absence_rtt($onglet, $new_debut_string, $new_fin_string, $new_comment, $moment_absence_ordinaire, $moment_absence_souhaitee)
{
    $return = '';

    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $session  = session_id();

    $duree_demande_1 = "";
    $duree_demande_2 = "";
    $valid           = true;

    // verif si les dates sont renseignées  (si ce n'est pas le cas, on ne verifie meme pas la suite !)
    // $new_debut et $new_fin sont des string au format : $year-$mois-$jour-X  (avec X = j pour "jour entier", a pour "a" (matin), et p pour "pm" (apres midi) )
    if (($new_debut_string == "") || ($new_fin_string == "")) {
        $valid = false;
    } else {
        $date_1          = explode("-", $new_debut_string);
        $year_debut      = $date_1[0];
        $mois_debut      = $date_1[1];
        $jour_debut      = $date_1[2];
        $demi_jour_debut = $date_1[3];

        $new_debut = "$year_debut-$mois_debut-$jour_debut";

        $date_2        = explode("-", $new_fin_string);
        $year_fin      = $date_2[0];
        $mois_fin      = $date_2[1];
        $jour_fin      = $date_2[2];
        $demi_jour_fin = $date_2[3];

        $new_fin = "$year_fin-$mois_fin-$jour_fin";

        /********************************************/
        // traitement du jour d'absence à remplacer

        // verif de la concordance des demandes avec l'existant, et affectation de valeurs à entrer dans la database
        if ($demi_jour_debut == "j") // on est absent la journee
        {
            if ($moment_absence_ordinaire == "j") // on demande à etre present tte la journee
            {
                $nouvelle_presence_date_1 = "J";
                $nouvelle_absence_date_1  = "N";
                $duree_demande_1          = "jour";
            } elseif ($moment_absence_ordinaire == "a") // on demande à etre present le matin
            {
                $nouvelle_presence_date_1 = "M";
                $nouvelle_absence_date_1  = "A";
                $duree_demande_1          = "demi";
            } elseif ($moment_absence_ordinaire == "p") // on demande à etre present l'aprem
            {
                $nouvelle_presence_date_1 = "A";
                $nouvelle_absence_date_1  = "M";
                $duree_demande_1          = "demi";
            }
        } elseif ($demi_jour_debut == "a") // on est absent le matin
        {
            if ($moment_absence_ordinaire == "j") // on demande à etre present tte la journee
            {
                $nouvelle_presence_date_1 = "J";
                $nouvelle_absence_date_1  = "N";
                $duree_demande_1          = "demi";
            } elseif ($moment_absence_ordinaire == "a") // on demande à etre present le matin
            {
                if ($new_debut == $new_fin) // dans ce cas, on veut intervertir 2 demi-journées
                {
                    $nouvelle_presence_date_1 = "M";
                    $nouvelle_absence_date_1  = "A";
                } else {
                    $nouvelle_presence_date_1 = "J";
                    $nouvelle_absence_date_1  = "N";
                }
                $duree_demande_1 = "demi";
            } elseif ($moment_absence_ordinaire == "p") // on demande à etre present l'aprem
            {
                $valid = false;
            }
        } elseif ($demi_jour_debut == "p") // on est absent l'aprem
        {
            if ($moment_absence_ordinaire == "j") // on demande à etre present tte la journee
            {
                $nouvelle_presence_date_1 = "J";
                $nouvelle_absence_date_1  = "N";
                $duree_demande_1          = "demi";
            } elseif ($moment_absence_ordinaire == "a") // on demande à etre present le matin
            {
                $valid = false;
            } elseif ($moment_absence_ordinaire == "p") // on demande à etre present l'aprem
            {
                if ($new_debut == $new_fin) // dans ce cas, on veut intervertir 2 demi-journées
                {
                    $nouvelle_presence_date_1 = "A";
                    $nouvelle_absence_date_1  = "M";
                } else {
                    $nouvelle_presence_date_1 = "J";
                    $nouvelle_absence_date_1  = "N";
                }
                $duree_demande_1 = "demi";
            }
        } else {
            $valid = false;
        }

        /**********************************************/
        // traitement du jour de présence à remplacer

        // verif de la concordance des demandes avec l'existant, et affectation de valeurs à entrer dans la database
        if ($demi_jour_fin == "j") // on est present la journee
        {
            if ($moment_absence_souhaitee == "j") // on demande à etre absent tte la journee
            {
                $nouvelle_presence_date_2 = "N";
                $nouvelle_absence_date_2  = "J";
                $duree_demande_2          = "jour";
            } elseif ($moment_absence_souhaitee == "a") // on demande à etre absent le matin
            {
                $nouvelle_presence_date_2 = "A";
                $nouvelle_absence_date_2  = "M";
                $duree_demande_2          = "demi";
            } elseif ($moment_absence_souhaitee == "p") // on demande à etre absent l'aprem
            {
                $nouvelle_presence_date_2 = "M";
                $nouvelle_absence_date_2  = "A";
                $duree_demande_2          = "demi";
            }
        } elseif ($demi_jour_fin == "a") // on est present le matin
        {
            if ($moment_absence_souhaitee == "j") // on demande à etre absent tte la journee
            {
                $nouvelle_presence_date_2 = "N";
                $nouvelle_absence_date_2  = "J";
                $duree_demande_2          = "demi";
            } elseif ($moment_absence_souhaitee == "a") // on demande à etre absent le matin
            {
                if ($new_debut == $new_fin) // dans ce cas, on veut intervertir 2 demi-journées
                {
                    $nouvelle_presence_date_2 = "A";
                    $nouvelle_absence_date_2  = "M";
                } else {
                    $nouvelle_presence_date_2 = "N";
                    $nouvelle_absence_date_2  = "j";
                }
                $duree_demande_2 = "demi";
            } elseif ($moment_absence_souhaitee == "p") // on demande à etre absent l'aprem
            {
                $valid = false;
            }
        } elseif ($demi_jour_fin == "p") // on est present l'aprem
        {
            if ($moment_absence_souhaitee == "j") // on demande à etre absent tte la journee
            {
                $nouvelle_presence_date_2 = "N";
                $nouvelle_absence_date_2  = "J";
                $duree_demande_2          = "demi";
            } elseif ($moment_absence_souhaitee == "a") // on demande à etre absent le matin
            {
                $valid = false;
            } elseif ($moment_absence_souhaitee == "p") // on demande à etre absent l'aprem
            {
                if ($new_debut == $new_fin) // dans ce cas, on veut intervertir 2 demi-journées
                {
                    $nouvelle_presence_date_2 = "M";
                    $nouvelle_absence_date_2  = "A";
                } else {
                    $nouvelle_presence_date_2 = "N";
                    $nouvelle_absence_date_2  = "J";
                }
                $duree_demande_2 = "demi";
            }
        } else {
            $valid = false;
        }

        // verif de la concordance des durée (journée avec journée ou 1/2 journée avec1/2 journée)
        if (($duree_demande_1 == "") || ($duree_demande_2 == "") || ($duree_demande_1 != $duree_demande_2)) {
            $valid = false;
        }

    }

    if ($valid) {
        $return .= schars($_SESSION['userlogin']) . ' --- ' . schars($new_debut) . ' --- ' . schars($new_fin) . ' --- ' . schars($new_comment) . '<br>';

        // insert du jour d'absence ordinaire (qui n'en sera plus un ou qu'a moitie ...)
        // e_presence = N (non) , J (jour entier) , M (matin) ou A (apres-midi)
        // verif si le couple user/date1 existe dans conges_echange_rtt ...
        $sql_verif_echange1    = 'SELECT e_absence, e_presence from conges_echange_rtt WHERE e_login="' . \includes\SQL::quote($_SESSION['userlogin']) . '" AND e_date_jour="' . \includes\SQL::quote($new_debut) . '";';
        $result_verif_echange1 = \includes\SQL::query($sql_verif_echange1);

        $count_verif_echange1 = $result_verif_echange1->num_rows;

        // si le couple user/date1 existe dans conges_echange_rtt : on update
        if ($count_verif_echange1 != 0) {
            $new_comment = addslashes($new_comment);
            //$resultat1=$result_verif_echange1->fetch_array();
            //if($resultatverif_echange1['e_absence'] == 'N' )
            $sql1 = 'UPDATE conges_echange_rtt
                    SET e_absence=\'' . $nouvelle_absence_date_1 . '\', e_presence=\'' . $nouvelle_presence_date_1 . '\', e_comment=\'' . $new_comment . '\'
                    WHERE e_login=\'' . $_SESSION['userlogin'] . '\' AND e_date_jour="' . \includes\SQL::quote($new_debut) . '"  ';
        } else // sinon : on insert
        {
            $sql1 = "INSERT into conges_echange_rtt (e_login, e_date_jour, e_absence, e_presence, e_comment)
                    VALUES ('" . $_SESSION['userlogin'] . "','$new_debut','$nouvelle_absence_date_1', '$nouvelle_presence_date_1', '$new_comment')";
        }
        $result1 = \includes\SQL::query($sql1);

        // insert du jour d'absence souhaité (qui en devient un)
        // e_absence = N (non) , J (jour entier) , M (matin) ou A (apres-midi)
        // verif si le couple user/date2 existe dans conges_echange_rtt ...
        $sql_verif_echange2    = 'SELECT e_absence, e_presence from conges_echange_rtt WHERE e_login="' . \includes\SQL::quote($_SESSION['userlogin']) . '" AND e_date_jour="' . \includes\SQL::quote($new_fin) . '";';
        $result_verif_echange2 = \includes\SQL::query($sql_verif_echange2);

        $count_verif_echange2 = $result_verif_echange2->num_rows;

        // si le couple user/date2 existe dans conges_echange_rtt : on update
        if ($count_verif_echange2 != 0) {
            $sql2 = 'UPDATE conges_echange_rtt
                    SET e_absence=\'' . $nouvelle_absence_date_2 . '\', e_presence=\'' . $nouvelle_presence_date_2 . '\', e_comment=\'' . $new_comment . '\'
                    WHERE e_login=\'' . $_SESSION['userlogin'] . '\' AND e_date_jour=\'' . $new_fin . '\' ';
        } else // sinon: on insert
        {
            $sql2 = "INSERT into conges_echange_rtt (e_login, e_date_jour, e_absence, e_presence, e_comment)
                    VALUES ('" . $_SESSION['userlogin'] . "','$new_fin','$nouvelle_absence_date_2', '$nouvelle_presence_date_2', '$new_comment')";
        }
        $result2 = \includes\SQL::query($sql2);

        $comment_log = "echange absence - rtt  ($new_debut_string / $new_fin_string)";
        log_action(0, "", $_SESSION['userlogin'], $comment_log);

        if (($result1) && ($result2)) {
            $return .= 'Changements pris en compte avec succes !<br><br>';
        } else {
            $return .= 'ERREUR ! Une erreur s\'est produite : contactez votre responsable !<br><br>';
        }

    } else {
        $return .= 'ERREUR ! Les valeurs saisies sont invalides ou manquantes  !!!<br><br>';
    }

    /* RETOUR PAGE PRINCIPALE */
    $return .= '<form action="' . $PHP_SELF . '?session=' . $session . '&onglet=' . $onglet . '" method="POST">';
    $return .= '<input type="submit" value="Retour">';
    $return .= '</form>';

    return $return;
}

/**
 * Retourne vrai si l'utilisateur a un planning associé
 *
 * @param string $user
 *
 * @return bool
 */
function hasUserPlanning($user)
{
    $sql = \includes\SQL::singleton();
    $req = 'SELECT EXISTS (
            SELECT planning_id
            FROM conges_users
                INNER JOIN planning USING (planning_id)
            WHERE u_login ="' . $sql->quote($user) . '"
            AND planning.status = ' . \App\Models\Planning::STATUS_ACTIVE . '
        )';
    $query = $sql->query($req);

    return 0 < (int) $query->fetch_array()[0];
}

//affiche le formulaire d'échange d'un jour de rtt-temps partiel / jour travaillé
function saisie_echange_rtt($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet)
{
    $return                            = '';
    $PHP_SELF                          = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $session                           = session_id();
    $mois_calendrier_saisie_debut_prec = 0;
    $year_calendrier_saisie_debut_prec = 0;
    $mois_calendrier_saisie_debut_suiv = 0;
    $year_calendrier_saisie_debut_suiv = 0;
    $mois_calendrier_saisie_fin_prec   = 0;
    $year_calendrier_saisie_fin_prec   = 0;
    $mois_calendrier_saisie_fin_suiv   = 0;
    $year_calendrier_saisie_fin_suiv   = 0;

    $return .= '<form action="' . $PHP_SELF . '?session=' . $session . '&&onglet=' . $onglet . '" method="POST">';

    $return .= '<table class="table table condensed">';
    $return .= '<tr align="center">';

    // cellule 1 : calendrier de saisie du jour d'absence
    $return .= '<td class="cell-top">';
    $return .= '<table class="table table-bordered table-calendar">';
    $return .= '<tr>';
    init_var_navigation_mois_year($mois_calendrier_saisie_debut, $year_calendrier_saisie_debut,
        $mois_calendrier_saisie_debut_prec, $year_calendrier_saisie_debut_prec,
        $mois_calendrier_saisie_debut_suiv, $year_calendrier_saisie_debut_suiv,
        $mois_calendrier_saisie_fin, $year_calendrier_saisie_fin,
        $mois_calendrier_saisie_fin_prec, $year_calendrier_saisie_fin_prec,
        $mois_calendrier_saisie_fin_suiv, $year_calendrier_saisie_fin_suiv);

    // affichage des boutons de défilement
    // recul du mois saisie debut
    $return .= '<td align="center">';
    $return .= '<a href="' . $PHP_SELF . '?session=' . $session . '&year_calendrier_saisie_debut=' . $year_calendrier_saisie_debut_prec . '&mois_calendrier_saisie_debut=' . $mois_calendrier_saisie_debut_prec . '&year_calendrier_saisie_fin=' . $year_calendrier_saisie_fin . '&mois_calendrier_saisie_fin=' . $mois_calendrier_saisie_fin . '&user_login=' . $user_login . '&onglet=' . $onglet . '">';
    $return .= '<i class="fa fa-chevron-circle-left"></i>';
    $return .= '</a>';
    $return .= '</td>';

    // titre du calendrier de saisie du jour d'absence
    $return .= '<td align="center">' . _('saisie_echange_titre_calendrier_1') . '</td>';

    // affichage des boutons de défilement
    // avance du mois saisie debut
    $return .= '<td align="center">';
    $return .= '<a href="' . $PHP_SELF . '?session=' . $session . '&year_calendrier_saisie_debut=' . $year_calendrier_saisie_debut_suiv . '&mois_calendrier_saisie_debut=' . $mois_calendrier_saisie_debut_suiv . '&year_calendrier_saisie_fin=' . $year_calendrier_saisie_fin . '&mois_calendrier_saisie_fin=' . $mois_calendrier_saisie_fin . '&user_login=' . $user_login . '&onglet=' . $onglet . '">';
    $return .= '<i class="fa fa-chevron-circle-right"></i>';
    $return .= '</a>';
    $return .= '</td>';
    $return .= '</tr>';
    $return .= '<tr>';
    $return .= '<td colspan="3">';
    //*** calendrier saisie date debut ***/
    $return .= affiche_calendrier_saisie_jour_absence($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut);
    $return .= '</td>';
    $return .= '</tr>';
    $return .= '</table>';
    $return .= '</td>';

    // cellule 2 : boutons radio 1/2 journée ou jour complet
    $return .= '<td class="day-period">';
    $return .= '<div><input type="radio" name="moment_absence_ordinaire" value="a"><label>' . _('form_am') . '</label><input type="radio" name="moment_absence_souhaitee" value="a"></div>';
    $return .= '<input type="radio" name="moment_absence_ordinaire" value="p"><label>' . _('form_pm') . '</label><input type="radio" name="moment_absence_souhaitee" value="p"></div>';
    $return .= '<div><input type="radio" name="moment_absence_ordinaire" value="j" checked><label>' . _('form_day') . '</label><input type="radio" name="moment_absence_souhaitee" value="j" checked></div>';
    $return .= '</td>';

    // cellule 3 : calendrier de saisie du jour d'absence
    $return .= '<td class="cell-top">';
    $return .= '<table class="table table-bordered table-calendar">';
    $return .= '<tr>';
    $mois_calendrier_saisie_fin_prec = $mois_calendrier_saisie_fin == 1 ? 12 : $mois_calendrier_saisie_fin - 1;
    $mois_calendrier_saisie_fin_suiv = $mois_calendrier_saisie_fin == 12 ? 1 : $mois_calendrier_saisie_fin + 1;

    // affichage des boutons de défilement
    // recul du mois saisie fin
    $return .= '<td align="center">';
    $return .= '<a href="' . $PHP_SELF . '?session=' . $session . '&year_calendrier_saisie_debut=' . $year_calendrier_saisie_debut . '&mois_calendrier_saisie_debut=' . $mois_calendrier_saisie_debut . '&year_calendrier_saisie_fin=' . $year_calendrier_saisie_fin_prec . '&mois_calendrier_saisie_fin=' . $mois_calendrier_saisie_fin_prec . '&user_login=' . $user_login . '&onglet=' . $onglet . '">';
    $return .= '<i class="fa fa-chevron-circle-left"></i>';
    $return .= '</a>';
    $return .= '</td>';

    // titre du ecalendrier de saisie du jour d'absence
    $return .= '<td align="center">' . _('saisie_echange_titre_calendrier_2') . '</td>';

    // affichage des boutons de défilement
    // avance du mois saisie fin
    $return .= '<td align="center">';
    $return .= '<a href="' . $PHP_SELF . '?session=' . $session . '&year_calendrier_saisie_debut=' . $year_calendrier_saisie_debut . '&mois_calendrier_saisie_debut=' . $mois_calendrier_saisie_debut . '&year_calendrier_saisie_fin=' . $year_calendrier_saisie_fin_suiv . '&mois_calendrier_saisie_fin=' . $mois_calendrier_saisie_fin_suiv . '&user_login=' . $user_login . '&onglet=' . $onglet . '">';
    $return .= '<i class="fa fa-chevron-circle-right"></i>';
    $return .= '</a>';
    $return .= '</td>';
    $return .= '</tr>';
    $return .= '<tr>';
    $return .= '<td colspan="3">';
    //*** calendrier saisie date fin ***/
    $return .= affiche_calendrier_saisie_jour_presence($user_login, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin);
    $return .= '</td>';
    $return .= '</tr>';
    $return .= '</table>';
    $return .= '</td>';
    $return .= '</tr>';
    $return .= '</table>';
    $return .= "<hr/>\n";
    // cellule 1 : champs texte et boutons (valider/cancel)
    $return .= '<label>' . _('divers_comment_maj_1') . '</label><input class="form-control" type="text" name="new_comment" size="25" maxlength="30" value="">';
    $return .= "<hr/>\n";
    $return .= '<input type="hidden" name="user_login" value="' . schars($user_login) . '">';
    $return .= '<input type="hidden" name="new_echange_rtt" value=1>';
    $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
    $return .= "<a class=\"btn\" href=\"$PHP_SELF?session=$session\">" . _('form_cancel') . "</a>\n";
    $return .= '</form>';

    return $return;
}

//affichage du calendrier du mois avec les case à cocher sur les jour d'absence
function affiche_calendrier_saisie_jour_absence($user_login, $year, $mois)
{
    $return          = '';
    $jour_today      = date('j');
    $jour_today_name = date('D');

    $first_jour_mois_timestamp = mktime(0, 0, 0, $mois, 1, $year);
    $last_jour_mois_timestamp  = mktime(0, 0, 0, $mois + 1, 0, $year);

    $mois_name = date_fr('F', $first_jour_mois_timestamp);

    $first_jour_mois_rang = date('w', $first_jour_mois_timestamp); // jour de la semaine en chiffre (0=dim , 6=sam)
    $last_jour_mois_rang  = date('w', $last_jour_mois_timestamp); // jour de la semaine en chiffre (0=dim , 6=sam)
    $nb_jours_mois        = ($last_jour_mois_timestamp - $first_jour_mois_timestamp + 60 * 60 * 12) / (24 * 60 * 60); // + 60*60 *12 for fucking DST

    if ($first_jour_mois_rang == 0) {
        $first_jour_mois_rang = 7;
    }
    // jour de la semaine en chiffre (1=lun , 7=dim)

    if ($last_jour_mois_rang == 0) {
        $last_jour_mois_rang = 7;
    }
    // jour de la semaine en chiffre (1=lun , 7=dim)

    $return .= '<table class="table calendrier_saisie_date">';
    $return .= '<thead><tr><th colspan="7" class="titre"> ' . $mois_name . ' ' . $year . ' </th></tr><tr><th class="cal-saisie2">' . _('lundi_1c') . '</th><th class="cal-saisie2">' . _('mardi_1c') . '</th><th class="cal-saisie2">' . _('mercredi_1c') . '</th><th class="cal-saisie2">' . _('jeudi_1c') . '</th><th class="cal-saisie2">' . _('vendredi_1c') . '</th><th class="cal-saisie2">' . _('samedi_1c') . '</th><th class="cal-saisie2">' . _('dimanche_1c') . '</th></tr></thead>';
    $return .= '<tbody>';

    $start_nb_day_before = $first_jour_mois_rang - 1;
    $stop_nb_day_before  = 7 - $last_jour_mois_rang;
    $planningUser        = \utilisateur\Fonctions::getUserPlanning($user_login);

    for ($i = -$start_nb_day_before; $i <= $nb_jours_mois + $stop_nb_day_before; $i++) {
        if (($i + $start_nb_day_before) % 7 == 0) {
            $return .= '<tr>';
        }

        $j_timestamp     = mktime(0, 0, 0, $mois, $i + 1, $year);
        $td_second_class = get_td_class_of_the_day_in_the_week($j_timestamp);

        if ($i < 0 || $i > $nb_jours_mois || $td_second_class == 'weekend') {
            $return .= '<td class="' . $td_second_class . '">-</td>';
        } else {
            $val_matin = '';
            $val_aprem = '';
            recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $planningUser);
            $return .= affiche_cellule_calendrier_echange_absence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i + 1);
        }

        if (($i + $start_nb_day_before) % 7 == 6) {
            $return .= '<tr>';
        }

    }

    $return .= '</tbody>';
    $return .= '</table>';

    return $return;
}

// affichage du calendrier du mois avec les case à cocher sur les jour de présence
function affiche_calendrier_saisie_jour_presence($user_login, $year, $mois)
{
    $return          = '';
    $jour_today      = date('j');
    $jour_today_name = date('D');

    $first_jour_mois_timestamp = mktime(0, 0, 0, $mois, 1, $year);
    $last_jour_mois_timestamp  = mktime(0, 0, 0, $mois + 1, 0, $year);

    $mois_name = date_fr('F', $first_jour_mois_timestamp);

    $first_jour_mois_rang = date('w', $first_jour_mois_timestamp); // jour de la semaine en chiffre (0=dim , 6=sam)
    $last_jour_mois_rang  = date('w', $last_jour_mois_timestamp); // jour de la semaine en chiffre (0=dim , 6=sam)
    $nb_jours_mois        = ($last_jour_mois_timestamp - $first_jour_mois_timestamp + 60 * 60 * 12) / (24 * 60 * 60); // + 60*60 *12 for fucking DST

    if ($first_jour_mois_rang == 0) {
        $first_jour_mois_rang = 7;
    }
    // jour de la semaine en chiffre (1=lun , 7=dim)

    if ($last_jour_mois_rang == 0) {
        $last_jour_mois_rang = 7;
    }
    // jour de la semaine en chiffre (1=lun , 7=dim)

    $return .= '<table class="table calendrier_saisie_date">';
    $return .= '<thead>
            <tr>
            <th colspan="7" class="titre"> ' . $mois_name . ' ' . $year . ' </th>
            </tr>
            <tr>
            <th class="cal-saisie2">' . _('lundi_1c') . '</th>
            <th class="cal-saisie2">' . _('mardi_1c') . '</th>
            <th class="cal-saisie2">' . _('mercredi_1c') . '</th>
            <th class="cal-saisie2">' . _('jeudi_1c') . '</th>
            <th class="cal-saisie2">' . _('vendredi_1c') . '</th>
            <th class="cal-saisie2">' . _('samedi_1c') . '</th>
            <th class="cal-saisie2">' . _('dimanche_1c') . '</th>
            </tr>
            </thead>';
    $return .= '<tbody>';

    $start_nb_day_before = $first_jour_mois_rang - 1;
    $stop_nb_day_before  = 7 - $last_jour_mois_rang;
    $planningUser        = \utilisateur\Fonctions::getUserPlanning($user_login);

    for ($i = -$start_nb_day_before; $i <= $nb_jours_mois + $stop_nb_day_before; $i++) {
        if (($i + $start_nb_day_before) % 7 == 0) {
            $return .= '<tr>';
        }

        $j_timestamp     = mktime(0, 0, 0, $mois, $i + 1, $year);
        $td_second_class = get_td_class_of_the_day_in_the_week($j_timestamp);

        if ($i < 0 || $i > $nb_jours_mois || $td_second_class == 'weekend') {
            $return .= '<td class="' . $td_second_class . '">-</td>';
        } else {
            $val_matin = '';
            $val_aprem = '';
            recup_infos_artt_du_jour($user_login, $j_timestamp, $val_matin, $val_aprem, $planningUser);
            $return .= affiche_cellule_calendrier_echange_presence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $i + 1);
        }

        if (($i + $start_nb_day_before) % 7 == 6) {
            $return .= '<tr>';
        }

    }

    $return .= '</tbody>';
    $return .= '</table>';

    return $return;
}

function affiche_cellule_calendrier_echange_absence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $j)
{
    $return  = '';
    $bgcolor = $_SESSION['config']['temps_partiel_bgcolor'];
    if ($val_matin == 'Y' && $val_aprem == 'Y') {
        $return .= '<td bgcolor=' . $bgcolor . ' class="cal-saisie">' . $j . '<input type="radio" name="new_debut" value="' . $year . '-' . $mois . '-' . $j . '-j"></td>';
    } elseif ($val_matin == 'Y' && $val_aprem == 'N') {
        $return .= '<td bgcolor=' . $bgcolor . ' class="cal-day_semaine_rtt_am_travail_pm_w35">' . $j . '<input type="radio" name="new_debut" value="' . $year . '-' . $mois . '-' . $j . '-a"></td>';
    } elseif ($val_matin == 'N' && $val_aprem == 'Y') {
        $return .= '<td bgcolor=' . $bgcolor . ' class="cal-day_semaine_travail_am_rtt_pm_w35">' . $j . '<input type="radio" name="new_debut" value="' . $year . '-' . $mois . '-' . $j . '-p"></td>';
    } else {
        $bgcolor = $_SESSION['config']['semaine_bgcolor'];
        $return .= '<td bgcolor=' . $bgcolor . ' class="cal-saisie">' . $j . '</td>';
    }
    return $return;
}

function affiche_cellule_calendrier_echange_presence_saisie_semaine($val_matin, $val_aprem, $year, $mois, $j)
{
    $return  = '';
    $bgcolor = $_SESSION['config']['temps_partiel_bgcolor'];
    if ($val_matin == 'Y' && $val_aprem == 'Y') // rtt le matin et l'apres midi !
    {
        $return .= '<td bgcolor=' . $bgcolor . ' class="cal-saisie">' . $j . '</td>';
    } elseif ($val_matin == 'Y' && $val_aprem == 'N') {
        $return .= '<td bgcolor=' . $bgcolor . ' class="cal-day_semaine_rtt_am_travail_pm_w35">' . $j . '<input type="radio" name="new_fin" value="' . $year . '-' . $mois . '-' . $j . '-p"></td>';
    } elseif ($val_matin == 'N' && $val_aprem == 'Y') {
        $return .= '<td bgcolor=' . $bgcolor . ' class="cal-day_semaine_travail_am_rtt_pm_w35">' . $j . '<input type="radio" name="new_fin" value="' . $year . '-' . $mois . '-' . $j . '-a"></td>';
    } else {
        $bgcolor = $_SESSION['config']['semaine_bgcolor'];
        $return .= '<td bgcolor=' . $bgcolor . ' class="cal-saisie">' . $j . '<input type="radio" name="new_fin" value="' . $year . '-' . $mois . '-' . $j . '-j"></td>';
    }

    return $return;
}
