<?php declare(strict_types = 1);
global $environnement;
require ROOT_PATH . 'version.php';
// ddd(ROOT_PATH, is_file(ROOT_PATH . 'version.php'));
?>
<script>
var _rollbarConfig = {
    accessToken: "<?= LOGGER_TOKEN ?>",
    captureUncaught: true,
    captureUnhandledRejections: true,
    payload: {
        environment: "<?= $environnement ?>",
        code_version : "<?= $config_php_conges_version ?>"
    }
};
</script>
<script type="text/javascript" src="<?= JS_PATH ?>rollbar.js?v=<?= $config_php_conges_version ?>"></script>
<script type="text/javascript" src="<?= NODE_PATH ?>jquery/dist/jquery.min.js?v=<?= $config_php_conges_version ?>"></script>
<script type="text/javascript" src="<?= NODE_PATH ?>bootstrap/dist/js/bootstrap.min.js?v=<?= $config_php_conges_version ?>"></script>
<script type="text/javascript" src="<?= NODE_PATH ?>bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js?v=<?= $config_php_conges_version ?>"></script>
<script type="text/javascript" src="<?= NODE_PATH ?>bootstrap-datepicker/dist/locales/bootstrap-datepicker.fr.min.js?v=<?= $config_php_conges_version ?>"></script>
<script type="text/javascript" src="<?= NODE_PATH ?>bootstrap-timepicker/js/bootstrap-timepicker.min.js?v=<?= $config_php_conges_version ?>"></script>
<?php if ('development' === $environnement) : ?>
    <script type="text/javascript" src="<?= NODE_PATH ?>vue/dist/vue.js?v=<?= $config_php_conges_version ?>"></script>
<?php else : ?>
    <script type="text/javascript" src="<?= NODE_PATH ?>vue/dist/vue.min.js?v=<?= $config_php_conges_version ?>"></script>
<?php endif ;?>
<script type="text/javascript" src="<?= JS_PATH ?>reboot.js?v=<?= $config_php_conges_version ?>"></script>
<script language="JavaScript" type="text/javascript">
<!--
// Les cookies sont obligatoires
if (! navigator.cookieEnabled) {
    document.write("<font color=\'#FF0000\'><br><br><center>'. _('cookies_obligatoires') .'</center></font><br><br>");
}

if (undefined !== optionsVue) {
    optionsVue.axios = instance;
    var vm = new Vue(optionsVue);
}

//-->
</script>
<noscript>
        <font color="#FF0000"><br><br><center>'. _('javascript_obligatoires') .'</center></font><br><br>
</noscript>
</body>
</html>
