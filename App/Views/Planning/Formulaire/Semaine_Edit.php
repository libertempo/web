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
            <tr>
                <td>
                    <select class="form-control" id="<?= $optionsSemaine['selectJourId'] ?>">
                        <option value="<?= NIL_INT ?>"></option>
                    <?php foreach ($jours as $idJour => $jour) : ?>
                        <option value="<?= $idJour ?>"><?= $jour ?></option>
                    <?php endforeach ; ?>
                    </select>
                </td><td>
                    <div class="form-inline col-xs-3">
                        <input type="text" id="<?= $optionsSemaine['debutId'] ?>" class="form-control" style="width:45%" />&nbsp;<i class="fa fa-caret-right"></i>&nbsp;
                        <input type="text" id="<?= $optionsSemaine['finId'] ?>" class="form-control" style="width:45%" size="8" />
                    </div>
                    &nbsp;&nbsp;
                    <div class="form-inline col-xs-4">
                        <label class="radio-inline">
                            <input type="radio" name="periode" value="<?= \App\Models\Planning\Creneau::TYPE_PERIODE_MATIN ?>"><?= _('form_am') ?>
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="periode" value="<?= \App\Models\Planning\Creneau::TYPE_PERIODE_APRES_MIDI ?>"><?= _('form_pm') ?>
                        </label>
                        &nbsp;&nbsp; <button type="button" class="btn btn-default btn-sm" id="<?= $optionsSemaine['ajoutBoutonId'] ?>"><i class="fa fa-plus link" ></i></button>
                    </div>
                    <span class="text-danger" id="<?= $optionsSemaine['helperId'] ?>"></span>
                </td><td></td>
            </tr>
            <script type="text/javascript">
            generateTimePicker("<?= $optionsSemaine['debutId'] ?>");
            generateTimePicker("<?= $optionsSemaine['finId'] ?>");
            </script>
            <?php foreach ($jours as $idJour => $jour) : ?>
                <tr data-id-jour=<?= $idJour ?>><td name="nom"><?= $jour ?></td><td class="creneaux"></td><td></td></tr>
        <?php endforeach ; ?>
        </tbody>
        <script type="text/javascript">
        new planningController("<?= $optionsSemaine['ajoutBoutonId'] ?>", <?= json_encode($optionsSemaine) ?>, <?= json_encode($creneauxGroupes) ?>).init();
        </script>
    </table>
</div>
