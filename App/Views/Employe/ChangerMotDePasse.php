<?php
/*
 * $titre
 * $self
 * $onglet
 * $error
 */
?>
<h1><?= $titre ?></h1>
<?php if (!empty($error)) : ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>
<form action="<?= $self ?>?onglet=<?= $onglet ?>" method="POST">
    <table cellpadding="2" class="tablo" width="500">
        <thead>
            <tr>
                <th class="titre"><?= _('user_passwd_saisie_1') ?></th>
                <th class="titre"><?= _('user_passwd_saisie_2') ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><input class="form-control" type="password" name="new_passwd1" size="10" maxlength="20" value="" autocomplete="off"></td>
                <td><input class="form-control" type="password" name="new_passwd2" size="10" maxlength="20" value="" autocomplete="off"></td>
            </tr>
        </tbody>
    </table>
    <hr/>
    <input type="hidden" name="change_passwd" value=1>
    <input class="btn btn-success" type="submit" value="<?= _('form_submit') ?>">
</form>
