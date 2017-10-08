<?php
/*
 * $canUserSaisi
 * $errorsLst
 * $notice
 * $champsRecherche
 * $dataHeures
 * $urlSaisie
 * $texteSaisie
 */

use \App\Models\AHeure;

?>
<?php if ($canUserSaisi) : ?>
    <a href="<?= ROOT_PATH . $urlSaisie ?>" style="float:right" class="btn btn-success"><?= $texteSaisie ?></a>
<?php endif; ?>
<h1><?= $titre ?></h1>
<?php if (!empty($errorsLst)) : ?>
    <div class="alert alert-danger"><?= _('erreur_recommencer') ?> :<ul>
    <?php foreach ($errorsLst as $error) : ?>
        <li><?= $error ?></li>
    <?php endforeach; ?>
    </ul></div>
<?php endif; ?>
<?php if (!empty($notice)) : ?>
    <div class="alert alert-info"><?= $notice ?>.</div>
<?php endif; ?>

<form method="post" action="" class="form-inline search" role="form">
    <div class="form-group">
        <label class="control-label col-md-4" for="statut">Statut&nbsp;:</label>
        <div class="col-md-8">
            <select class="form-control" name="search[statut]" id="statut">
                <option value="all"><?= _('tous') ?></option>
                <?php foreach (AHeure::getOptionsStatuts() as $key => $value) : ?>
                    <?php
                    $selected = (isset($champsRecherche['statut']) && $key == $champsRecherche['statut'])
                        ? 'selected="selected"'
                        : '';
                    ?>
                    <option value="<?= $key ?>" <?= $selected ?>><?= $value ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-4" for="annee">Année&nbsp;:</label>
        <div class="col-md-8">
            <select class="form-control" name="search[annee]" id="sel1">
                <?php foreach (\utilisateur\Fonctions::getOptionsAnnees() as $key => $value) : ?>
                    <?php
                    $selected = (isset($champsRecherche['annee']) && $key == $champsRecherche['annee'])
                        ? 'selected="selected"'
                        : '';
                    ?>
                    <option value="<?= $key ?>" <?= $selected ?>><?= $value ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <div class="input-group">
            <button type="submit" class="btn btn-default"><i class="fa fa-search" aria-hidden="true"></i></button>&nbsp;<a href="<?= ROOT_PATH ?>utilisateur/user_index.php?onglet=liste_heure_repos" type="reset" class="btn btn-default">Reset</a>
        </div>
    </div>
</form>
<table class="table table-hover table-responsive table-condensed table-striped">
    <thead>
        <tr><th><?= _('jour') ?></th>
            <th><?= _('divers_debut_maj_1') ?></th>
            <th><?= _('divers_fin_maj_1') ?></th>
            <th><?= _('duree') ?></th>
            <th><?= _('statut') ?></th>
            <th><?= _('commentaire') ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($dataHeures)) : ?>
        <tr><td colspan="7"><center><?= _('aucun_resultat') ?></center></td></tr>
    <?php else : ?>
        <?php foreach ($dataHeures as $heure) : ?>
            <tr>
                <td><?= $heure->jour ?></td>
                <td><?= $heure->debut ?></td>
                <td><?= $heure->fin ?></td>
                <td><?= $heure->duree ?></td>
                <td><?= $heure->statut ?></td>
                <td><?= $heure->comment ?></td>
                <td>
                <?php if ($data->isModifiable) : ?>
                    <form action="" method="post" accept-charset="UTF-8"
            enctype="application/x-www-form-urlencoded">
                        <a title="<?= _('form_modif') ?>" href="<?= $heure->urlModification ?>">
                            <i class="fa fa-pencil"></i>
                        </a>
                        <input type="hidden" name="id_heure" value="<?= $heure->idHeure ?>" />
                        <input type="hidden" name="_METHOD" value="DELETE" />
                        <button type="submit" class="btn btn-link" title="<?= _('Annuler') ?>"><i class="fa fa-times-circle"></i></button>
                    </form>
                <?php else : ?>
                    <i class="fa fa-pencil disabled" title="<?= _('heure_non_modifiable') ?>"></i>
                    <button title="<?= _('heure_non_supprimable') ?>" type="button" class="btn btn-link disabled">
                        <i class="fa fa-times-circle"></i>
                    </button>
                <?php endif; ?>
                </td>
            </tr>
        <?php endforeach ?>
    <?php endif; ?>
    </tbody>
</table>
