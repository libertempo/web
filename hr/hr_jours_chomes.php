<?php declare(strict_types = 1);

defined('_PHP_CONGES') or die('Restricted access');

if (file_exists(CONFIG_PATH .'config_ldap.php')) {
    include CONFIG_PATH .'config_ldap.php';
}

function commitSaisie(array $tab_checkbox_j_chome) : bool
{
    // si l'année est déja renseignée dans la database, on efface ttes les dates de l'année
    if (isAnneeSaisie($tab_checkbox_j_chome)) {
        $year = explode('-', key($tab_checkbox_j_chome))[0];
        \hr\Fonctions::supprimeFeriesAnnee(intval($year));
    }

    // on insère les nouvelles dates saisies
    if (\hr\Fonctions::insereFeriesAnnee(array_keys($tab_checkbox_j_chome))) {
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
    $date_1 = key($tab_checkbox_j_chome);
    $year = (int) substr($date_1, 0, 4);

    return !empty(getJourFeriesListe($year));
}

function getJourFeriesListe(int $year) : array
{
    $sql = \includes\SQL::singleton();
    $config = new \App\Libraries\Configuration($sql);
    $injectableCreator = new \App\Libraries\InjectableCreator($sql, $config);
    $api = $injectableCreator->get(\App\Libraries\ApiClient::class);
    $feries = $api->get('jour_ferie', $_SESSION['token'])['data'];

    $feriesAnnee = array_filter(
        $feries, function ($ferie) use ($year) {
            return false !== stripos($ferie['date'], (string) $year);
        }
    );

    return array_map(
        function ($ferie) {
            return $ferie['date'];
        }, $feriesAnnee
    );
}

function afficheJourHorsMois(int $mois, $jour, int $year) : string
{
    $j_timestamp = mktime(0, 0, 0, $mois, $jour, $year);
    $td_second_class = get_td_class_of_the_day_in_the_week($j_timestamp);
    return '<td class="cal-saisie2 month-out ' . $td_second_class . '">&nbsp;</td>';
}

function afficheJourMois($mois, $i, $year, $tab_year) : string
{
    $j_timestamp = mktime(0, 0, 0, $mois, $i, $year);
    $j_date = date("Y-m-d", $j_timestamp);
    $j_day = date("d", $j_timestamp);
    $td_second_class = get_td_class_of_the_day_in_the_week($j_timestamp);
    $checked = in_array("$j_date", $tab_year);

    return '<td class="cal-saisie ' . $td_second_class . ' ' . (($checked) ? ' fermeture' : '') . '">' . $j_day . '<input type="checkbox" name="tab_checkbox_j_chome[' . $j_date . ']" value="Y" ' . (($checked) ? ' checked' : '') . '></td>';
}

// verif des droits du user à afficher la page
verif_droits_user("is_hr");

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

$title = _('admin_button_jours_chomes_1');
$prev_link = "$PHP_SELF?year_calendrier_saisie=". ($annee - 1);
$next_link = "$PHP_SELF?year_calendrier_saisie=". ($annee + 1);
$listeMois = [[1, 2, 3, 4], [5, 6, 7, 8], [9, 10, 11, 12]];

require_once VIEW_PATH . 'JourFerie/Liste.php';
