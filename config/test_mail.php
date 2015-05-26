<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2005 (cedric chauvineau)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les
termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE,
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation
dans un but spécifique. Reportez-vous à la Licence Publique Générale GNU pour plus de détails.
Vous devez avoir reçu une copie de la Licence Publique Générale GNU en même temps
que ce programme ; si ce n'est pas le cas, écrivez à la Free Software Foundation,
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*************************************************************************************************
This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either
version 2 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*************************************************************************************************/

define('_PHP_CONGES', 1);
define('ROOT_PATH', '../');
include ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : "") ) ;

if (file_exists(CONFIG_PATH .'config_ldap.php'))
	include CONFIG_PATH .'config_ldap.php';
	
include ROOT_PATH .'fonctions_conges.php' ;
include INCLUDE_PATH .'fonction.php';
if(!isset($_SESSION['config']))
	$_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
include INCLUDE_PATH .'session.php';
$session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : "") ) ;


//$DEBUG = TRUE ;
$DEBUG = FALSE ;

// verif des droits du user à afficher la page
verif_droits_user($session, "is_admin", $DEBUG);



	/*** initialisation des variables ***/
	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET / POST
	$action = getpost_variable('action') ;
	$tab_new_values = getpost_variable('tab_new_values');

	/*************************************/
	if(!isset($tab_new_values['mail_to']))
		$tab_new_values['mail_to']="";
	if(!isset($tab_new_values['mail_to_2']))
		$tab_new_values['mail_to_2']="";
	if(!isset($tab_new_values['smtp_host_name']))
		$tab_new_values['smtp_host_name']="";
	if(!isset($tab_new_values['smtp_host_ip']))
		$tab_new_values['smtp_host_ip']="";

	if($DEBUG)
	{
		print_r($tab_new_values); echo "<br>\n";
		echo "$action<br>\n";
	}

	
	header_popup('CONGES : Configuration');
	
	
	echo "<center>";

	/*********************************/
	/*********************************/

	if(($action=="test") && ($tab_new_values['smtp_host_name']=="") && ($tab_new_values['smtp_host_ip']=="") )
		test_mail_direct($tab_new_values, $session, $DEBUG);
	elseif( ($action=="test") && ( ($tab_new_values['smtp_host_name']!="") || ($tab_new_values['smtp_host_ip']!="") ) )
		test_mail_smtp($tab_new_values, $session, $DEBUG);
	else
		affichage($tab_new_values, $session, $DEBUG);

	/*********************************/
	/*********************************/

	bottom();





/**************************************************************************************/
/**********  FONCTIONS  ***************************************************************/


function affichage($tab_new_values,  $session, $DEBUG=FALSE)
{
	$session=session_id();
	$PHP_SELF=$_SERVER['PHP_SELF'];

	if($session=="")
		$URL = "$PHP_SELF";
	else
		$URL = "$PHP_SELF?session=$session";

	/**************************************/
	// affichage du titre
	echo "<H1>Mail Test</H1>\n";
	echo "<br><br>\n";
	/**************************************/

	echo "<form action=\"\" method=\"POST\">\n";
	echo "<center><input type=\"button\" value=\"". _('form_close_window') ."\" onClick=\"javascript:window.close();\"></center>\n";
	echo "</form>\n";

	// affichage de la liste des type d'absence existants

	echo "    <form action=\"$URL\" method=\"POST\"> \n";
	echo "<table>\n";
	echo "<tr><td>\n";
	echo "    <table cellpadding=\"2\" class=\"tablo-config\" >\n";
	echo "    <tr>\n";
	echo "    	<td class=\"config\" valign=\"top\"><b>TO (1) :</b></td>\n";
	echo "    	<td class=\"config\"><input type=\"text\" size=\"80\" name=\"tab_new_values[mail_to]\" value=\"".$tab_new_values['mail_to']."\"></td>\n";
	echo "    </tr>\n";
	echo "    <tr>\n";
	echo "    	<td class=\"config\" valign=\"top\"><b>TO (2)(optional) :</b></td>\n";
	echo "    	<td class=\"config\"><input type=\"text\" size=\"80\" name=\"tab_new_values[mail_to_2]\" value=\"".$tab_new_values['mail_to_2']."\"></td>\n";
	echo "    </tr>\n";
	echo "    <tr>\n";
	echo "    	<td class=\"config\" valign=\"top\"><b>SMTP host name (optional) :</b></td>\n";
	echo "    	<td class=\"config\"><input type=\"text\" size=\"80\" name=\"tab_new_values[smtp_host_name]\" value=\"".$tab_new_values['smtp_host_name']."\"></td>\n";
	echo "    </tr>\n";
	echo "    <tr>\n";
	echo "    	<td class=\"config\" valign=\"top\"><b>SMTP IP address (optional) :</b></td>\n";
	echo "    	<td class=\"config\"><input type=\"text\" size=\"80\" name=\"tab_new_values[smtp_host_ip]\" value=\"".$tab_new_values['smtp_host_ip']."\"></td>\n";
	echo "    </tr>\n";

	echo "    </table>\n";
	echo "</td></tr>\n";
	echo "</table>\n";

	echo "    <input type=\"hidden\" name=\"action\" value=\"test\">\n";
	echo "    <input type=\"submit\"  value=\"Test Mail\"><br>\n";
	echo "    </form>\n";

	// Bouton de retour : différent suivant si on vient des pages d'install ou de l'appli
	echo "<br><br>\n";

	echo "<form action=\"\" method=\"POST\">\n";
	echo "<center><input type=\"button\" value=\"". _('form_close_window') ."\" onClick=\"javascript:window.close();\"></center>\n";
	echo "</form>\n";

}



function test_mail_direct($tab_new_values,  $session, $DEBUG=FALSE)
{
	$session=session_id();
	$PHP_SELF=$_SERVER['PHP_SELF'];

	$destination = $tab_new_values['mail_to'];
	$destination_2 = $tab_new_values['mail_to_2'];

	/*******************************************************************/
	error_reporting(E_ALL);
	echo "<b>Direct Mail Test </b><br><br>\n";

	echo "<b>MAIL:</b><br>From = from@example.com<br>To = $destination, $destination_2<br><br>\n";

	// preparation du test de mail
	require( LIBRARY_PATH .'phpmailer/class.phpmailer.php');

	$mail = new PHPMailer();

	$mail->SetLanguage("fr",  LIBRARY_PATH ."phpmailer/language/");

	$mail->From = "from@example.com";
	$mail->FromName = "PHP_CONGES";
	$mail->AddAddress($destination);
	if($destination_2!="")
		$mail->AddAddress($destination_2);
	//$mail->AddAddress("ellen@example.com");                  // name is optional
	//$mail->AddReplyTo("info@example.com", "Information");

	$mail->WordWrap = 50;                                 // set word wrap to 50 characters
	//$mail->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
	//$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
	//$mail->IsHTML(true);                                  // set email format to HTML

	$mail->Subject = "test phpmailer / php_conges";
	//$mail->Body    = "This is the HTML message body <b>in bold!</b>";
	//$mail->AltBody = "This is the body in plain text for non-HTML mail clients";
	$mail->Body    = "This is the body in plain text for non-HTML mail clients";

	// test envoie du mail
	echo "<b>Mail Test :</b><br>\n";

	echo "<b>send message using PHP mail() function :</b><br>\n";
	$mail->IsMail();   // send message using PHP mail() function
	if(!$mail->Send())
	{
	   echo "Message could not be sent. <p>";
	   echo "Mailer Error: " . $mail->ErrorInfo."<br><br>";
	}
	else
		echo "Message has been sent.<br><br>\n";


	echo "<b>send message using the Sendmail program :</b><br>\n";
	if(!file_exists("/usr/sbin/sendmail"))
			echo "/usr/sbin/sendmail doesn't exist.<br><br>\n";
	else
	{
		$mail->IsSendmail();   // send message using the $Sendmail program
		if(!$mail->Send())
		{
		   echo "Message could not be sent. <p>";
		   echo "Mailer Error: " . $mail->ErrorInfo."<br><br>";
		}
		else
			echo "Message has been sent.<br><br>\n";
	}

	echo "<b>send message using the qmail MTA :</b><br>\n";
	if(!file_exists("/var/qmail/bin/sendmail"))
			echo "/var/qmail/bin/sendmail doesn't exist.<br><br>\n";
	else
	{
		$mail->IsQmail();   // send message using the qmail MTA
		if(!$mail->Send())
		{
		   echo "Message could not be sent. <p>";
		   echo "Mailer Error: " . $mail->ErrorInfo."<br><br>";
		}
		else
			echo "Message has been sent.<br><br>\n";
	}

	echo "<br><br><a href=\"$PHP_SELF?session=$session\" method=\"POST\">". _('form_retour') ."</a><br>\n" ;

}



function test_mail_smtp($tab_new_values,  $session, $DEBUG=FALSE)
{
	$session=session_id();
	$PHP_SELF=$_SERVER['PHP_SELF'];

	$destination = $tab_new_values['mail_to'];
	$destination_2 = $tab_new_values['mail_to_2'];
	$SMTP    = $tab_new_values['smtp_host_name'];
	$SMTP_IP = $tab_new_values['smtp_host_ip'];

	/*******************************************************************/
	error_reporting(E_ALL);
	echo "<b> SMTP Mail Test</b><br><br>\n";

	echo "<b>MAIL:</b><br>SMTP = $SMTP / $SMTP_IP<br>From = from@example.com<br>To = $destination, $destination_2<br><br>\n";

	if($SMTP!="")
	{
		echo "<b>name resolution test : </b><br>\n";
		$ip = gethostbyname($SMTP);

		if ($ip == $SMTP)  // si erreur
		{
			echo "name resolution FAILED for $SMTP<br>\n";
			echo "test abandonné ! / test aborted !<br>\n";
			echo "vérifiez le nom de serveur smtp ou donnez l'adresse IP / check smtp server's name or try with IP address !<br>\n";
			$SMTP="";
		}
		else
		{
			echo "name resolution OK : $SMTP ==> $ip<br>\n";
		}
	}

	// preparation du test de mail
	require( LIBRARY_PATH .'phpmailer/class.phpmailer.php');

	$mail = new PHPMailer();

	$mail->SetLanguage("fr",  LIBRARY_PATH ."phpmailer/language/");

	$mail->From = "from@example.com";
	$mail->FromName = "PHP_CONGES";
	$mail->AddAddress($destination);
	if($destination_2!="")
		$mail->AddAddress($destination_2);
	//$mail->AddAddress("ellen@example.com");                  // name is optional
	//$mail->AddReplyTo("info@example.com", "Information");

	$mail->WordWrap = 50;                                 // set word wrap to 50 characters
	//$mail->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
	//$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
	$mail->IsHTML(true);                                  // set email format to HTML

	$mail->Subject = "test phpmailer pour php_conges";
	//$mail->Body    = "This is the HTML message body <b>in bold!</b>";
	//$mail->AltBody = "This is the body in plain text for non-HTML mail clients";
	$mail->Body    = "This is the body in plain text for non-HTML mail clients";

	// test avec hostname du serveur smtp
	if($SMTP!="")
	{
		echo "<b>Mail Test with HostName : </b><br>\n";

		$mail->IsSMTP();                                      // set mailer to use SMTP
		//$mail->Host = "mailhub1.univ-montp2.fr;mailhub2.univ-montp2.fr";  // specify main and backup server
		$mail->Host = $SMTP ;  // specify main and backup server
		//$mail->SMTPAuth = true;     // turn on SMTP authentication
		//$mail->Username = "jswan";  // SMTP username
		//$mail->Password = "secret"; // SMTP password

		if(!$mail->Send())
		{
		   echo "Message could not be sent. <br>";
		   echo "Mailer Error: " . $mail->ErrorInfo."<br><br>";
		}
		else
			echo "Message has been sent<br>\n";
	}

	// test avec adr IP du serevur smtp
	if($SMTP_IP!="")
	{
		echo "<br><b>Mail Test with IP Address : </b><br>\n";

		$mail->IsSMTP();                                      // set mailer to use SMTP
		//$mail->Host = "mailhub1.univ-montp2.fr;mailhub2.univ-montp2.fr";  // specify main and backup server
		$mail->Host = $SMTP_IP ;  // specify main and backup server
		//$mail->SMTPAuth = true;     // turn on SMTP authentication
		//$mail->Username = "jswan";  // SMTP username
		//$mail->Password = "secret"; // SMTP password

		if(!$mail->Send())
		{
		   echo "Message could not be sent. <br>";
		   echo "Mailer Error: " . $mail->ErrorInfo."<br><br>";
		}
		else
			echo "Message has been sent<br>\n";
	}

	echo "<br><br><a href=\"$PHP_SELF?session=$session\" method=\"POST\">". _('form_retour') ."</a><br>\n" ;

}


