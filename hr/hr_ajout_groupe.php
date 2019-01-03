<?php declare(strict_types = 1);
defined('_PHP_CONGES') or die('Restricted access');
$gestionGroupes = new \App\ProtoControllers\Groupe\Gestion();
$idGroupe = NIL_INT;

/**
 * retourne les utilisateurs responsables
 *
 * @return array
 */
function getInfosResponsables(array $employes)
{
    return array_filter($employes, function (array $e) {
        return $e['is_haut_responsable'];
    });
}

$sql = \includes\SQL::singleton();
$config = new \App\Libraries\Configuration($sql);
$doubleValidationActive = $config->isDoubleValidationActive();

$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
$message = '';
$infosGroupe = [
    'nom' => '',
    'doubleValidation' => '',
    'comment' => '',
];
$data = [];

$errorsLst = [];
if (!empty($_POST)) {
    if (0 >= (int) $gestionGroupes->postHtmlCommon($_POST, $errorsLst)) {
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
        $data = $gestionGroupes->FormData2Array($_POST);
    } else {
        if (key_exists('_METHOD', $_POST)) {
            redirect(ROOT_PATH . 'hr/hr_index.php?onglet=liste_groupe&notice=update');
        } else {
            redirect(ROOT_PATH . 'hr/hr_index.php?onglet=liste_groupe&notice=insert');
        }
    }
}

if (!empty($data)) {
    $infosGroupe = [
        'nom' => $data['nom'],
        'comment' => $data['commentaire']
    ];
    if ($doubleValidationActive) {
        $infosGroupe['doubleValidation'] = $data['isDoubleValidation'];
    }
}

$selectId = uniqid();
$divGrandRespId = uniqid();

$baseURIApi = $config->getUrlAccueil() . '/api/';
$injectableCreator = new \App\Libraries\InjectableCreator($sql, $config);
$api = $injectableCreator->get(\App\Libraries\ApiClient::class);
$employes = $api->get('utilisateur', $_SESSION['token'])['data'];
$employes = array_map(function (array $e) {
    $e += ['isDansGroupe' => false];
    return $e;
}, $employes);

$responsables = getInfosResponsables($employes);
$responsablesGroupe = [];
$titre = '<h1>' . _('admin_groupes_new_groupe') . '</h1>';

require_once VIEW_PATH . 'Groupe/Edition.php';
