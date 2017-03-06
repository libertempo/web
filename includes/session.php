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
$session=(isset($_REQUEST['session']) ? $_REQUEST['session'] : '' );

if ($session != "") //  UNE SESSION EXISTE
{
	if(session_is_valid($session) )
		session_update($session);
	else {
		session_delete($session);
		$session="";
		$session_username="";
		$session_password="";
		$_SESSION['config']=init_config_tab();  // on recrée le tableau de config pour l'url du lien
		
		redirect(ROOT_PATH . 'index.php?error=session-invalid');
	}
}
else    //  PAS DE SESSION   ($session == "")
	redirect(ROOT_PATH . 'index.php');


