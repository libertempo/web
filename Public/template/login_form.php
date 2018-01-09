<?php

$error_message = NULL;
if ($erreur == 'login_passwd_incorrect') {
    $error_message = _('login_passwd_incorrect');
} elseif ($erreur=='login_non_connu') {
    $error_message = _('login_non_connu');
} elseif( 'session-invalid' == getpost_variable('error', false)){
    $error_message = _('session_pas_session_ouverte');
}
?>
<div class="container">
    <div class="form-signin">
    <?php if($error_message): ?>
        <div class="alert alert-warning">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <?= $error_message ?>
        </div>
    <?php endif;
    if(preg_match('/MSIE/i',$_SERVER['HTTP_USER_AGENT'])):?>
        <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <?= _('ie_non_gere') ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $PHP_SELF ?>">
        <h2 class="form-signin-heading">Connexion</h2>
        <input type="text" id="session_username" class="form-control" name="session_username" value="<?= $session_username ?>" placeholder="<?= _('divers_login_maj_1') ?>" autofocus>
        <input type="password" id="session_password" class="form-control" name="session_password" placeholder="Mot de passe" autocomplete="off"/>
        <button type="submit" class="btn btn-lg btn-primary btn-block"><?= _('form_submit') ?></button>
    </form>
    <script type="text/javascript">$('#login_form .submit').button();</script>
    </div>
</div>
