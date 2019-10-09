<?php declare(strict_types = 1);

defined('_PHP_CONGES') or die('Restricted access');

$message   = '';
$sql = \includes\SQL::singleton();
$config = new \App\Libraries\Configuration($sql);
$typeAbsencesConges = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges');
$groupes = \App\ProtoControllers\Groupe::getListeGroupes($sql);

$formValue = [
    'login' => '',
    'nom' => '',
    'prenom' => '',
    'quotite' => '100',
    'soldeHeure' => '00:00',
    'isResp' => 'N',
    'isAdmin' => 'N',
    'isHR' => 'N',
    'isActive' => 'Y',
    'email' => '',
    'pwd1' => '',
    'pwd2' => '',
    'groupesId' => [],
];

if (!empty($_POST)) {
    $errorsLst = [];
    $notice    = '';
    $formValue = dataForm2Array($_POST, $sql, $config);
    if (postFormUtilisateur($formValue, $errorsLst, $notice)) {
        redirect(ROOT_PATH . 'hr/page_principale?notice=' . $notice, false);
    } else {
        if (!empty($errorsLst)) {
            $errors = '';
            foreach ($errorsLst as $key => $value) {
                $errors .= '<li>' . $key . ' : ' . $value . '</li>';
            }
            $message = '<div class="alert alert-danger">' . _('erreur_recommencer') . ' :<ul>' . $errors . '</ul></div>';
        }
    }
}

$soldeHeureId = uniqid();
$readOnly = '';
$optLdap = '';
$typeAbsencesExceptionnels = [];
if ($config->isUsersExportFromLdap()) {
    $readOnly = 'readonly';
    $optLdap = 'onkeyup="searchLdapUser()" autocomplete="off"';
}

if ($config->isCongesExceptionnelsActive()) {
    $typeAbsencesExceptionnels = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges_exceptionnels');
}
/**
 * Nettoyage des données postés par le formulaire
 *
 * @param array $htmlPost
 * @param \includes\SQL $sql
 * @param \App\Libraries\Configuration $config
 *
 * @return array
 */
function dataForm2Array(array $htmlPost, \includes\SQL $sql, \App\Libraries\Configuration $config) : array
{
    $data['login'] = htmlentities($htmlPost['new_login'], ENT_QUOTES | ENT_HTML401);
    $data['oldLogin'] = key_exists('old_login', $htmlPost)
            ? htmlentities($htmlPost['old_login'], ENT_QUOTES | ENT_HTML401)
            : htmlentities($htmlPost['new_login'], ENT_QUOTES | ENT_HTML401);
    $data['nom'] = htmlentities($htmlPost['new_nom'], ENT_QUOTES | ENT_HTML401);
    $data['prenom'] = htmlentities($htmlPost['new_prenom'], ENT_QUOTES | ENT_HTML401);
    $data['quotite'] = (int) $htmlPost['new_quotite'];
    $data['soldeHeure'] = htmlentities($htmlPost['new_solde_heure'], ENT_QUOTES | ENT_HTML401);
    $data['isActive'] = 'N' === $htmlPost['new_is_active'] ? 'N' : 'Y';
    $data['isResp'] = 'Y' === $htmlPost['new_is_resp'] ? 'Y' : 'N';
    $data['isAdmin'] = 'Y' === $htmlPost['new_is_admin'] ? 'Y' : 'N';
    $data['isHR'] = 'Y' === $htmlPost['new_is_hr'] ? 'Y' : 'N';

    if (!$config->isUsersExportFromLdap()) {
        $data['email'] = htmlentities($htmlPost['new_email'], ENT_QUOTES | ENT_HTML401);
    } else {
        $injectableCreator = new \App\Libraries\InjectableCreator($sql, $config);
        $ldap = $injectableCreator->get(\App\Libraries\Ldap::class);
        $data['email'] = $ldap->getEmailUser($data['login']);
    }

    if ('dbconges' === $config->getHowToConnectUser()) {
        $data['pwd1'] = '' !== $htmlPost['new_password1'] ? md5($htmlPost['new_password1']) : "";
        $data['pwd2'] = '' !== $htmlPost['new_password2'] ? md5($htmlPost['new_password2']) : "";
    } else {
        $data['pwd1'] = md5(uniqid('', true));
        $data['pwd2'] = $data['pwd1'];
    }

    if (array_key_exists('_METHOD', $htmlPost)) {
        $data['_METHOD'] = htmlentities($htmlPost['_METHOD'], ENT_QUOTES | ENT_HTML401);
    }

    foreach ($htmlPost['tab_new_jours_an'] as $typeId => $joursAn) {
        $tmp = htmlentities($joursAn, ENT_QUOTES | ENT_HTML401);
        $data['joursAn'][$typeId] = strtr((string) \App\Helpers\Formatter::roundToHalf($tmp), ",", ".");
    }
    foreach ($htmlPost['tab_new_solde'] as $typeId => $solde) {
        $tmp = htmlentities($solde, ENT_QUOTES | ENT_HTML401);
        $data['soldes'][$typeId] = strtr((string) \App\Helpers\Formatter::roundToHalf($tmp), ",", ".");
    }
    foreach ($htmlPost['tab_new_reliquat'] as $typeId => $reliquat) {
        $tmp = htmlentities($reliquat, ENT_QUOTES | ENT_HTML401);
        $data['reliquats'][$typeId] = strtr((string) \App\Helpers\Formatter::roundToHalf($tmp), ",", ".");
    }
    $data['groupesId'] = array_key_exists('checkbox_user_groups', $htmlPost) ? array_keys($htmlPost['checkbox_user_groups']) : [];

    return $data;
}

/**
 * Traite la creation d'un utilisateur
 *
 * @param array $post
 * @param array &$errors
 * @param string $notice
 *
 * @return int
 */
function postFormUtilisateur(array $post, array &$errors, string &$notice) : bool
{
    $return = false;
    if (!\App\ProtoControllers\Utilisateur::isRH($_SESSION['userlogin'])) {
        $errors[] = _('non autorisé');
        return $return;
    }

    $return = insertUtilisateur($post, $errors);
    if ($return) {
        $notice = "inserted";
        log_action(0, '', $post['login'], 'utilisateur ' . $post['login'] . ' ajouté');
    }
    return $return;
}

/**
 * Création d'un nouvel utilisateur
 *
 * @param array $data
 * @param array $errors
 * @return boolean
 */
function insertUtilisateur(array $data, array &$errors) : bool
{
    $sql = \includes\SQL::singleton();
    $config = new \App\Libraries\Configuration($sql);
    if (!isFormInsertValide($data, $errors, $sql, $config)) {
        return false;
    }

    $sql->getPdoObj()->begin_transaction();
    $insertInfos = insertInfosUtilisateur($data, $sql);
    $insertSoldes = insertSoldeUtilisateur($data, $sql);
    $insertGroupes = true;
    if (!empty($data['groupesId'])) {
        $insertGroupes = insertGroupesUtilisateur($data, $sql);
    }
    if ($insertInfos && $insertSoldes && $insertGroupes) {
        return $sql->getPdoObj()->commit();
    }

    $sql->getPdoObj()->rollback();
    return false;
}

function insertInfosUtilisateur(array $data, \includes\SQL $sql)
{
    $req = "INSERT INTO conges_users SET
                u_login='" . $data['login'] . "',
                u_nom='" . $data['nom'] . "',
                u_prenom='" . $data['prenom'] . "',
                u_is_resp='" . $data['isResp'] . "',
                u_is_admin='" . $data['isAdmin'] . "',
                planning_id = 0,
                u_is_hr='" . $data['isHR'] . "',
                u_passwd='" . $data['pwd1'] . "',
                u_quotite=" . $data['quotite'] . ",
                u_email = '" . $data['email'] . "',
                u_heure_solde=" . \App\Helpers\Formatter::hour2Time($data['soldeHeure']) . ",
                date_inscription = '" . date('Y-m-d H:i') . "';";

    return $sql->query($req);
}

function insertSoldeUtilisateur(array $data, \includes\SQL $sql) : bool
{
    $config = new \App\Libraries\Configuration($sql);
    $typeAbsencesConges = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges');

    foreach ($typeAbsencesConges as $typeId => $info) {
        $valuesStd[] = "(DEFAULT, '" . $data['login'] . "' ,"
                            . $typeId . ", "
                            . $data['joursAn'][$typeId] . ", "
                            . $data['soldes'][$typeId] . ", "
                            . $data['reliquats'][$typeId] . ")" ;
    }
    $req = "INSERT INTO conges_solde_user (su_id, su_login, su_abs_id, su_nb_an, su_solde, su_reliquat) VALUES " . implode(",", $valuesStd);
    $returnStd = $sql->query($req);
    $returnExc = 1;
    if ($config->isCongesExceptionnelsActive()) {
        $typeAbsencesExceptionnels = \App\ProtoControllers\Conge::getTypesAbsences($sql, 'conges_exceptionnels');
        foreach ($typeAbsencesExceptionnels as $typeId => $info) {
            $valuesExc[] = "(DEFAULT, '" . $data['login'] . "' ,"
                                . $typeId . ", 0, "
                                . $data['soldes'][$typeId] . ", 0)" ;

        }
        $req = "INSERT INTO conges_solde_user (su_id, su_login, su_abs_id, su_nb_an, su_solde, su_reliquat) VALUES " . implode(",", $valuesExc);
        $returnExc = $sql->query($req);
    }

    return $returnStd && $returnExc;
}

function insertGroupesUtilisateur(array $data, \includes\SQL $sql)
{
    foreach ($data['groupesId'] as $gid) {
        $values[] = "(" . $gid . ", '" . $data['login'] . "')"  ;
    }
    $req = "INSERT INTO conges_groupe_users (gu_gid, gu_login) VALUES " . implode(",", $values);

    return $sql->query($req);
}

/**
 * Controle la conformité du formulaire de création
 *
 * @param array $data
 * @param array $errors
 * @param \includes\SQL $sql
 * @param \App\Libraries\Configuration $config
 *
 * @return boolean
 */
function isFormInsertValide(array $data, array &$errors, \includes\SQL $sql, \App\Libraries\Configuration $config) : bool
{
    $return = true;
    $users = \App\ProtoControllers\Utilisateur::getListId(false);
    if (in_array($data['login'], $users, true)) {
        $errors[] = _('Cet identifiant existe déja.');
        $return = false;
    }

    if ('dbconges' === $config->getHowToConnectUser()) {
        if ('' === $data['pwd1'] || 0!== strcmp($data['pwd1'], $data['pwd2'])) {
            $errors[] = _('Saisie du mot de passe incorrect');
            $return = false;
        }
    }

    return $return && isFormValide($data, $errors, $sql, $config);
}

/**
 * Controle la conformité du formulaire (création et mise à jour)
 *
 * @param array $data
 * @param array $errors
 * @param \includes\SQL $sql
 * @param \App\Libraries\Configuration $config
 * @return boolean
 */
function isFormValide(array $data, array &$errors, \includes\SQL $sql, \App\Libraries\Configuration $config) : bool
{
    $return = true;

    if (!preg_match('/^[@a-z.\d_-]{2,30}$/i', $data['login'])) {
        $errors[] = _('Identifiant incorrect.');
        $return = false;
    }

    if ('' === $data['nom']) {
        $errors[] = _('Veuillez saisir un nom');
        $return = false;
    }

    if ('' === $data['prenom']) {
        $errors[] = _('Veuillez saisir un prenom');
        $return = false;
    }

    if (0 >= $data['quotite'] || 100 < $data['quotite']) {
        $errors[] = _('quotité incorrect');
        $return = false;
    }

    if ($config->isHeuresAutorise()) {
        if (!\App\Helpers\Formatter::isHourFormat($data['soldeHeure'])) {
            $errors[] = _('Format du solde d\'heure incorrect');
            $return = false;
        }
    }

    if (!$config->isUsersExportFromLdap()) {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = _('Format de l\'adresse email incorrect');
            $return = false;
        }
    }

    foreach ($data['joursAn'] as $typeId => $joursAn) {
        if (!is_numeric($joursAn)) {
            $errors[] = _('nombre de jours par an incorrect');
            $return = false;
            break;
        }
    }

    foreach ($data['soldes'] as $typeId => $solde) {
        if (!is_numeric($solde)) {
            $errors[] = _('solde incorrect');
            $return = false;
            break;
        }
    }

    foreach ($data['reliquats'] as $typeId => $reliquat) {
        if (!is_numeric($reliquat)) {
            $errors[] = _('reliquat incorrect');
            $return = false;
            break;
        }
    }

    return $return;
}

require_once VIEW_PATH . 'HautResponsable/Employe/Ajout.php';
