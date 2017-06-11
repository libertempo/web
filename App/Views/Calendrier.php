<?php
/*
 * Variables disponibles :
 * $calendar
 * $session
 * $calendrier
 * $evenements
 */
$mois = $calendar->getMonth(new \DateTime($moisDemande->format('Y-m-d')));
$jours = [];
$moisPrecedent = getUrlMois($moisDemande->modify('-1 month'), $session, $idGroupe);
$moisCourant = getUrlMois(new \DateTimeImmutable(), $session, $idGroupe);
$moisSuivant = getUrlMois($moisDemande->modify('+1 month'), $session, $idGroupe);
?>

<?php /* Div auto fermé par le bottom */ ?>
<div id="calendar-wrapper"><h1><?= _('calendrier_titre') ?></h1>

<form method="get" action="" class="form-inline search" role="form">
    <div class="form-group col-md-4 col-sm-5">
        <label class="control-label col-md-3 col-sm-3" for="groupe">Groupe&nbsp;:</label>
        <div class="col-md-8 col-sm-8">
            <select class="form-control" name="groupe" id="groupe">
                <option value="<?= NIL_INT ?>">Tous</option>
                <?php
                    foreach (\App\ProtoControllers\Groupe::getOptions() as $id => $groupe) {
                        $selected = ($id === $idGroupe)
                        ? 'selected="selected"'
                        : '';
                        echo '<option value="' . $id . '" ' . $selected . '>' . $groupe['nom'] . '</option>';
                    }
                    ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <div class="input-group pull-right"><button type="submit" class="btn btn-default"><i class="fa fa-search" aria-hidden="true"></i></button>
        </div>
    </div>
    <input type="hidden" name="session" value="<?= $session ?>" />
</form>

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
            <th class="<?= $today ?>">
                <a href=?session=<?= $session ?>&jour=<?= $jourString ?>><?= $day->getBegin()->format('d') ?></a>
            </th>
            <?php endforeach ?>
            <?php endforeach ?>
        </tr>
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
    </table>
</div>
