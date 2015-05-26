<?php
	include INCLUDE_PATH.'misc.class.php';

	$locales_browser = new HTTPLocale();
	$lang_browser = $locales_browser->language;
	$country_browser = $locales_browser->country;
	if($lang_browser && $country_browser)
		$lang_selected = $lang_browser."_".strtoupper($country_browser);

?>
<div class="container">
	<div class="form-signin">
		<?php
		// error
		$error_message = NULL;
		if ($erreur == 'login_passwd_incorrect') {
			$error_message = _('login_passwd_incorrect');
		}
		elseif ($erreur=='login_non_connu') {
			$error_message = _('login_non_connu');
		}
		if($error_message):
		?>
		<div class="alert alert-warning">
	 		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<?php echo $error_message; ?>
	 	</div>
		<?php endif; ?>

		<form method="post" action="<?php echo $PHP_SELF; ?>">
	    	<h2 class="form-signin-heading">Connexion</h2>
	        <input type="text" id="session_username" class="form-control" name="session_username" value="<?php echo $session_username; ?>" placeholder="<?php echo _('divers_login_maj_1'); ?>" autofocus>
	        <input type="password" id="session_password" class="form-control" name="session_password" placeholder="Mot de passe"/>
	        <?php affiche_select_from_lang_directory('lang',$lang_selected); ?>
	        <button type="submit" class="btn btn-lg btn-primary btn-block"><?php echo _('form_submit'); ?></button>
		</form>
		<script type="text/javascript">
			$('#login_form .submit').button();
		</script>
	</div>
</div> <!-- /container -->
