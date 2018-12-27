<?php
/*
 * $titre
 * $message
 * $listIdUsed
 * $plannings
 * $isHr
 * $lienModif
 */
?>
<?php if ($isHr) : ?>
<a href="<?= ROOT_PATH ?>hr/ajout_planning" style="float:right" class="btn btn-success"><?= _('hr_ajout_planning') ?></a>
<?php endif ?>
<h1><?= $titre ?></h1>
<?= $message ?>
<table class="table table-hover table-responsive table-condensed table-striped">
<thead>
    <tr><th><?= _('divers_nom_maj_1') ?></th><th style="width:10%"></th></tr>
</thead>
<tbody>
<?php if (empty($plannings)) : ?>
    <tr><td colspan="2"><center><?= _('aucun_resultat') ?></center></td></tr>
<?php else : ?>
    <?php foreach ($plannings as $planning) : ?>
        <tr><td><?= $planning['name'] ?></td>
            <td>
                <form action="" method="post" accept-charset="UTF-8"
                enctype="application/x-www-form-urlencoded">
                <a title="<?= _('form_modif') ?>" href="<?= $lienModif ?>?id=<?= $planning['id'] ?>"><i class="fa fa-pencil"></i></a>&nbsp;&nbsp;
                <?php if ($isHr) : ?>
                    <?php if (in_array($planning['id'], $listIdUsed)) : ?>
                        <button title="<?= _('planning_used') ?>" type="button" class="btn btn-link disabled"><i class="fa fa-times-circle"></i></button>
                    <?php else : ?>
                            <input type="hidden" name="planning_id" value="<?= $planning['id'] ?>" />
                            <input type="hidden" name="_METHOD" value="DELETE" />
                            <button type="submit" class="btn btn-link" title="<?= _('form_supprim') ?>"><i class="fa fa-times-circle"></i></button>
                    <?php endif ?>
                <?php endif ?>
                </form>
            </td>
        </tr>
    <?php endforeach ?>
<?php endif ?>
</tbody>
</table>
