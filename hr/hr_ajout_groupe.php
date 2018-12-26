<?php declare(strict_types = 1);
defined('_PHP_CONGES') or die('Restricted access');
$gestionGroupes = new \App\ProtoControllers\Groupe\Gestion();
$idGroupe = NIL_INT;

/**
 * retourne les utilisateurs
 *
 * @return array
 */
function getEmployes(\App\Libraries\ApiClient $api, string $token)
{
    $employes = $api->get('utilisateur', $token)['data'];
    $employes = array_map(function (array $e) {
        $e += ['prenom' => 'A recup', 'isDansGroupe' => false];
        return $e;
    }, $employes);

    return $employes;
}

/**
 * retourne les utilisateurs responsables
 *
 * @return array
 */
function getInfosResponsables()
{
    $responsables = [];

    $infosResps = \App\ProtoControllers\Responsable::getInfosResponsables(\includes\SQL::singleton(), true);
    foreach ($infosResps as $infos) {
        $login = $infos['u_login'];
        $responsables[$login] = [
            'nom' => $infos['u_nom'],
            'prenom' => $infos['u_prenom'],
            'login' => $login,
            'isDansGroupe' => false,
        ];
    }
    return $responsables;
}

/**
 *
 * retourne les utilisateurs grands responsables
 * @return array
 */
function getGrandResponsables()
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
    }
    return $responsables;
}

$sql = \includes\SQL::singleton();
$config = new \App\Libraries\Configuration($sql);
$doubleValidationActive = $config->isDoubleValidationActive();

$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
$message = '';
$infosGroupe = [
    'nom' => '',
    'doubleValidation' => false,
    'comment' => '',
];
$data = NULL;

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

if (isset($data)) {
    $infosGroupe = [
        'nom' => $data['nom'],
        'comment' => $data['commentaire']
    ];
    if ($doubleValidationActive) {
        $infosGroupe['doubleValidation'] = $data['isDoubleValidation'];
    }
}

$selectId = uniqid();
$DivGrandRespId = uniqid();

$injectableCreator = new \App\Libraries\InjectableCreator($sql, $config);
$api = $injectableCreator->get(\App\Libraries\ApiClient::class);
$employes = getEmployes($api, $_SESSION['token']);
$responsables = getInfosResponsables();
$grandResponsables = getGrandResponsables();
$titre = '<h1>' . _('admin_groupes_new_groupe') . '</h1>';

require_once VIEW_PATH . 'Groupe/Edition.php';
