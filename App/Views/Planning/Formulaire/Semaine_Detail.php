<?php
/*
 * $idSemaine
 * $titreSemaine
 * $optionsSemaine
 * $jours
 * $creneauxGroupes
 */

?>
<div id="<?= $idSemaine ?>"><h4><?= $titreSemaine ?></h4>
    <table class="table table-hover table-responsive table-condensed table-striped" id="<?= $optionsSemaine['tableId'] ?>">
        <thead>
            <tr><th width="20%"><?= _('Jour') ?></th><th><?= _('Creneaux_travail') ?></th><th id="<?= $optionsSemaine['dureeHebdoId'] ?>"></th><tr>
        </thead>
        <tbody>
        <?php foreach ($jours as $idJour => $jour) : ?>
            <tr data-id-jour=<?= $idJour ?>><td name="nom"><?= $jour ?></td><td class="creneaux"></td><td></td></tr>
        <?php endforeach ;?>
        </tbody>
        <script type="text/javascript">
        new planningController("<?= $optionsSemaine['ajoutBoutonId'] ?>", <?= json_encode($optionsSemaine) ?>, <?= json_encode($creneauxGroupes) ?>).readOnly();
        </script>
    </table>
</div>
