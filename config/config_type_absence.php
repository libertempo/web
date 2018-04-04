<?php

include_once ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );


if (file_exists(CONFIG_PATH .'config_ldap.php')) {
    include_once CONFIG_PATH .'config_ldap.php';
}

// include_once ROOT_PATH .'fonctions_conges.php' ;
// include_once INCLUDE_PATH .'fonction.php';
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
$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
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
        $db = \includes\SQL::singleton();
        $config = new \App\Libraries\Configuration($db);
        $url = "$PHP_SELF?onglet=type_absence";
        $tab_enum = \config\Fonctions::get_tab_from_mysql_enum_field("conges_type_absence", "ta_type");
        $listeTypeConges = [];
        $enumTypeConges = [];
        foreach ($tab_enum as $typeConge) {
            if($typeConge != "conges_exceptionnels"
                || $config->isCongesExceptionnelsActive()
            ) {
                $sql1 = 'SELECT * FROM conges_type_absence WHERE ta_type = "' . $db->quote($typeConge) . '"';
                $ReqLog1 = $db->query($sql1);
                $listeTypeConges[$typeConge] = $ReqLog1->fetch_all();
                $enumTypeConges[] = $typeConge;
            }
        }
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
