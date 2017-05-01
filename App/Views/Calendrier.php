<?php
$noms = ['Abe', 'Lafayette', 'Georges', 'Stan', 'Barry'];
$debut = new \DateTime(date('Y-m-01'));
$month = $calendar->getMonth($debut);
$jours = [];
?>

<table id="calendrier">
    <tr id="entete">
        <th></th>
        <?php foreach ($month as $week) : ?>
            <?php foreach ($week as $day) : ?>
                <?php
                $today = ($day->isCurrent()) ? 'today' : '';
                $jours[] = $day->getBegin()->format('Y-m-d');
                ?>
                <th class="<?= $today ?>"><?= $day->getBegin()->format('j') ?></th>
            <?php endforeach ?>
        <?php endforeach ?>
    </tr>
    <?php foreach ($noms as $nom) : ?>
        <tr class="calendrier-employe">
            <td class="calendrier-nom"><?= $nom ?></td>
        <?php foreach ($jours as $jour) : ?>
            <td class="calendrier-jour" title="<?= $jour ?>"></td>
        <?php endforeach ?>
        </tr>
    <?php endforeach ?>
</table>
