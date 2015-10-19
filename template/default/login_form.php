<?php

	include INCLUDE_PATH.'misc.class.php';

	$locales_browser = new HTTPLocale();
	$lang_browser = $locales_browser->language;
	$country_browser = $locales_browser->country;
	if($lang_browser && $country_browser)
		$lang_selected = $lang_browser."_".strtoupper($country_browser);

	
?>
<div id="background">
	<div><img src="<?php echo TEMPLATE_PATH . HEADER_LOGIN; ?>"/></div>
	
	<div id="login_form" class="ui-widget ui-widget-content ui-corner-all">
		<div class="ui-widget-header ui-corner-all ui-helper-clearfix">
			<span><?php echo _('login_fieldset');?></span>
		</div>
		<div class="ui-dialog-content ui-widget-content">
			<form method="post" action="<?php echo $PHP_SELF; ?>">
				<?php
					if ($return_url)
						echo '<input type="hidden" name="return_url" value="'.$return_url.'"/>';
				?>
				<div class="login">
					<label for="session_username"><?php echo _('divers_login_maj_1'); ?></label>
					<input type="text" id="session_username" name="session_username" size="32" maxlength="99"  value="<?php echo $session_username; ?>"/>
				</div>
				<div class="password">
					<label for="session_password"><?php echo _('password'); ?></label>
					<input type="password" id="session_password" name="session_password" size="32" maxlength="99"  value="<?php //echo $session_password; ?>"/>
				</div>
 				<div class="language">
 					<label for="lang"><?php echo _('langue'); ?></label>
 					<?php affiche_select_from_lang_directory('lang',$lang_selected); ?>
 				</div>
				<div>
					<button type="submit" class="submit"><?php echo _('form_submit'); ?></button>
				</div>
				<div class="php-conges_link"><?php echo '<a href="'.$config_url_site_web_php_conges.'/">PHP_CONGES v '.$config_php_conges_version.'</a>';?></div>
			</form>
		</div>
	</div>
	<style>
		#background{overflow: hidden; background:url('<?php echo TEMPLATE_PATH . IMG_INDEX; ?>') bottom right no-repeat; margin: auto; width: 1000px; height: 625px; margin-top: 10px;}
		
		#login_form {width: 550px; margin-top: 50px;}
		#login_form .ui-widget-header{padding: 5px;}
		#login_form form {padding: 5px;}
		#login_form form div{padding: 5px;}
		#login_form label{ width: 200px; float: left; text-align: left;}
		#login_form button {width: 200px; margin-top: 10px;}
		#login_form button span{padding: 0px;}
		#login_form .php-conges_link{text-align: right; margin-bottom: -5px; font-size: 10px;}
		
		#err_login_pass , #err_login_unknow{width: 550px; margin: 20px 0px 20px 0px; font-size: 1em;}
	</style>
	<script type="text/javascript">
		$('#login_form .submit').button();
	</script>
<?php

	if($erreur=='login_passwd_incorrect') {
		echo '<div class="ui-widget" id="err_login_pass">
				<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"> 
					<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span> 
					'. _('login_passwd_incorrect') .'</p>
				</div>
			</div>';
	}
	elseif($erreur=='login_non_connu') {
		echo '<div class="ui-widget" id="err_login_unknow">
				<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"> 
					<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span> 
					'. _('login_non_connu') .'</p>
				</div>
			</div>';
	}

?>
</div>
