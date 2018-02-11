<?php
/*
 * $message
 * $notice
 * $valueName
 * $titre
 * $idToggleSemaine
 * $planningId
 * $jours
 * $creneauxGroupesCommuns
 * $creneauxGroupesImpairs
 * $creneauxGroupesPairs
 * $idSemaineCommune
 * $idSemaineImpaire
 * $idSemainePaire
 * $optionsSemaineCommune
 * $optionsSemaineImpaire
 * $optionsSemainePaire
 * $typeSemaine
 * $text
 * $utilisateursAssocies
 * $optionsGroupes
 * $associations
 * $isSemaineReadOnly
 */
?>
<h1><?= $titre ?></h1>
<?= $message ?>
<form action="" method="post" accept-charset="UTF-8"
enctype="application/x-www-form-urlencoded" class="form-group">
    <table class="table table-hover table-responsive table-condensed table-striped">
        <thead>
            <tr><th class="col-md-4"><?= _('Nom') ?></th><th></th></tr>
        </thead>
        <tbody>
            <?php if (NIL_INT !== $planningId) :?>
                <input type="hidden" name="planning_id" value="<?= $planningId ?>" />
                <input type="hidden" name="_METHOD" value="PUT" />
            <?php endif; ?>
            <tr><td><?= $valueName ?></td><td></td></tr>
            <tr><td><input type="text" name="name" value="<?= $valueName ?>" class="form-control" required /></td>
            <td><input type="button" id="<?= $idToggleSemaine ?>" class="btn btn-default " /></td></tr>
        </tbody>
    </table>
    <h3><?= _('Creneaux') ?></h3>
    <?php
    $idSemaine = $idSemaineCommune;
    $titreSemaine = _('hr_temps_partiel_sem');
    $optionsSemaine = $optionsSemaineCommune;
    $creneauxGroupes = $creneauxGroupesCommuns;

    if ($isSemaineReadOnly) {
        require VIEW_PATH . 'Planning/Formulaire/Semaine_Detail.php';
    } else {
        require VIEW_PATH . 'Planning/Formulaire/Semaine_Edit.php';
    }
    ?>
    <?php
    $idSemaine = $idSemaineImpaire;
    $titreSemaine = _('hr_temps_partiel_sem_impaires');
    $optionsSemaine = $optionsSemaineImpaire;
    $creneauxGroupes = $creneauxGroupesImpairs;

    if ($isSemaineReadOnly) {
        require VIEW_PATH . 'Planning/Formulaire/Semaine_Detail.php';
    } else {
        require VIEW_PATH . 'Planning/Formulaire/Semaine_Edit.php';
    }
    ?>
    <?php
    $idSemaine = $idSemainePaire;
    $titreSemaine = _('hr_temps_partiel_sem_paires');
    $optionsSemaine = $optionsSemainePaire;
    $creneauxGroupes = $creneauxGroupesPairs;

    if ($isSemaineReadOnly) {
        require VIEW_PATH . 'Planning/Formulaire/Semaine_Detail.php';
    } else {
        require VIEW_PATH . 'Planning/Formulaire/Semaine_Edit.php';
    }
    ?>
<?php if ($isSemaineReadOnly) :?>
    <script>new semaineDisplayer("<?= $idToggleSemaine ?>", "<?= \App\Models\Planning\Creneau::TYPE_SEMAINE_COMMUNE ?>", <?= json_encode($typeSemaine) ?>, <?= json_encode($text) ?>).init().readOnly();</script>
<?php else : ?>
    <script>new semaineDisplayer("<?= $idToggleSemaine ?>", "<?= \App\Models\Planning\Creneau::TYPE_SEMAINE_COMMUNE ?>", <?= json_encode($typeSemaine) ?>, <?= json_encode($text) ?>).init();</script>
<?php endif ; ?>
    <h3>Employés associés</h3>
    <?php if (empty($utilisateursAssocies)) : ?>
        <div><?= _('hr_tout_utilisateur_associe') ?></div>
    <?php else : ?>
        <div class="form-group col-md-4 col-sm-5">
            <label class="control-label col-md-3 col-sm-3" for="groupe">Groupe&nbsp;:</label>
            <div class="col-md-8 col-sm-8"><select class="form-control" name="groupeId" id="groupe">
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
        $checked = ($planningId === $utilisateur['planningId'])
            ? 'checked '
            : '';
        $nom = \App\ProtoControllers\Utilisateur::getNomComplet($utilisateur['prenom'], $utilisateur['nom']);
        ?>
        <div class="checkbox-utilisateur" data-user-login="<?= $utilisateur['login'] ?>">
            <label><input type="checkbox" name="utilisateurs[]" value="<?= $utilisateur['login'] ?>" <?= $disabled . ' ' . $checked ?> />&nbsp;<?= $nom ?></label>
        </div>
    <?php endforeach ; ?>
    </div>
    <script type="text/javascript">
    new selectAssociationPlanning("groupe", <?= json_encode($associations) ?>, <?= NIL_INT ?>);
    </script>
    <?php endif ; ?>
    <br><input type="submit" class="btn btn-success" value="<?= _('form_submit') ?>" />
</form>
