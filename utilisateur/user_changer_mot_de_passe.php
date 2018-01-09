<?php
defined( '_PHP_CONGES' ) or die( 'Restricted access' );
$config = new \App\Libraries\Configuration(\includes\SQL::singleton());

if ($config->getMailFromLdap()) {
    include_once CONFIG_PATH .'config_ldap.php';
}

$titre = _('user_change_password');
$self = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

if (getpost_variable('change_passwd', 0) == 1) {
    $new_passwd1 = getpost_variable('new_passwd1');
    $new_passwd2 = getpost_variable('new_passwd2');

    if ((strlen($new_passwd1)==0) || (strlen($new_passwd2)==0) || ($new_passwd1 != $new_passwd2)) {
        $error = _('user_passwd_error');
    } else {
        $passwd_md5 = md5($new_passwd1);
        $sql1 = 'UPDATE conges_users SET  u_passwd=\''.$passwd_md5.'\' WHERE u_login=\''.$_SESSION['userlogin'].'\' ';
        $result = \includes\SQL::query($sql1) ;

        if (!$result) {
            $error = _('form_modif_not_ok');
        }
    }

    $comment_log = 'changement Password';
    log_action(0, '', $_SESSION['userlogin'], $comment_log);
}

require_once VIEW_PATH . 'Employe/ChangerMotDePasse.php';
