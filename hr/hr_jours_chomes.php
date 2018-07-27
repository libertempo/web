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
        delete_year($tab_checkbox_j_chome);
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
    $sql = \includes\SQL::singleton();
    $config = new \App\Libraries\Configuration($sql);
    $injectableCreator = new \App\Libraries\InjectableCreator($sql, $config);
    $api = $injectableCreator->get(\App\Libraries\ApiClient::class);
    $feries = $api->get('jour_ferie', $_SESSION['token'])['data'];

    $feriesAnnee = array_filter($feries, function ($ferie) use ($year) {
        return false !== stripos($ferie['date'], $year);
    });

    return array_map(function ($ferie) {
        return $ferie['date'];
    }, $feriesAnnee);
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

function afficheJourHorsMois($mois, $i, $year, $tab_year) : string
{
    $j_timestamp = mktime(0,0,0,$mois,$i,$year);
    $td_second_class = get_td_class_of_the_day_in_the_week($j_timestamp);
    return '<td class="cal-saisie2 month-out ' . $td_second_class . '">&nbsp;</td>';
}

function afficheJourMois($mois, $i, $year, $tab_year) : string
{
    $j_timestamp = mktime(0,0,0,$mois,$i,$year);
    $j_date = date("Y-m-d", $j_timestamp);
    $j_day = date("d", $j_timestamp);
    $td_second_class = get_td_class_of_the_day_in_the_week($j_timestamp);
    $checked = in_array("$j_date", $tab_year);

    return '<td class="cal-saisie ' . $td_second_class . ' ' . (($checked) ? ' fermeture' : '') . '">' . $j_day . '<input type="checkbox" name="tab_checkbox_j_chome[' . $j_date . ']" value="Y" ' . (($checked) ? ' checked' : '') . '></td>';
}

function isJourLundi($jour) : bool
{
    return $jour % 7 == 1;
}


// verif des droits du user à afficher la page
verif_droits_user( "is_hr");

$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
// GET / POST
$choix_action = getpost_variable('choix_action');
$year_calendrier_saisie = getpost_variable('year_calendrier_saisie', date("Y"));
$checkbox = getpost_variable('tab_checkbox_j_chome');
$tab_checkbox_j_chome = (!is_array($checkbox) || empty($checkbox)) ? [] : $checkbox;
$message = ($choix_action == "commit")
    ? commit_saisie($tab_checkbox_j_chome)
    : null;
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

$listeMois = [['01', '02', '03', '04'], ['05', '06', '07', '08'], ['09', '10', '11', '12']];


require_once VIEW_PATH . 'JourFerie/Liste.php';
