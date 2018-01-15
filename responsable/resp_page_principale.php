<?php
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$typeConges = $tab_type_cong;
$config = new \App\Libraries\Configuration(\includes\SQL::singleton());
$congesExceptionnels = ($config->isCongesExceptionnelsActive())
    ? $tab_type_conges_exceptionnels
    : [];

$gestionHeure = $config->isHeuresAutorise();
$gestionEditionPapier = $config->canEditPapier();
$subalternesResponsable = recup_infos_all_users_du_resp($_SESSION['userlogin']);
$subalternesActifsResponsable = array_filter($subalternesResponsable, function ($employe) {
    return 'Y' == $employe['is_active'];
});
$nombreColonnes = 3 + 2 * count($typeConges) + count($congesExceptionnels) + (int) $gestionHeure + 1 + (int) $gestionEditionPapier;

$subalternesGrandResponsable = ($config->isDoubleValidationActive())
    ? recup_infos_all_users_du_grand_resp($_SESSION['userlogin'])
    : [];
$subalternesActifsGrandResponsable = array_filter($subalternesGrandResponsable, function ($employe) {
    return 'Y' == $employe['is_active'];
});
$subalternesActifsGrandResponsableNonDirect = [];
foreach ($subalternesActifsGrandResponsable as $k => $v) {
    if (!isset($subalternesActifsResponsable[$k])) {
        $subalternesActifsGrandResponsableNonDirect[$k] = $v;
    }
}

require_once VIEW_PATH . 'Responsable/Employe/Liste.php';
