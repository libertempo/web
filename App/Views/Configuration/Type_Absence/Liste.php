<?php
/*
 * $listeTypeConges
 * $url
 * $nouveauLibelle
 * $nouveauLibelleCourt
 * $enumTypeConges
 */
?>
<h1><?= _('config_abs_titre') ?></h1>
<?php foreach ($listeTypeConges as $typeConge => $conges) : ?>
    <h2><?= _('divers_' . $typeConge . '_maj_1') ?></h2>
    <p><?= _('config_abs_comment_' . $typeConge) ?></p>
    <table class="table table-hover table-responsive table-condensed table-striped">
        <tr>
            <th><?= _('config_abs_libelle') ?></th>
            <th><?= _('config_abs_libelle_short') ?></th>
            <th></th>
        </tr>
    <?php if (empty($conges)) : ?>
        <tr><td colspan="2"><center><?= _('aucun_resultat') ?></center></td></tr>
    <?php else : ?>
        <?php foreach ($conges as $conge) : ?>
            <tr>
                <td><strong><?= $conge['libelle'] ?></strong></td>
                <td><?= $conge['libelleCourt'] ?></td>
                <td class="action">
                    <a href="<?= $PHP_SELF ?>?action=modif&id_to_update=<?= $conge['id'] ?>" title=" <?= _('form_modif') ?>"><i class="fa fa-pencil"></i></a>
                    &nbsp;
                    <?php if (!$conge['typeNatif']) : ?>
                        <a href="<?= $PHP_SELF ?>?action=suppr&id_to_update=<?= $conge['id'] ?>" title=" <?= _('form_supprim') ?>"><i class="fa fa-times-circle"></i></a>
                    <?php else : ?>
                        <i class="fa fa-times-circle disabled"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach ; ?>
    <?php endif ?>
    </table>
    <hr/>
<?php endforeach ; ?>

<h2><?= _('config_abs_add_type_abs') ?></h2>
<p><?= _('config_abs_add_type_abs_comment') ?></p>
<form action="<?= $url ?>" method="POST">
    <table class="table table-hover table-responsive table-condensed table-striped">
        <tr>
            <th><?= _('config_abs_libelle') ?></th>
            <th><?= _('config_abs_libelle_short') ?></th>
            <th><?= _('divers_type') ?></th>
        </tr><tr>
            <td><input class="form-control" type="text" name="tab_new_values[libelle]" size="20" maxlength="20" value="<?= $nouveauLibelle ?>"></td>
            <td><input class="form-control" type="text" name="tab_new_values[short_libelle]" size="3" maxlength="3" value="<?= $nouveauLibelleCourt ?>"></td>
            <td>
                <select class="form-control" name=tab_new_values[type]>
                <?php foreach (array_keys($listeTypeConges) as $typeConge) : ?>
                    <option <?= ($typeConge == $nouveauType) ? 'selected' : '' ?>><?= $typeConge ?></option>
                <?php endforeach ;?>
                </select>
            </td>
        </tr>
    </table>
    <input type="hidden" name="action" value="new">
    <hr/>
    <input type="submit" class="btn btn-success" value="<?= _('form_ajout') ?>"><br>
</form>
