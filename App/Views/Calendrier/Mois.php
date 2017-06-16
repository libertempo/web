<?php
/*
 * Variables disponibles :
 * $calendar
 * $session
 * $evenements
 * $idGroupe
 * $moisDemande
 * $employesATrouver
 */
$mois = $calendar->getMonth(new \DateTime($moisDemande->format('Y-m-d')));
$jours = [];
$moisPrecedent = getUrlMois($moisDemande->modify('-1 month'), $session, $idGroupe);
$moisCourant = getUrlMois(new \DateTimeImmutable(), $session, $idGroupe);
$moisSuivant = getUrlMois($moisDemande->modify('+1 month'), $session, $idGroupe);

require_once VIEW_PATH . 'Calendrier.php';
?>

<div class="btn-group pull-right">
    <a class="btn btn-default" href="<?= $moisPrecedent ?>"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>
    <a class="btn btn-default" title="<?= _('retour_periode_courante') ?>" href="<?= $moisCourant ?>"><i class="fa fa-home" aria-hidden="true"></i></a>
    <a class="btn btn-default" href="<?= $moisSuivant ?>"><i class="fa fa-chevron-right" aria-hidden="true"></i></a>
</div>

<h2><?= strftime('%B %G', $mois->getBegin()->getTimestamp()) ?></h2>
<div id="calendrierMois" class="calendrier">
    <table id="calendrier">
        <tr id="entete"><th></th>
            <?php foreach ($mois as $week) : ?>
            <?php foreach ($week as $day) : ?>
            <?php
            $today = ($day->isCurrent()) ? 'today' : '';
            $jourString = $day->getBegin()->format('Y-m-d');
            $jours[] = $jourString;
            ?>
            <th class="<?= $today ?>"><?= $day->getBegin()->format('d') ?></th>
            <?php endforeach ?>
            <?php endforeach ?>
        </tr>
        <?php foreach ($employesATrouver as $loginUtilisation => $nomComplet) : ?>
        <tr class="calendrier-employe">
            <td class="calendrier-nom"><?= $nomComplet ?></td>
            <?php foreach ($jours as $jour) : ?>
            <td class="calendrier-jour <?= getClassesJour($evenements, $loginUtilisation, $jour, $moisDemande) ?>">
                <div class="triangle-top"></div>
                <div class="triangle-bottom"></div>
                <?php $title = getTitleJour($evenements, $loginUtilisation, $jour);
                if (!empty($title)) {
                    echo '<div class="title">' . $title . '</div>';
                }?>
            </td>
            <?php endforeach ?>
        </tr>
        <?php endforeach ?>
    </table>
</div>
