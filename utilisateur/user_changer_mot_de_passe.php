<?php

defined('_PHP_CONGES') or die('Restricted access');
echo modificationMotDePasseModule($onglet);

/**
 * Encapsule le comportement du module de modification de mot de passe
 *
 * @param string $onglet Nom de l'onglet à afficher
 *
 * @return void
 * @access public
 * @static
 */
function modificationMotDePasseModule($onglet)
{
    $return = '';
    if ($_SESSION['config']['where_to_find_user_email'] == "ldap") {
        include_once CONFIG_PATH . 'config_ldap.php';
    }

    $change_passwd = getpost_variable('change_passwd', 0);
    $new_passwd1   = getpost_variable('new_passwd1');
    $new_passwd2   = getpost_variable('new_passwd2');

    if ($change_passwd == 1) {
        $return .= change_passwd($new_passwd1, $new_passwd2);
    } else {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $session  = session_id();

        $return .= '<h1>' . _('user_change_password') . '</h1>';
        $return .= '<form action="' . $PHP_SELF . '?session=' . $session . '&onglet=' . $onglet . '" method="POST">';
        $return .= '<table cellpadding="2" class="tablo" width="500">';
        $return .= '<thead>';
        /*
        echo '<tr>
        <td class="titre">'. _('user_passwd_saisie_1') .'</td>
        <td class="titre">'. _('user_passwd_saisie_2') .'</td>
        </tr>';
         */
        $return .= '<tr>
                <th class="titre">' . _('user_passwd_saisie_1') . '</th>
                <th class="titre">' . _('user_passwd_saisie_2') . '</th>
                </tr>';
        $return .= '</thead>';
        $return .= '<tbody>';

        $text_passwd1 = '<input class="form-control" type="password" name="new_passwd1" size="10" maxlength="20" value="" autocomplete="off">';
        $text_passwd2 = '<input class="form-control" type="password" name="new_passwd2" size="10" maxlength="20" value="" autocomplete="off">';
        $return .= '<tr>';
        $return .= '<td>' . ($text_passwd1) . '</td><td>' . ($text_passwd2) . '</td>' . "\n";
        $return .= '</tr>';

        $return .= '</tbody>';
        $return .= '</table>';

        $return .= '<hr/>';
        $return .= '<input type="hidden" name="change_passwd" value=1>';
        $return .= '<input class="btn btn-success" type="submit" value="' . _('form_submit') . '">';
        $return .= '</form>';
    }

    return $return;
}

function change_passwd($new_passwd1, $new_passwd2)
{
    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $session  = session_id();
    $return   = '';

    if ((strlen($new_passwd1) == 0) || (strlen($new_passwd2) == 0) || ($new_passwd1 != $new_passwd2)) // si les 2 passwd sont vides ou differents
    {
        $return .= _('user_passwd_error') . "<br>\n";
    } else {
        $passwd_md5 = md5($new_passwd1);
        $sql1       = 'UPDATE conges_users SET  u_passwd=\'' . $passwd_md5 . '\' WHERE u_login=\'' . $_SESSION['userlogin'] . '\' ';
        $result     = \includes\SQL::query($sql1);

        if ($result) {
            $return .= _('form_modif_ok') . " <br><br> \n";
        } else {
            $return .= _('form_modif_not_ok') . "<br><br> \n";
        }

    }

    $comment_log = 'changement Password';
    log_action(0, '', $_SESSION['userlogin'], $comment_log);

    return $return;
}
