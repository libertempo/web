<?php declare(strict_types = 1);

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

if (file_exists(CONFIG_PATH .'config_ldap.php')) {
    include CONFIG_PATH .'config_ldap.php';
}

function commitSaisie(array $tab_checkbox_j_chome) : bool
{
    // si l'année est déja renseignée dans la database, on efface ttes les dates de l'année
    if (isAnneeSaisie($tab_checkbox_j_chome)) {
        supprimeAnnee($tab_checkbox_j_chome);
    }

    // on insère les nouvelles dates saisies
    if (insereAnnee($tab_checkbox_j_chome)) {
        $date_1 = key($tab_checkbox_j_chome);
        $tab_date = explode('-', $date_1);
        $comment_log = "saisie des jours chomés pour " . $tab_date[0] ;
        log_action(0, "", "", $comment_log);
        init_tab_jours_feries();
        return true;
    }

    return false;
}

function isAnneeSaisie(array $tab_checkbox_j_chome) : bool
{
    $db = \includes\SQL::singleton();
    $date_1 = key($tab_checkbox_j_chome);
    $year = (int) substr($date_1, 0, 4);

    return !empty(getJourFeriesListe($year));
}

function supprimeAnnee(array $tab_checkbox_j_chome) : bool
{
    $db = \includes\SQL::singleton();
    $date_1=key($tab_checkbox_j_chome);
    $year=substr($date_1, 0, 4);
    $sql_delete='DELETE FROM conges_jours_feries WHERE jf_date LIKE "'. $db->quote($year).'%" ;';
    $result = $db->query($sql_delete);

    return true;
}

function insereAnnee(array $tab_checkbox_j_chome) : bool
{
    $db = \includes\SQL::singleton();
    foreach($tab_checkbox_j_chome as $key => $value) {
        $result = $db->query('INSERT INTO conges_jours_feries SET jf_date="'. $db->quote($key).'";');
    }
    return true;
}

function getJourFeriesListe(int $year) : array
{
    $sql = \includes\SQL::singleton();
    $config = new \App\Libraries\Configuration($sql);
    $injectableCreator = new \App\Libraries\InjectableCreator($sql, $config);
    $api = $injectableCreator->get(\App\Libraries\ApiClient::class);
    $feries = $api->get('jour_ferie', $_SESSION['token'])['data'];

    $feriesAnnee = array_filter($feries, function ($ferie) use ($year) {
        return false !== stripos($ferie['date'], (string) $year);
    });

    return array_map(function ($ferie) {
        return $ferie['date'];
    }, $feriesAnnee);
}

function getJoursFeriesFrance(int $iAnnee) : array
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

function afficheJourHorsMois(int $mois, $i, int $year) : string
{
    $j_timestamp = mktime(0, 0, 0, $mois, $i, $year);
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

// verif des droits du user à afficher la page
verif_droits_user( "is_hr");

$PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
// GET / POST
$PHP_SELF = parse_url($PHP_SELF, PHP_URL_PATH);
$choix_action = getpost_variable('choix_action');
$annee = (int) getpost_variable('year_calendrier_saisie', date("Y"));
$checkbox = getpost_variable('tab_checkbox_j_chome');
$tab_checkbox_j_chome = (!is_array($checkbox) || empty($checkbox)) ? [] : $checkbox;
$commitSuccess = ($choix_action == "commit" && !empty($tab_checkbox_j_chome))
    ? commitSaisie($tab_checkbox_j_chome)
    : null;
/*************************************/

$joursFeries = getJourFeriesListe($annee);

$sql = \includes\SQL::singleton();
$config = new \App\Libraries\Configuration($sql);
if ($config->isJoursFeriesFrance()) {
    $tableau_jour_feries = getJoursFeriesFrance($annee) ;
    foreach ($tableau_jour_feries as $value) {
        if (in_array($value, $joursFeries)) {
            continue;
        }
        $joursFeries[] = $value;
    }
}

$title = _('admin_button_jours_chomes_1');
$prev_link = "$PHP_SELF?year_calendrier_saisie=". ($annee - 1);
$next_link = "$PHP_SELF?year_calendrier_saisie=". ($annee + 1);
$listeMois = [[1, 2, 3, 4], [5, 6, 7, 8], [9, 10, 11, 12]];

require_once VIEW_PATH . 'JourFerie/Liste.php';
