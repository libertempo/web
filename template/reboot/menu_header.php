<?php
    defined( '_PHP_CONGES' ) or die( 'Restricted access' );
    include TEMPLATE_PATH . 'template_define.php';
    $printable = getpost_variable('printable');
    if (is_admin($_SESSION['userlogin'])) {
        $home = 'admin/admin_index.php';
    } elseif (is_hr($_SESSION['userlogin'])) {
        $home = 'hr/hr_index.php';
    } elseif (is_resp($_SESSION['userlogin'])) {
        $home = 'responsable/resp_index.php';
    } else {
        $home = 'utilisateur/user_index.php';
    }
    //user mode
    $user_mode = '';
    $tmp = dirname(filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL));
    $tmp = explode('/',$tmp);
    $tmp = array_pop($tmp);
    $adminActive = $userActive = $respActive = $hrActive = $calendarActive = '';
    switch ($tmp) {
        case "utilisateur":
            $user_mode = _('user');
            $userActive = 'active';
            break;
        case "admin":
        case "config":
            $user_mode = _('button_admin_mode');
            $adminActive = 'active';
            break;
        case "responsable":
            $user_mode = _('button_responsable_mode');
            $respActive = 'active';
            break;
        case "hr":
            $hrActive = _('button_hr_mode');
            $hrActive = 'active';
            break;
        default :
            $calendarActive = _('button_calendar');
            $calendarActive = 'active';
    }
    $onglet = getpost_variable('onglet');
    // toolbar contextuelle au mode
    $mod_toolbar = [];

    switch($tmp) {
        case 'admin':
            $mod_toolbar[] = '';
            if($_SESSION['config']['affiche_bouton_config_pour_admin'] || $_SESSION['config']['affiche_bouton_config_absence_pour_admin'] || $_SESSION['config']['affiche_bouton_config_mail_pour_admin'] || $_SESSION['userlogin']=="admin" )
                $mod_toolbar[] = "";
        break;
        case 'hr':
            $mod_toolbar[] = "<a href=\"" . ROOT_PATH . "hr/hr_jours_fermeture.php\"><i class=\"fa fa-calendar\"></i><span>" . _('admin_button_jours_fermeture_2') . "</span></a>";
        break;
        case 'utilisateur':
            $mod_toolbar[] = '<a href="#"
            onClick="OpenPopUp(\'' . ROOT_PATH . 'export/export_vcalendar.php?user_login=' . $_SESSION['userlogin'] .
            '\', \'\', 600, 400);return false;">
            <i class="fa fa-download"></i><span>' . _('Exporter cal') . '</span></a>';
            if($_SESSION['config']['editions_papier'])
                $mod_toolbar[] = "<a href=\"" . ROOT_PATH . "edition/edit_user.php\"><i class=\"fa fa-file-text\"></i><span>"._('button_editions')."</span></a>";
        break;
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?= $title; ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php /* FAVICONS */ ?>
        <link rel="apple-touch-icon" href="<?= IMG_PATH ?>Favicons/apple-touch-icon.png">
        <link rel="apple-touch-icon" sizes="57x57" href="<?= IMG_PATH ?>Favicons/apple-touch-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="<?= IMG_PATH ?>Favicons/apple-touch-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="<?= IMG_PATH ?>Favicons/apple-touch-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="<?= IMG_PATH ?>Favicons/apple-touch-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="<?= IMG_PATH ?>Favicons/apple-touch-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="<?= IMG_PATH ?>Favicons/apple-touch-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="<?= IMG_PATH ?>Favicons/apple-touch-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="<?= IMG_PATH ?>Favicons/apple-touch-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="<?= IMG_PATH ?>Favicons/apple-touch-icon-180x180.png">
        <link rel="icon" type="image/png" sizes="16x16" href="<?= IMG_PATH ?>Favicons/favicon-16x16.png">
        <link rel="icon" type="image/png" sizes="32x32" href="<?= IMG_PATH ?>Favicons/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="<?= IMG_PATH ?>Favicons/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= IMG_PATH ?>Favicons/android-chrome-192x192.png">
        <link rel="manifest" href="<?= IMG_PATH ?>Favicons/manifest.json">
        <link rel="mask-icon" href="<?= IMG_PATH ?>Favicons/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#da532c">
        <meta name="msapplication-TileImage" content="<?= IMG_PATH ?>Favicons/mstile-144x144.png">
        <meta name="theme-color" content="#ffffff">
        <?php /* BOOTSTRAP */?>
        <link type="text/css" href="<?= ASSETS_PATH ?>bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen,print">
        <link type="text/css" href="<?= CSS_PATH ?>datepicker.css" rel="stylesheet" media="screen">
        <link type="text/css" href="<?= ASSETS_PATH ?>bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" media="screen" />
        <?php /* FONT AWESOME */ ?>
        <link href="<?= ASSETS_PATH ?>font-awesome/css/font-awesome.css" rel="stylesheet">
        <?php /* REBOOT STYLE */ ?>
        <link type="text/css" href="<?= CSS_PATH ?>reboot.css" rel="stylesheet" media="screen,print">
        <?php /* JQUERY */ ?>
        <script type="text/javascript" src="<?= ASSETS_PATH ?>jquery/js/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="<?= ASSETS_PATH  ?>bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?= ASSETS_PATH ?>bootstrap-datepicker/bootstrap-datepicker.js"></script>
        <script type="text/javascript" src="<?= ASSETS_PATH ?>bootstrap-datepicker/locales/bootstrap-datepicker.fr.js"></script>
        <script type="text/javascript" src="<?= ASSETS_PATH ?>bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
        <script type="text/javascript" src="<?= JS_PATH ?>reboot.js"></script>
        <?= $additional_head ?>
    </head>
    <body id="top" class="hbox connected <?= ($printable) ? 'printable' : '' ?>">
        <aside id="toolbar">
            <header class="main-header">
                <i class="icon-ellipsis-vertical toolbar-toggle"></i>
                <div class="brand"><a href="<?= ROOT_PATH . $home ?>" title="Accueil"><img src="<?= IMG_PATH ?>Libertempo64.png" alt="Libertempo"></a></div>
            </header>
            <div class="tools">
                <div class="profil-info">
                    <i class="fa fa-smile-o"></i>
                    <?= $_SESSION['u_prenom'] ?> <?= $_SESSION['u_nom'] ?>
                </div>
				<?php if (is_admin($_SESSION['userlogin'])): ?>
                <a class="primary <?= $adminActive ?>" href="<?= ROOT_PATH ?>admin/admin_index.php" <?php print ($tmp == 'admin' || $tmp == 'config') ? 'active' : '' ;?>>
                    <i class="fa fa-bolt"></i><?= _('button_admin_mode');?>
				</a>
                <?php if (($tmp == 'admin' || $tmp == 'config')) : ?>
                <a class="secondary" href="<?= ROOT_PATH ?>admin/admin_index.php?onglet=admin-users">Utilisateurs</a>
                <a class="secondary" href="<?= ROOT_PATH ?>admin/admin_index.php?onglet=admin-group">Groupes</a>
                <a class="secondary" href="<?= ROOT_PATH ?>admin/admin_db_sauve.php">Backup</a>
                <a class="secondary" href="<?= ROOT_PATH ?>config/index.php">Configuration</a>
                <?php endif; ?>
				<?php endif; ?>
				<?php if (is_hr($_SESSION['userlogin'])): ?>
                <a class="primary <?= $hrActive ?>" title="<?= _('button_hr_mode');?>" href="<?= ROOT_PATH ?>hr/hr_index.php" <?php print ($tmp == 'hr') ? 'active' : '' ;?>>
                    <i class="fa fa-sitemap"></i>
				</a>
				<?php endif; ?>
				<?php if (is_resp($_SESSION['userlogin'])): ?>
                <a class="primary <?= $respActive ?>" title="<?= _('button_responsable_mode');?>" href="<?= ROOT_PATH ?>responsable/resp_index.php" <?php print ($tmp == 'utilisateur') ? 'active' : '' ;?>>
                    <i class="fa fa-users"></i>
				</a>
				<?php endif; ?>
                <a class="primary <?= $userActive ?>" href="<?= ROOT_PATH ?>utilisateur/user_index.php" <?php print ($tmp == 'utilisateur') ? 'active' : '' ;?>>
                    <i class="fa fa-user"></i><?= _('user') ?>
                </a>
                <?php if('active' === $calendarActive || $tmp=='utilisateur' || $tmp=='responsable' || in_array($tmp, ['hr', 'admin', 'config'])): ?>
                <a class="primary <?= $calendarActive ?>" href="<?= ROOT_PATH ?>calendrier.php">
                    <i class="fa fa-calendar"></i><?= _('button_calendar') ?>
                </a>
                <?php endif; ?>
               <?php if($_SESSION['config']['auth']): ?>
                <a class="primary" href="<?= ROOT_PATH ?>deconnexion.php">
                    <i class="fa fa-power-off"></i><?= _('button_deconnect') ?>
                </a>
                <?php endif; ?>
            </div>
        </aside>
        <section id="content">
            <section class="vbox">
                <header class="header bg-white">
                <?php if($mod_toolbar) : ?>
                    <ul id="mod-toolbar" class="pull-right">
                    <?php foreach ($mod_toolbar as $key => $link) : ?>
                        <li><?php echo $link; ?></li>
                    <?php endforeach;?>
                    </ul>
                <?php endif; ?>
                </header>
                <section id="scrollable">
                    <div class="wrapper bg-white">
