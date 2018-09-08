<?php
require_once INCLUDE_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );


if (file_exists(CONFIG_PATH .'config_ldap.php')) {
    include_once CONFIG_PATH .'config_ldap.php';
}
if(!isset($_SESSION['config'])) {
    $_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
}
include_once INCLUDE_PATH . 'session.php';

// verif des droits du user à afficher la page
verif_droits_user( "is_admin");



/*** initialisation des variables ***/
/*************************************/
// recup des parametres reçus :
// SERVER
$PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
// GET / POST
$action         = getpost_variable('action') ;
$tab_new_values = getpost_variable('tab_new_values');
$id_to_update   = htmlentities(getpost_variable('id_to_update'), ENT_QUOTES | ENT_HTML401);

/*********************************/

switch ($action) {
    case 'new':
        echo \config\Fonctions::commit_ajout($tab_new_values);
        break;
    case 'modif':
        echo \config\Fonctions::modifier($tab_new_values, $id_to_update);
        break;
    case 'commit_modif':
        echo \config\Fonctions::commit_modif_absence($tab_new_values, $id_to_update);
        break;
    case 'suppr':
        echo \config\Fonctions::supprimer($id_to_update);
        break;
    case 'commit_suppr':
        echo \config\Fonctions::commit_suppr($id_to_update);
        break;
    default:
        $sql = \includes\SQL::singleton();
        $config = new \App\Libraries\Configuration($sql);
        $injectableCreator = new \App\Libraries\InjectableCreator($sql, $config);
        $api = $injectableCreator->get(\App\Libraries\ApiClient::class);
        $absenceTypes = $api->get('absence/type', $_SESSION['token'])['data'];
        foreach ($absenceTypes as $type) {
            // @TODO 2018-09-08 : avance de phase pour l'API. À enlever quand l'API sera consommée
            if (!isset($type['typeNatif'])) {
                $type += ['typeNatif' => false];
            }
            $listeTypeConges[$type['type']][] = $type;
        }
        if (!$config->isCongesExceptionnelsActive() && isset($listeTypeConges['conges_exceptionnels'])) {
            unset($listeTypeConges['conges_exceptionnels']);
        }
        $url = $PHP_SELF;

        $nouveauLibelle = isset($tab_new_values['libelle'])
            ? $tab_new_values['libelle']
            : '';
        $nouveauLibelleCourt = isset($tab_new_values['short_libelle'])
            ? $tab_new_values['short_libelle']
            : '';
        $nouveauType = isset($tab_new_values['type'])
            ? $tab_new_values['type']
            : '';
        require_once VIEW_PATH .  'Configuration/Type_Absence/Liste.php';
        break;
}

bottom();
