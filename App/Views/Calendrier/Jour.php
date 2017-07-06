<?php
/*
 * Variables disponibles :
 * $calendar
 * $evenements
 * $idGroupe
 * $jourDemande
 */
$urlMoisDemande = getUrlMois($jourDemande, $idGroupe);
$jour = $calendar->getDay(new \DateTime($jourDemande->format('Y-m-d')));

require_once VIEW_PATH . 'Calendrier.php';
?>
<div class="btn-group pull-right">
    <a class="btn btn-default" href="<?= $urlMoisDemande ?>"><i class="fa fa-chevron-up" aria-hidden="true"></i>&nbsp;Retour au mois</a>
</div>

<h2><?= strftime('%d %B %G', $jour->getBegin()->getTimestamp()) ?></h2>
<div id="calendrierJour" class="calendrier">
    <table id="calendrier">
        <tr id="entete"><th></th>
            <?php foreach ($jour as $hour) : ?>
            <?php
            $heureString = $hour->getBegin()->format('H');
            $heures[] = $heureString;
            ?>
            <th><?= $heureString ?></th>
            <?php endforeach ?>
        </tr>
        <?php foreach ($utilisateursATrouver as $nom) : ?>
        <tr class="calendrier-employe">
            <td class="calendrier-nom"><?= $nom ?></td>
        </tr>
        <?php endforeach ?>
    </table>
</div>

<?php
/*


<?php foreach ($evenements->getEmployes() as $nom) : ?>
<tr class="calendrier-employe">
<td class="calendrier-nom"><?= $nom ?></td>
<?php foreach ($jours as $jour) : ?>
<td class="calendrier-jour <?= getClassesJour($evenements, $nom, $jour, $moisDemande) ?>" title=" | ">
<div class="triangle-top"></div>
<div class="triangle-bottom"></div>
</td>
<?php endforeach ?>
</tr>
<?php endforeach ?>

*/

 ?>
