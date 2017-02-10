<?php
defined('_PHP_CONGES') or die('Restricted access');

$conge               = new \App\ProtoControllers\Conge();
$listeAbsentFromDate = $conge->getListeAbsentDateSousResponsable();

print("<pre>");
print_r($listeAbsentFromDate);
print("</pre>");
echo '<h1>' . _('user_liste_absents_jour_titre') . '</h1>';
if (sizeof($listeAbsentFromDate) == 0) {
	echo _('pas_d_absents');
} else {
    $table = new \App\Libraries\Structure\Table();
    $table->addClasses([
        'table',
        'table-hover',
        'table-responsive',
        'table-condensed',
        'table-striped',
    ]);        
    $childTable = '<thead><tr>';
    $childTable .= '<th>' . _('divers_nom_maj_1') . '</th><th>' . _('divers_prenom_maj_1') . '</th><th>' . _('divers_fin_maj_1') . '</th></th>';
    $childTable .= '</tr>';
    $childTable .= '</thead><tbody>';
    $childTable .= '</tbody>';
    foreach ($listeAbsentFromDate as $absentFromDate) {
        $childTable .= '<tr>';
        foreach ($absentFromDate as $key => $value) {
            $childTable .= '<td>' . $value . '</td>';
        }
        $childTable .= '<tr>';
    }
    $table->addChild($childTable);
    $table->render();
}
