<?php
/**
 * $titre
 * $message
 * $typeAbsencesConges
 * $typeAbsencesExceptionnels
 * $isHeuresAutorises
 */
?>

<?php if (!empty($message)) : ?>
    <div class="alert alert-info"><?= $message ?></div>
<?php endif; ?>
<a href="<?= ROOT_PATH ?>hr/ajout_user" style="float:right" class="btn btn-success"><?= _('admin_onglet_add_user') ?></a>
<h1><?= $titre ?></h1>
<table class="table table-hover table-responsive table-striped table-condensed">
    <thead>
        <tr>
            <th><?= _('user') ?></th>
            <th><?= _('divers_quotite_maj_1') ?></th>
            <?php foreach ($typeAbsencesConges as $infoType) : ?>
                <th><?= $infoType['libelle'] ?> / <?= _('divers_an') ?></th>
                <th><?= _('divers_solde') ?> <?= $infoType['libelle'] ?></th>
            <?php endforeach; ?>
            <?php foreach ($typeAbsencesExceptionnels as $infoType) : ?>
                <th><?= _('divers_solde') ?> <?= $infoType['libelle'] ?></th>
            <?php endforeach; ?>
            <?php if ($isHeuresAutorises) : ?>
                <th><?= _('divers_solde') ?> <?= _('heures') ?></th>
            <?php endif; ?>
            <th></th>
            <th></th>
            <?php if (($config->getHowToConnectUser() == "dbconges")) : ?>
                <th></th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($infoUsers as $login => $infosUser) : ?>
        <tr class="<?= $infosUser['u_is_active'] == 'Y' ? 'actif' : 'inactif' ?>">
            <td class="utilisateur">
                <strong><?= $infosUser['u_nom'] ?> <?= $infosUser['u_prenom'] ?></strong>
                <span class="login"><?= $login ?></span>
                <span class="mail"><?= $infosUser['u_email'] ?></span>
                <?php if (count($infosUser['rights']) > 0) : ?>
                    <span class="rights"><?= implode(', ', $infosUser['rights']) ?></span>
                <?php endif; ?>
                <span class="responsable"> responsables : <strong><?= implode(', ', $infosUser['responsables']) ?></strong></span>
            </td>
            <td><?= $infosUser['u_quotite'] ?> %</td>
            <?php foreach ($typeAbsencesConges as $congesId => $infoType) : ?>
                <td><?= isset($infosUser['solde'][$congesId]) ? $infosUser['solde'][$congesId]['su_nb_an'] : 0 ?></td>
                <td><?= isset($infosUser['solde'][$congesId]) ? $infosUser['solde'][$congesId]['su_solde'] : 0 ?></td>
            <?php endforeach; ?>
            <?php foreach ($typeAbsencesExceptionnels as $congesId => $infoType) : ?>
                <td><?= isset($infosUser['solde'][$congesId]) ? $infosUser['solde'][$congesId]['su_solde'] : 0 ?></td>
            <?php endforeach; ?>
            <?php if ($isHeuresAutorises) : ?>
                <td><?= \App\Helpers\Formatter::timestamp2Duree($infosUser['u_heure_solde']) ?></td>
            <?php endif; ?>
            <td>
                <a href="hr_index.php?onglet=traite_user&user_login=<?= $login ?>" title="<?= _('resp_etat_users_afficher') ?>">
                    <i class="fa fa-eye"></i>
                </a>
            </td>
            <td>
                <a href="../edition/edit_user.php?user_login=<?= $login ?>" target="_blank" title="<?= _('resp_etat_users_imprim') ?>">
                    <i class="fa fa-file-text"></i>
                </a>
            </td>
            <td>
                <a href="hr_index.php?onglet=modif_user&login=<?= $login ?>" title="<?= _('form_modif') ?>">
                    <i class="fa fa-pencil"></i>
                </a>
            </td>
            <td>
                <a href="hr_index.php?onglet=suppr_user&login=<?= $login ?>" title="<?= _('form_supprim') ?>">
                    <i class="fa fa-times-circle"></i>
                </a>
            </td>
        </tr>
    <?php endforeach ; ?>
    </tbody>
</table>
<br>
