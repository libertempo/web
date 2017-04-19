<?php
defined('_PHP_CONGES') or die('Restricted access');
$config = new \App\Libraries\Configuration();
if (!$config->isHeuresAutorise()) {
    redirect(ROOT_PATH . 'responsable/resp_index.php');
}
$repos = new \App\ProtoControllers\Responsable\Traitement\Repos();
echo $repos->getForm();
