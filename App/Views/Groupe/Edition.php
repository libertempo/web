<?php
/**
 * $message
 * $selectId
 * $DivGrandRespId
 * $PHP_SELF
 * $infosGroupe
 * $data (tmp)
 * $config
 * $idGroupe
 */
?>
<div onload="showDivGroupeGrandResp('<?= $selectId ?>','<?= $DivGrandRespId ?>');" class="form-group">
    <h1><?= $titre ?></h1>
    <?= $message ?>
    <form method="post" action=""  role="form">
        <table class="table">
            <thead>
                <tr>
                    <th><b><?= _('Nom du groupe') ?></b></th>
                    <th><?= _('admin_groupes_libelle') ?> / <?= _('divers_comment_maj_1') ?></th>
                    <?php if ($config->isDoubleValidationActive()) : ?>
                        <th><?= _('admin_groupes_double_valid') ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <input class="form-control" type="text" name="new_group_name" size="30" maxlength="50" value="<?= $infosGroupe['nom'] ?>" required>
                    </td>
                    <td>
                        <input class="form-control" type="text" name="new_group_libelle" size="50" maxlength="250" value="<?= $infosGroupe['comment'] ?>">
                    </td>
                    <?php if ($config->isDoubleValidationActive()) : ?>
                        <?php
                        $selectN = $infosGroupe['doubleValidation'] == 'N' ? 'selected="selected"' : '';
                        $selectY = $infosGroupe['doubleValidation'] == 'Y' ? 'selected="selected"' : '';
                        ?>
                        <td>
                            <select class="form-control" name="new_group_double_valid" id="<?= $selectId ?>" onchange="showDivGroupeGrandResp('<?= $selectId ?>','<?= $DivGrandRespId ?>');">
                                <option value="N" <?= $selectN ?>>N</option>
                                <option value="Y" <?= $selectY ?>>Y</option>
                            </select>
                        </td>
                    <?php endif; ?>
                </tr>
            </tbody>
        </table>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h2><?= _('admin_gestion_groupe_users_membres') ?></h2>
                <?= getFormChoixEmploye($idGroupe, $data) ?>
            </div>

            <div class="col-md-6">
                <h2><?= _('admin_gestion_groupe_resp_responsables') ?></h2>
                <?= getFormChoixResponsable($idGroupe, $selectId, $data) ?>
            </div>
            <div class="col-md-6 hide" id="<?= $DivGrandRespId ?>">
                <h2><?= _('admin_gestion_groupe_grand_resp_responsables') ?></h2>
                <?= getFormChoixGrandResponsable($idGroupe, $selectId, $data) ?>
            </div>
        </div>
    </div>

    <div class="form-group">
    <?php if (NIL_INT !== $idGroupe) : ?>
        <input type="hidden" name="_METHOD" value="PUT" />
        <input type="hidden" name="group" value="<?= $idGroupe ?>" />
    <?php endif; ?>
        <input class="btn btn-success" type="submit" value="<?= _('form_submit') ?>">
        <a class="btn" href="<?= $PHP_SELF ?>?onglet=liste_groupe"><?= _('form_annul') ?></a>
    </div>
</form>
