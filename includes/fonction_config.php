<?php

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

function affiche_bouton_retour()
{
	// Bouton de retour : différent suivant si on vient des pages d'install ou de l'appli
	// $_SESSION['from_config'] est initialisée dans install/index
	if( isset($_SESSION['from_config']) && $_SESSION['from_config'] )
		echo '<center><a href="'. ROOT_PATH .'config/">'. _('form_retour') .'</a></center>';
	else
	{
		echo '<form action="" method="POST">';
		echo '<center><input type="button" value="'. _('form_close_window') .'" onClick="window.close();"></center>';
		echo '</form>';
	}
}
