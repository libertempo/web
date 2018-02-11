<?php
/*
 * $message
 * $planning
 * $idToggleSemaine
 * $idSemaineCommune
 * $idSemaineImpaire
 * $idSemainePaire
 * $creneauxGroupesCommuns
 * $creneauxGroupesImpairs
 * $creneauxGroupesPairs
 * $jours
 * $optionsSemaineCommune
 * $optionsSemaineImpaire
 * $optionsSemainePaire
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
                <td><input type="hidden" id="<?= $idToggleSemaine ?>" /></td>
            </tr>
        </tbody>
    </table>
    <h3><?= _('Creneaux') ?></h3>
    <?php
    $idSemaine = $idSemaineCommune;
    $titreSemaine = _('resp_temps_partiel_sem');
    $optionsSemaine = $optionsSemaineCommune;
    $creneauxGroupes = $creneauxGroupesCommuns;

    require VIEW_PATH . 'Planning/Formulaire/Semaine_Detail.php';
    ?>
    <?php
    $idSemaine = $idSemaineImpaire;
    $titreSemaine = _('resp_temps_partiel_sem_impaires');
    $optionsSemaine = $optionsSemaineImpaire;
    $creneauxGroupes = $creneauxGroupesImpairs;

    require VIEW_PATH . 'Planning/Formulaire/Semaine_Detail.php';
    ?>
    <?php
    $idSemaine = $idSemainePaire;
    $titreSemaine = _('resp_temps_partiel_sem_paires');
    $optionsSemaine = $optionsSemainePaire;
    $creneauxGroupes = $creneauxGroupesPairs;

    require VIEW_PATH . 'Planning/Formulaire/Semaine_Detail.php';
    ?>
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
            $checked = ($planning->id === $utilisateur['planningId'])
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
            new selectAssociationPlanning("groupe", <?= json_encode($associationsGroupe) ?>, <?= NIL_INT ?>);
        </script>
    <?php endif; ?>
    <br><input type="submit" class="btn btn-success" value="<?= _('form_submit') ?>" />
</form>
