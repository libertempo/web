<?php

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

if (file_exists(CONFIG_PATH .'config_ldap.php')) {
    include CONFIG_PATH .'config_ldap.php';
}

function verif_year_deja_saisie($tab_checkbox_j_chome) : bool
{
    $db = \includes\SQL::singleton();
    $date_1=key($tab_checkbox_j_chome);
    $year=substr($date_1, 0, 4);
    $sql_select='SELECT jf_date FROM conges_jours_feries WHERE jf_date LIKE "'. $db->quote($year).'%" ;';
    $relog = $db->query($sql_select);
    return($relog->num_rows != 0);
}

function delete_year($tab_checkbox_j_chome) : bool
{
    $db = \includes\SQL::singleton();
    $date_1=key($tab_checkbox_j_chome);
    $year=substr($date_1, 0, 4);
    $sql_delete='DELETE FROM conges_jours_feries WHERE jf_date LIKE "'. $db->quote($year).'%" ;';
    $result = $db->query($sql_delete);

    return true;
}

function insert_year($tab_checkbox_j_chome) : bool
{
    $db = \includes\SQL::singleton();
    foreach($tab_checkbox_j_chome as $key => $value)
        $result = $db->query('INSERT INTO conges_jours_feries SET jf_date="'. $db->quote($key).'";');
    return true;
}

function commit_saisie($tab_checkbox_j_chome) : string
{
    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $return = '';

    // si l'année est déja renseignée dans la database, on efface ttes les dates de l'année
    if (verif_year_deja_saisie($tab_checkbox_j_chome)) {
        $result = delete_year($tab_checkbox_j_chome);
    }


    // on insert les nouvelles dates saisies
    $result = insert_year($tab_checkbox_j_chome);

    // on recharge les jours feries dans les variables de session
    init_tab_jours_feries();

    if ($result) {
        $return .= '<div class="alert alert-success">' . _('form_modif_ok') . '</div>';
    } else {
        $return .= '<div class="alert alert-danger">' . _('form_modif_not_ok') . '</div>';
    }

    $date_1=key($tab_checkbox_j_chome);
    $tab_date = explode('-', $date_1);
    $comment_log = "saisie des jours chomés pour ".$tab_date[0] ;
    log_action(0, "", "", $comment_log);
    return $return;
}

// retourne un tableau des jours feriés de l'année dans un tables passé par référence
function get_tableau_jour_feries($year) : array
{
    $db = \includes\SQL::singleton();
    $tab_year = [];
    $sql_select='SELECT jf_date FROM conges_jours_feries WHERE jf_date LIKE "'. $db->quote($year).'-%" ;';
    $res_select = $db->query($sql_select);
    $num_select = $res_select->num_rows;

    if ($num_select!=0) {
        while($result_select = $res_select->fetch_array()) {
            $tab_year[] = $result_select["jf_date"];
        }
    }

    return $tab_year;
}

//fonction de recherche des jours fériés de l'année demandée
// trouvée sur http://www.phpcs.com/codes/LISTE-JOURS-FERIES-ANNEE_32791.aspx
function fcListJourFeries($iAnnee = 2000) : array
{
    //Initialisation de variables
    $unJour = 3600*24;
    $tbJourFerie = array();
    $timePaques = easter_date($iAnnee) + 6 * 3600; // évite les changements d'heures

    $tbJourFerie["Jour de l an"] = $iAnnee . "-01-01";
    $tbJourFerie["Paques"] = date('Y-m-d', $timePaques);
    $tbJourFerie["Lundi de Paques"] = $iAnnee . date("-m-d", $timePaques + 1 * $unJour);
    $tbJourFerie["Fete du travail"] = $iAnnee . "-05-01";
    $tbJourFerie["Armistice 39-45"] = $iAnnee . "-05-08";
    $tbJourFerie["Jeudi de l ascension"] = $iAnnee . date("-m-d", easter_date($iAnnee) + 39 * $unJour);
    $tbJourFerie["Fete nationale"] = $iAnnee . "-07-14";
    $tbJourFerie["Assomption"] = $iAnnee . "-08-15";
    $tbJourFerie["Toussaint"] = $iAnnee . "-11-01";
    $tbJourFerie["Armistice 14-18"] = $iAnnee . "-11-11";
    $tbJourFerie["Noel"] = $iAnnee . "-12-25";

    return $tbJourFerie;
}

// affichage du calendrier du mois avec les case à cocher
// on lui passe en parametre le tableau des jour chomé de l'année (pour pré-cocher certaines cases)
function affiche_calendrier_saisie_jours_chomes($year, $mois, $joursFeries) : string
{
    $jour_today=date("j");
    $jour_today_name=date("D");
    $return = '';

    $first_jour_mois_timestamp = mktime(0,0,0,$mois,1,$year);
    $mois_name=date_fr("F", $first_jour_mois_timestamp);
    $first_jour_mois_rang=date("w", $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
    if ($first_jour_mois_rang==0) {
        $first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)
    }

    $return .= '<table>';
    /* affichage  2 premieres lignes */
    $return .= '<thead>';
    $return .= '<tr align="center"><th colspan=7 class="titre">' . $mois_name . ' ' . $year . '</th></tr>';
    $return .= '<tr>';
    $return .= '<th class="cal-saisie2">' . _('lundi_1c') . '</th>';
    $return .= '<th class="cal-saisie2">' . _('mardi_1c') . '</th>';
    $return .= '<th class="cal-saisie2">' . _('mercredi_1c') . '</th>';
    $return .= '<th class="cal-saisie2">' . _('jeudi_1c') . '</th>';
    $return .= '<th class="cal-saisie2">' . _('vendredi_1c') . '</th>';
    $return .= '<th class="cal-saisie2 weekend">' . _('samedi_1c') . '</th>';
    $return .= '<th class="cal-saisie2 weekend">' . _('dimanche_1c') . '</th>';
    $return .= '</tr>';
    $return .= '</thead>';

    /* affichage ligne 1 du mois*/
    $return .= '<tr>';
    // affichage des cellules vides jusqu'au 1 du mois ...
    for($i=1; $i<$first_jour_mois_rang; $i++) {
        $return .= \hr\Fonctions::affiche_jour_hors_mois($mois,$i,$year,$joursFeries);
    }
    // affichage des cellules cochables du 1 du mois à la fin de la ligne ...
    for($i=$first_jour_mois_rang; $i<8; $i++) {
        $j=$i-$first_jour_mois_rang+1;
        $return .= \hr\Fonctions::affiche_jour_checkbox($mois,$j,$year,$joursFeries);
    }
    $return .= '</tr>';

    /* affichage ligne 2 du mois*/
    $return .= '<tr>';
    for ($i=8-$first_jour_mois_rang+1; $i<15-$first_jour_mois_rang+1; $i++) {
        $return .= \hr\Fonctions::affiche_jour_checkbox($mois,$i,$year,$joursFeries);
    }
    $return .= '</tr>';

    /* affichage ligne 3 du mois*/
    $return .= '<tr>';
    for($i=15-$first_jour_mois_rang+1; $i<22-$first_jour_mois_rang+1; $i++) {
        $return .= \hr\Fonctions::affiche_jour_checkbox($mois,$i,$year,$joursFeries);
    }
    $return .= '</tr>';

    /* affichage ligne 4 du mois*/
    $return .= '<tr>';
    for($i=22-$first_jour_mois_rang+1; $i<29-$first_jour_mois_rang+1; $i++) {
        $return .= \hr\Fonctions::affiche_jour_checkbox($mois,$i,$year,$joursFeries);
    }
    $return .= '</tr>';

    /* affichage ligne 5 du mois (peut etre la derniere ligne) */
    $return .= '<tr>';
    for($i=29-$first_jour_mois_rang+1; $i<36-$first_jour_mois_rang+1 && checkdate($mois, $i, $year); $i++) {
        $return .= \hr\Fonctions::affiche_jour_checkbox($mois,$i,$year,$joursFeries);
    }

    for ($i; $i<36-$first_jour_mois_rang+1; $i++) {
        $return .= \hr\Fonctions::affiche_jour_hors_mois($mois,$i,$year,$joursFeries);
    }
    $return .= '</tr>';

    /* affichage ligne 6 du mois (derniere ligne)*/
    $return .= '<tr>';
    for($i=36-$first_jour_mois_rang+1; checkdate($mois, $i, $year); $i++) {
        $return .= \hr\Fonctions::affiche_jour_checkbox($mois,$i,$year,$joursFeries);
    }

    for($i; $i<43-$first_jour_mois_rang+1; $i++) {
        $return .= \hr\Fonctions::affiche_jour_hors_mois($mois,$i,$year,$joursFeries);
    }
    $return .= '</tr></table>';

    return $return;
}

// verif des droits du user à afficher la page
verif_droits_user( "is_hr");

$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
// GET / POST
$choix_action = getpost_variable('choix_action');
$year_calendrier_saisie = getpost_variable('year_calendrier_saisie', date("Y"));
$checkbox = getpost_variable('tab_checkbox_j_chome');
$tab_checkbox_j_chome = (!is_array($checkbox) || empty($checkbox)) ? [] : $checkbox;
/*************************************/


$title = _('admin_button_jours_chomes_1');

$prev_link = "$PHP_SELF?onglet=jours_chomes&year_calendrier_saisie=". ($year_calendrier_saisie - 1);
$next_link = "$PHP_SELF?onglet=jours_chomes&year_calendrier_saisie=". ($year_calendrier_saisie + 1);


$sql = \includes\SQL::singleton();
$config = new \App\Libraries\Configuration($sql);


// on construit le tableau des jours feries de l'année considérée
$joursFeries = get_tableau_jour_feries($year_calendrier_saisie);

//calcul automatique des jours feries
if ($config->isJoursFeriesFrance()) {
    $tableau_jour_feries = fcListJourFeries($year_calendrier_saisie) ;
    foreach ($tableau_jour_feries as $value) {
        if (!in_array($value, $joursFeries))
            $joursFeries[] = $value;
    }
}

$months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
$i = 0;
$idLine = 0;
$linesMois = [];
foreach ($months as $month) {
    if ($i%4 == 0) {
        $idLine++;
    }
    $linesMois[$idLine][] = affiche_calendrier_saisie_jours_chomes($year_calendrier_saisie, $month, $joursFeries);
    $i++;
}

require_once VIEW_PATH . 'JourFerie.php';
