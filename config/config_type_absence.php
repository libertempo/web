<?php
require_once INCLUDE_PATH . 'define.php';
defined('_PHP_CONGES') or die('Restricted access');

if (file_exists(CONFIG_PATH .'config_ldap.php')) {
    include_once CONFIG_PATH .'config_ldap.php';
}
if(!isset($_SESSION['config'])) {
    $_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
}
require_once INCLUDE_PATH . 'session.php';

// verif des droits du user à afficher la page
verif_droits_user("is_admin");

$PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
$action         = getpost_variable('action');
$tab_new_values = getpost_variable('tab_new_values');
$id_to_update   = htmlentities(getpost_variable('id_to_update'), ENT_QUOTES | ENT_HTML401);

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
        $isCongesExceptionnelsActive = $config->isCongesExceptionnelsActive();
        $titres = [
            'conges' => _('divers_conges_maj_1'),
            'absences' => _('divers_absences_maj_1'),
            'conges_exceptionnels' => _('divers_conges_exceptionnels_maj_1'),
        ];
        $classesConges = array_keys($titres);
        $comments = [
            'conges' => _('config_abs_comment_conges'),
            'absences' => _('config_abs_comment_absences'),
            'conges_exceptionnels' => _('config_abs_comment_conges_exceptionnels'),
        ];
        $traductions = [
            'titres' => $titres,
            'commentaires' => $comments,
        ];
        $offsetCongesExceptionnels = array_search('conges_exceptionnels', $classesConges);
        if (!$isCongesExceptionnelsActive && is_int($offsetCongesExceptionnels)) {
            unset($classesConges[$offsetCongesExceptionnels]);
        }
        $url = $PHP_SELF;

        $nouveauLibelle = $tab_new_values['libelle'] ?? '';
        $nouveauLibelleCourt = $tab_new_values['short_libelle'] ?? '';
        $nouveauType = $tab_new_values['type'] ?? '';
        require_once VIEW_PATH .  'Configuration/Type_Absence/Liste.php';
        break;
}
