<?php declare(strict_types = 1);

defined( '_PHP_CONGES' ) or die( 'Restricted access' );
$gestionGroupes = new \App\ProtoControllers\Groupe\Gestion();
$idGroupe = (int) getpost_variable('group');

if (0 >= $idGroupe) {
    redirect(ROOT_PATH . 'deconnexion');
}

$db = \includes\SQL::singleton();
$config = new \App\Libraries\Configuration($db);
$baseURIApi = $config->getUrlAccueil() . '/api/';
$doubleValidationActive = $config->isDoubleValidationActive();

$message = '';
$infosGroupe = \App\ProtoControllers\Groupe::getInfosGroupe($idGroupe, $db);
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
            redirect(ROOT_PATH . 'hr/liste_groupe?notice=update');
        } else {
            redirect(ROOT_PATH . 'hr/liste_groupe?notice=insert');
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

$responsablesGroupe = [];
$titre = _('admin_modif_groupe_titre');

require_once VIEW_PATH . 'Groupe/Edition.php';
