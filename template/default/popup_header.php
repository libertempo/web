<?php
	
	defined( '_PHP_CONGES' ) or die( 'Restricted access' );
	include TEMPLATE_PATH . 'template_define.php';


echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\">\n";
echo "<html>\n";
	echo "<head>\n";
		echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
		echo "<title> ".$title." </TITLE>\n";
		echo "<link href=\"". TEMPLATE_PATH ."style.css\" rel=\"stylesheet\" type=\"text/css\" />";
		if (isset($_SESSION['config']['stylesheet_file']))
			echo "<link href=\"". TEMPLATE_PATH .$_SESSION['config']['stylesheet_file']."\" rel=\"stylesheet\" type=\"text/css\">\n";
		echo '<link type="text/css" href="'. TEMPLATE_PATH .'jquery/css/custom-theme/jquery-ui-1.8.17.custom.css" rel="stylesheet" />';
		echo '<script type="text/javascript" src="'. TEMPLATE_PATH .'jquery/js/jquery-1.7.1.min.js"></script>';
		echo '<script type="text/javascript" src="'. TEMPLATE_PATH .'jquery/js/jquery-ui-1.8.17.custom.min.js"></script>';
		include ROOT_PATH .'fonctions_javascript.php' ;
		echo $additional_head;
	echo "</head>\n";
	echo '<body>';
		echo '<center>';