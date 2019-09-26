<?php
/*
 * $errors
 * $message
 * $groupes
 */
?>
<h1><?= _('admin_onglet_gestion_groupe') ?></h1>
<?php if (!empty($errors)) : ?>
    <div class="alert alert-danger"><?= _('erreur_recommencer') ?><ul>
    <?php foreach ($errors as $k => $v) : ?>
        <li><?= $k ?> : <?= $v ?></li>
    <?php endforeach ; ?>
    </ul></div>
<?php endif ; ?>
<?php if (!empty($message)) : ?>
    <div class="alert alert-info"><?= $message ?>.</div>
<?php endif ; ?>
<a href="ajout_groupe" class="btn btn-success pull-right"><?= _('admin_groupes_new_groupe') ?></a>

<table class="table table-hover table-responsive table-condensed table-striped">
    <thead>
        <tr>
            <th><?= _('admin_groupes_groupe') ?></th>
            <th><?= _('admin_groupes_libelle') ?></th>
            <th><?= _('admin_groupes_nb_users') ?></th>
            <th><?= _('admin_groupes_double_valid') ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($groupes)) : ?>
        <tr><td colspan="5"><center><?= _('aucun_resultat') ?></center></td></tr>
    <?php else : ?>
        <?php $i = 0; ?>
        <?php foreach ($groupes as $groupe) : ?>
            <tr class="<?= ($i % 2 ? 'i' : 'p') ?>">
                <td><b><?= $groupe['name'] ?></b></td>
                <td><?= $groupe['comment'] ?></td>
                <td><?= count(\App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds([$groupe['id']])); ?></td>
                <td><?= \App\Helpers\Formatter::bool2String($groupe['double_validation']) ?></td>
                <td class="action">
                    <a href="modif_groupe?group=<?= $groupe['id'] ?>" title="<?= _('form_modif') ?>"><i class="fa fa-pencil"></i></a>
                    <a href="hr_index.php?onglet=suppr_groupe&group=<?= $groupe['id'] ?>" title="<?= _('form_supprim') ?>"><i class="fa fa-times-circle"></i></a>
                </td>
            </tr>
            <?php ++$i ;?>
        <?php endforeach ; ?>
    <?php endif ; ?>
    </tbody>
</table>
<hr/>
