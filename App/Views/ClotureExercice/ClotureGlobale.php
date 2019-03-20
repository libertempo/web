<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script>generateDatePicker(<?php json_encode($datePickerOpts) ?>, false);</script>
<h1><?= $titre ?></h1>
<?php if ($commitSuccess) : ?>
    <div class="alert alert-success">Changement d'exercice effectué.</div
<?php endif; ?>
<?php if (!empty($error)) : ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>
<h2>Vous êtes sur le point de cloturer l'exercice actuel pour tous les utilisateurs actifs.</h2>
<form action="cloture_exercice" method="POST">
    <?php if ($isReliquatsAutorise && 0 != $DateReliquats) : ?>
    <div class="form-group row">
        <div class="col-xs-12">
            <label for="annee">Veuillez saisir l'année de la date limite des reliquats 
                <a href="#" data-toggle="tooltip" title="le jour et le mois (<?= $DateReliquats ?>) sont définis dans la configuration globale">
                    <i class="fa fa-question-circle"></i>
                </a> :
            </label>
        </div>
        <div class="col-xs-8 col-sm-2">
            <input class="form-control date" type="text" id="annee" name="annee" required placeholder="<?= date('Y'); ?>">
        </div>
    </div>
    <?php endif; ?>
    <div class="checkbox">
        <label><input type="checkbox" name="feries" value="1"> Valider automatiquement les jours fériés pour l'année à venir ?</label>
    </div>
    <input type="hidden" name="cloture_globale" value=1>
    <button type="submit" class="btn btn-danger">Valider la clôture globale</button>
</form>
<script>
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();   
});
</script>