<?php
defined('_PHP_CONGES') or die('Restricted access');

$message   = '';
if (!empty($_POST)) {
    $notice    = '';
    $errorsLst = [];
    if (0 >= (int) \App\ProtoControllers\HautResponsable\Planning::postPlanning($_POST, $errorsLst, $notice)) {
        $errors = '';
        if (!empty($errorsLst)) {
            foreach ($errorsLst as $value) {
                $errors .= '<li>' . $value . '</li>';
            }
            $message = '<div class="alert alert-danger">' . _('erreur_recommencer') . ' :<ul>' . $errors . '</ul></div>';
        }
    } elseif ('DELETE' === $_POST['_METHOD'] && !empty($notice)) {
        log_action(0, '', '', 'Suppression du planning ' . $_POST['planning_id']);
        $message = '<form action="" method="post" accept-charset="UTF-8"
enctype="application/x-www-form-urlencoded"><input type="hidden" name="planning_id" value="' . $_POST['planning_id'] . '" /><input type="hidden" name="status" value="' . \App\Models\Planning::STATUS_ACTIVE . '" /><input type="hidden" name="_METHOD" value="PATCH" /><div class="alert alert-info">' .  $notice . '. <button type="submit" class="btn btn-link alert-link">' . _('Annuler') . '</button></div></form>';
    } else {
        log_action(0, '', '', 'Récupération du planning ' . $_POST['planning_id']);
        redirect(ROOT_PATH . 'hr/liste_planning', false);
    }
}

$titre = _('hr_affichage_liste_planning_titre');
$lienModif = 'modif_planning';
$baseURIApi = $config->getUrlAccueil() . '/api/';
$isHr = true;
$listPlanningId = \App\ProtoControllers\HautResponsable\Planning::getListPlanningId();
$listIdUsed = \App\ProtoControllers\HautResponsable\Planning::getListPlanningUsed($listPlanningId);

require_once VIEW_PATH . 'Planning/Liste.php';
