<?php declare(strict_types = 1);
/**
 * $message
 * $soldeHeureId
 * $readOnly
 * $optLdap
 * $formValue
 * $config
 * $typeAbsencesConges
 * $typeAbsencesExceptionnels
 * $groupes
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
                <td><input class="form-control" type="text" name="new_login" size="10" maxlength="99" value="<?= $formValue['login'] ?>" <?= $readOnly ?> required></td>
                <td>
                    <input class="form-control" type="text" id="new_nom" name="new_nom" size="10" maxlength="30" value="<?= $formValue['nom'] ?>" <?= $optLdap ?> required>
                    <ul class="suggestions" id="suggestions"></ul>
                </td>
                <td><input class="form-control" type="text" name="new_prenom" size="10" maxlength="30" value="<?= $formValue['prenom'] ?>" <?= $readOnly ?> required></td>
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

    <table class="table table-hover table-responsive table-striped table-condensed">
        <thead>
            <tr>
                <th colspan=3><h4><?= _('Soldes') ?> </h4></th>
            </tr>
            <tr>
                <th></th>
                <th><?= _('admin_new_users_nb_par_an') ?></th>
                <th><?= _('divers_solde') ?></th>
                <th><?= _('Reliquat') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($typeAbsencesConges as $typeId => $infoType) :?>
            <tr>
            <?php
            $joursAn = $formValue['joursAn'][$typeId] ?? 0;
            $solde = $formValue['soldes'][$typeId] ?? 0;
            $reliquat = $formValue['reliquat'][$typeId] ?? 0;
            ?>
                <td><?= $infoType['libelle'] ?></td>
                <td><input class="form-control" type="text" name="tab_new_jours_an[<?= $typeId ?>]" size="5" maxlength="5" value="<?= $joursAn ?>"></td>
                <td><input class="form-control" type="text" name="tab_new_solde[<?= $typeId ?>]" size="5" maxlength="5" value="<?= $solde ?>"></td>
                <td><input class="form-control" type="text" name="tab_new_reliquat[<?= $typeId ?>]" size="5" maxlength="5" value="<?= $reliquat ?>"></td>
            </tr>
        <?php endforeach ;?>
        <?php foreach ($typeAbsencesExceptionnels as $typeId => $infoType) : ?>
            <tr>
            <?php
            $solde = $formValue['soldes'][$typeId] ?? 0;
            ?>
                <td><?= $infoType['libelle'] ?></td>
                <td><input type="hidden" name="tab_new_jours_an[<?= $typeId ?>]" size="5" maxlength="5" value="0"></td>
                <td><input class="form-control" type="text" name="tab_new_solde[<?= $typeId ?>]" size="5" maxlength="5" value="<?= $solde ?>"></td>
                <td><input type="hidden" name="tab_new_reliquat[<?= $typeId ?> ]" size="5" maxlength="5" value="0"></td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    <br>
    <br><hr>
    <table class="table table-hover table-responsive table-striped table-condensed">
        <thead>
            <tr>
                <th colspan=3><h4><?= _('Groupes') ?></h4></th>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <th>&nbsp;<?= _('Nom') ?></th>
                <th>&nbsp;<?= _('Description') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($groupes as $groupeId => $groupeInfos) : ?>
            <tr>
                <td>
                <?php
                $checked = in_array($groupeId, $formValue['groupesId']) ? 'checked' : '';
                ?>
                    <input type="checkbox" name="checkbox_user_groups[<?= $groupeId ?>]" value="<?= $groupeId ?>" <?= $checked ?>>
                </td>
                <td>&nbsp;<?= $groupeInfos['g_groupename'] ?>&nbsp</td>
                <td>&nbsp;<?= $groupeInfos['g_comment'] ?>&nbsp;</td>
            </tr>
        <?php endforeach ;?>
        <tbody>
    </table>
    <hr>
    <input class="btn btn-success" type="submit" value="<?= _('form_submit') ?>">
</form>
