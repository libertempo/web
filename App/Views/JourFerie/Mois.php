<?php
/*
 * $year_calendrier_saisie
 * $mois
 * $joursFeries
 */

$premierJour = mktime(0, 0, 0, $mois, 1, $year_calendrier_saisie);
$premierJourMois = date("w", $premierJour);
$nomMois = date_fr("F", $premierJour);

// jour de la semaine en chiffre (0=dim, 6=sam)
if (0 == $premierJourMois) {
    $premierJourMois = 7;
}
?>
<div class="month">
    <div class="wrapper">
        <table>
            <thead>
                <tr align="center"><th colspan=7 class="titre"> <?= $nomMois ?></th></tr>
                <tr>
                    <th class="cal-saisie2"><?= _('lundi_1c') ?></th>
                    <th class="cal-saisie2"><?= _('mardi_1c') ?></th>
                    <th class="cal-saisie2"><?= _('mercredi_1c') ?></th>
                    <th class="cal-saisie2"><?= _('jeudi_1c') ?></th>
                    <th class="cal-saisie2"><?= _('vendredi_1c') ?></th>
                    <th class="cal-saisie2 weekend"><?= _('samedi_1c') ?></th>
                    <th class="cal-saisie2 weekend"><?= _('dimanche_1c') ?></th>
                </tr>
            </thead>
            <tr>
            <?php // 1° ligne
            for ($jour = 1; $jour < $premierJourMois; ++$jour) : ?>
                <?= afficheJourHorsMois($mois, $jour, $year_calendrier_saisie, $joursFeries); ?>
            <?php endfor; ?>
            <?php for ($jour = $premierJourMois; $jour < 8; ++$jour) : ?>
                <?php $resteJourFinSemaine = $jour - $premierJourMois + 1; ?>
                <?= afficheJourMois($mois, $resteJourFinSemaine, $year_calendrier_saisie, $joursFeries); ?>
            <?php endfor; ?>
            </tr><tr>
            <?php // 2° ligne
            for ($jour = 8 - $premierJourMois + 1; $jour < 15 - $premierJourMois + 1; ++$jour) : ?>
                <?= afficheJourMois($mois, $jour, $year_calendrier_saisie, $joursFeries); ?>
                <?php endfor ; ?>
            </tr><tr>
            <?php // 3° ligne
            for ($jour = 15 - $premierJourMois + 1; $jour < 22 - $premierJourMois + 1; ++$jour) : ?>
                <?= afficheJourMois($mois, $jour, $year_calendrier_saisie, $joursFeries); ?>
            <?php endfor ; ?>
            </tr><tr>
            <?php // 4° ligne
            for ($jour = 22 - $premierJourMois + 1; $jour < 29 - $premierJourMois + 1; ++$jour) : ?>
                <?= afficheJourMois($mois, $jour, $year_calendrier_saisie, $joursFeries); ?>
            <?php endfor; ?>
            </tr><tr>

            <?php // 5° ligne
            for ($jour = 29 - $premierJourMois + 1; $jour < 36 - $premierJourMois + 1 && checkdate($mois, $jour, $year_calendrier_saisie); ++$jour) : ?>
                <?= afficheJourMois($mois, $jour, $year_calendrier_saisie, $joursFeries); ?>
            <?php endfor; for ($jour; $jour < 36 - $premierJourMois + 1; ++$jour) : ?>
                <?= afficheJourHorsMois($mois, $jour, $year_calendrier_saisie, $joursFeries); ?>
            <?php endfor ?>
            </tr><tr>
            <?php // 6° ligne
            for ($jour = 36 - $premierJourMois + 1; checkdate($mois, $jour, $year_calendrier_saisie); ++$jour) : ?>
                <?= afficheJourMois($mois, $jour, $year_calendrier_saisie, $joursFeries); ?>
            <?php endfor; for($jour; $jour < 43 - $premierJourMois + 1; ++$jour) : ?>
                <?= afficheJourHorsMois($mois, $jour, $year_calendrier_saisie, $joursFeries); ?>
            <?php endfor; ?>
            </tr>
        </table>
    </div>
</div>
