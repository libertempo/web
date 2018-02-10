<?php

/*
 * $message
 * $planning
 * $idToggleSemaine
 * $idSemaineCommune
 * $idSemaineImpaire
 * $creneauxGroupesCommuns
 * $creneauxGroupesImpairs
 * $creneauxGroupesPairs
 * $jours
 * $optionsCommuns
 * $optionsImpaires
 * $optionsPaires
 * $typesSemaines
 * $text
 * $utilisateursAssocies
 * $optionsGroupes
 * $associationsGroupe
 */
?>
<h1><?= _('resp_modif_planning_titre') ?></h1>
<?= $message ?>
<form action="" method="post" accept-charset="UTF-8"
enctype="application/x-www-form-urlencoded" class="form-group">
    <input type="hidden" name="_METHOD" value="PUT" />
    <table class="table table-hover table-responsive table-condensed table-striped">
        <thead>
            <tr><th class="col-md-4"><?= _('Nom') ?></th><th></th></tr>
        </thead>
        <tbody>
            <tr>
                <td><?= $planning->name ?><input type="hidden" name="planning_id" value="<?= $planning->id ?>" /></td>
                <td><input type="button" id="<?= $idToggleSemaine ?>" class="btn btn-default " /></td>
            </tr>
        </tbody>
    </table>
    <h3><?= _('Creneaux') ?></h3>
    <div id="<?= $idSemaineCommune ?>"><h4><?= _('resp_temps_partiel_sem') ?></h4>
        <table class="table table-hover table-responsive table-condensed table-striped" id="<?= $optionsCommuns['tableId'] ?>">
            <thead>
                <tr><th width="20%"><?= _('Jour') ?></th><th><?= _('Creneaux_travail') ?></th><tr>
            </thead>
            <tbody>
            <?php foreach ($jours as $idJour => $jour) : ?>
                <tr data-id-jour=<?= $idJour ?>><td name="nom"><?= $jour ?></td><td class="creneaux"></td></tr>
            <?php endforeach ;?>
            </tbody>
            <script type="text/javascript">
            new planningController("<?= $linkId ?>", <?= json_encode($optionsCommuns) ?>, <?= json_encode($creneauxGroupesCommuns) ?>).readOnly();
            </script>
        </table>
    </div>
    <div id="<?= $idSemaineImpaire ?>"><h4><?= _('resp_temps_partiel_sem_impaires') ?></h4>
        <table class="table table-hover table-responsive table-condensed table-striped" id="<?= $optionsImpaires['tableId'] ?>">
            <thead>
                <tr><th width="20%"><?= _('Jour') ?></th><th><?= _('Creneaux_travail') ?></th><tr>
            </thead>
            <tbody>
            <?php foreach ($jours as $idJour => $jour) : ?>
                <tr data-id-jour=<?= $idJour ?>><td name="nom"><?= $jour ?></td><td class="creneaux"></td></tr>
            <?php endforeach ;?>
            </tbody>
            <script type="text/javascript">
            new planningController("<?= $linkId ?>", <?= json_encode($optionsImpaires) ?>, <?= json_encode($creneauxGroupesImpairs) ?>).readOnly();
            </script>
        </table>
    </div>
    <div id="<?= $idSemainePaire ?>"><h4><?= _('resp_temps_partiel_sem_paires') ?></h4>
        <table class="table table-hover table-responsive table-condensed table-striped" id="<?= $optionsPaires['tableId'] ?>">
            <thead>
                <tr><th width="20%"><?= _('Jour') ?></th><th><?= _('Creneaux_travail') ?></th><tr>
            </thead>
            <tbody>
            <?php foreach ($jours as $idJour => $jour) : ?>
                <tr data-id-jour=<?= $idJour ?>><td name="nom"><?= $jour ?></td><td class="creneaux"></td></tr>
            <?php endforeach ;?>
            </tbody>
            <script type="text/javascript">
            new planningController("<?= $linkId ?>", <?= json_encode($optionsPaires) ?>, <?= json_encode($creneauxGroupesPairs) ?>).readOnly();
            </script>
        </table>
    </div>
    <script>new semaineDisplayer("<?= $idToggleSemaine ?>", "<?= \App\Models\Planning\Creneau::TYPE_SEMAINE_COMMUNE ?>", <?=  json_encode($typesSemaines) ?>, <?= json_encode($text) ?>).init().readOnly()</script>
    <h3>Employés associés</h3>
    <?php if (empty($utilisateursAssocies)) : ?>
        <div><?= _('resp_tout_utilisateur_associe') ?></div>
    <?php else : ?>
        <div class="form-group col-md-4 col-sm-5">
            <label class="control-label col-md-3 col-sm-3" for="groupe">Groupe&nbsp;:</label>
            <div class="col-md-8 col-sm-8">
                <select class="form-control" name="groupeId" id="groupe">
                    <option value="<?= NIL_INT ?>">Tous</option>
                    <?php foreach ($optionsGroupes as $idGroupe => $groupe) :?>
                        <option value="<?= $idGroupe ?>"><?= $groupe['nom'] ?></option>
                    <?php endforeach ; ?>
                </select>
            </div>
        </div>
        <br><br><br>
        <div>
        <?php foreach ($utilisateursAssocies as $utilisateur) : ?>
            <?php
            $disabled = (\App\ProtoControllers\Utilisateur::hasSortiesEnCours($utilisateur['login']))
                ? 'disabled '
                : '';
            $checked = ($idPlanning === $utilisateur['planningId'])
                ? 'checked '
                : '';
            $nom = \App\ProtoControllers\Utilisateur::getNomComplet($utilisateur['prenom'], $utilisateur['nom']);
            ?>
            <div class="checkbox-utilisateur" data-user-login="<?= $utilisateur['login'] ?>">
                <label><input type="checkbox" name="utilisateurs[]" value="<?= $utilisateur['login'] ?>" <?= $disabled . '' . $checked ?> />&nbsp;<?= $nom ?></label>
            </div>
        <?php endforeach ; ?>
        </div>
        <script type="text/javascript">
            new selectAssociationPlanning("groupe", <?= json_encode($associations) ?>, <?= NIL_INT ?>);
        </script>
    <?php endif; ?>
    <br><input type="submit" class="btn btn-success" value="<?= _('form_submit') ?>" />
</form>
