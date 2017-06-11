<?php
/*
 * Variables disponibles :
 * $calendar
 * $session
 * $evenements
 */

/* Div auto fermé par le bottom */ ?>
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

<?php
if ($jourDemande instanceof \DateTimeInterface) {
    $evenements->fetchEvenements(
        $jourDemande,
        $jourDemande->modify('+1 day'),
        $utilisateursATrouver,
        canSessionVoirEvenementEnTransit($_SESSION)
    );
    require_once VIEW_PATH . 'Calendrier/Jour.php';
} else {
    $evenements->fetchEvenements(
        $moisDemande,
        $moisDemande->modify('+1 month'),
        $utilisateursATrouver,
        canSessionVoirEvenementEnTransit($_SESSION)
    );
    require_once VIEW_PATH . 'Calendrier/Mois.php';
}
