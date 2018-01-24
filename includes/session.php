<?php
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

//
// MAIN
//

/*** initialisation des variables ***/
$session_username="";
$session_password="";
/************************************/

//
// recup du num  de session (mais on ne sais pas s'il est passé en GET ou POST
$session='';

if (session_id() == '' || !isset($_SESSION)) {
    redirect(ROOT_PATH . 'index.php');
} else {
    if(session_is_valid())
    session_update($session);
    else {
        session_delete();
        $session_username="";
        $session_password="";
        $_SESSION['config']=init_config_tab();  // on recrée le tableau de config pour l'url du lien

        redirect(ROOT_PATH . 'index.php?error=session-invalid');
    }
}
