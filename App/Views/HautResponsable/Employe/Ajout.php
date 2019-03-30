<?php declare(strict_types = 1);
/**
 * $message
 * $soldeHeureId
 * $readOnly
 * $optLdap
 */
?>

<h1><?= _('Nouvel Utilisateur') ?></h1>
<?= $message ?>

<form id="manageUser" action="" method="post" accept-charset="UTF-8"
enctype="application/x-www-form-urlencoded" class="form-group">
    <table class="table table-hover table-responsive table-striped table-condensed">
        <thead>
            <tr>
                <th><?= _('Identifiant') ?></th>
                <th><?= _('Nom') ?></th>
                <th><?= _('Prénom') ?></th>
                <th><?= _('Quotité') ?></th>
                <?php if ($config->isHeuresAutorise()) : ?>
                    <th><?= _('solde d\'heure') ?></th>
                <?php endif; ?>
                <th><?= _('Responsable?') ?></th>
                <th><?= _('Administrateur?') ?></th>
                <th><?= _('Haut responsable?') ?></th>
                <th><?= _('activé?') ?></th>
                <?php if (!$config->isUsersExportFromLdap()) : ?>
                    <th><?= _('Email') ?></th>
                <?php endif; ?>
                <?php if ($config->getHowToConnectUser() == "dbconges") : ?>
                    <th><?= _('mot de passe') ?></th>
                    <th><?= _('ressaisir mot de passe') ?></th>
                <?php endif ?>
            </tr>
        </thead>
        <tbody>
            <tr class="update-line">
                <td><input class="form-control" type="text" name="new_login" size="10" maxlength="99" value="<?= $formValue['login'] ?>" "<?= $readOnly ?>" required></td>
                <td>
                    <input class="form-control" type="text" id="new_nom" name="new_nom" size="10" maxlength="30" value="<?= $formValue['nom'] ?>" "<?= $optLdap ?>" required>
                    <ul class="suggestions" id="suggestions"></ul>
                </td>
                <td><input class="form-control" type="text" name="new_prenom" size="10" maxlength="30" value="<?= $formValue['prenom'] ?>" "<?= $readOnly ?>" required></td>
                <td><input class="form-control" type="text" name="new_quotite" size="3" maxlength="3" value="<?= $formValue['quotite'] ?>" required></td>
                <?php if ($config->isHeuresAutorise()) : ?>
                    <td>
                        <input class="form-control" type="text" name="new_solde_heure" id="<?= $soldeHeureId ?>" size="6" maxlength="6" value="<?= $formValue['soldeHeure'] ?>">
                    </td>
                <?php endif; ?>
                <td>
                    <select class="form-control" name="new_is_resp">
                        <option value="N" <?= $formValue['isResp'] == 'N' ? 'selected' : ''?>>N</option>
                        <option value="Y" <?= $formValue['isResp'] == 'Y' ? 'selected' : ''?>>Y</option>
                    </select>
                </td>
                <td>
                    <select class="form-control" name="new_is_admin">
                        <option value="N" <?= $formValue['isAdmin'] == 'N' ? 'selected' : ''?>>N</option>
                        <option value="Y" <?= $formValue['isAdmin'] == 'Y' ? 'selected' : ''?>>Y</option>
                    </select>
                </td>
                <td>
                    <select class="form-control" name="new_is_hr">
                        <option value="N" <?= $formValue['isHR'] == 'N' ? 'selected' : ''?>>N</option>
                        <option value="Y" <?= $formValue['isHR'] == 'Y' ? 'selected' : ''?>>Y</option>
                    </select>
                </td>
                <td>
                    <select class="form-control" name="new_is_active">
                        <option value="Y" <?= $formValue['isActive'] == 'Y' ? 'selected' : '' ?>>Y</option>
                        <option value="N" <?= $formValue['isActive'] == 'N' ? 'selected' : '' ?>>N</option>
                    </select>
                </td>
                <?php if (!$config->isUsersExportFromLdap()) : ?>
                    <td><input class="form-control" type="text" name="new_email" size="10" maxlength="99" value="<?= $formValue['email'] ?>"></td>
                <?php endif ;?>
                <?php if ($config->getHowToConnectUser() == "dbconges") : ?>
                    <td><input class="form-control" type="password" name="new_password1" size="10" maxlength="15" value="" autocomplete="off"></td>
                    <td><input class="form-control" type="password" name="new_password2" size="10" maxlength="15" value="" autocomplete="off"></td>
                <?php endif ;?>
            </tr>
        </tbody>
        <script type="text/javascript">
            generateTimePicker("<?= $soldeHeureId ?>");
        </script>
    </table>
    <br><hr>
    <?= getFormUserSoldes($formValue); ?>
    <br><hr>
    <?= getFormUserGroupes($formValue); ?>
    <hr>
    <input class="btn btn-success" type="submit" value="<?= _('form_submit') ?>">
</form>
