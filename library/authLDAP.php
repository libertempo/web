<?php

/****************************************************************************/
/* */
/****************************************************************************/
class authLDAP
{
  var $searchdn;
  var $basedn;
  var $ldap_server;
  var $ldap_server_backup;
  var $ldap_user;
  var $ldap_pass;
  var $ldap_group;
  var $ldap_login;

  var $user_login = "";
  var $user_password = "";
  var $user_auth  = 0;

  // liste des groupes autorisés
  var $auth_dn_groups;
  // liste des persones autorisées
  var $auth_users;

  var $DEBUG = 0;

  function servers()
  {
 
	include CONFIG_PATH .'config_ldap.php';

    $this->searchdn[0]    = $config_searchdn;
    $this->basedn[0]      = $config_basedn;
    $this->ldap_server[0] = $config_ldap_server;
    $this->ldap_server_backup[0] = $config_ldap_bupsvr;
    $this->ldap_protocol_version[0] = $config_ldap_protocol_version;
    $this->ldap_user[0]   = $config_ldap_user;
    $this->ldap_pass[0]   = $config_ldap_pass;
    $this->ldap_login     = $config_ldap_login;


     if ($this->DEBUG) print "Auth par defaut";
  }

  function AuthLDAP($utils="",$searchdn=0,$basedn=0,$ldap_server=0,$ldap_user=0,$ldap_pass=0,$ldap_group=0)
  {


    if ( is_array($searchdn) && is_array($basedn) && is_array($ldap_server) && is_array($ldap_user) && is_array($ldap_pass) )
    {
      $this->searchdn           = $searchdn;
      $this->basedn             = $basedn;
      $this->ldap_server        = $ldap_server;
      $this->ldap_server_backup = $ldap_server_backup;
      $this->ldap_user          = $ldap_user;
      $this->ldap_pass          = $ldap_pass;
      $this->ldap_group         = $ldap_group;
      $this->ldap_login	        = $ldap_login;

      if ($this->DEBUG) print "Auth personnalisée ";
    }
    else //serveurs par défaut
    {
      $this->servers();
    }
    $this->auth_users($utils);
  }

  function auth_users($utils)
  {
    $this->auth_users = $utils;
  }


  function bind($login,$password)
  {
    $this->user_login    = $login;
    $this->user_password = $password;
    if ( !($this->auth_users) || (in_array($login,$this->auth_users)) )
      $this->ldap_query();
    else
      $this->user_auth=0;
  }

  function ldap_query()
  {

    $n = count($this->ldap_server);
    for ($i=0;$i<$n;$i++)
    {
      if ($this->DEBUG) print "<p><hr>SERVEUR $i = ".$this->ldap_server[$i]."<br>searchdn=".$this->searchdn[$i]."</p>";


      $ds = @ldap_connect($this->ldap_server[$i]);
      //essaie le 2eme seveur au cas ou le 1er est parterre
      if (!$ds )
          $ds = @ldap_connect($this->ldap_server_backup[$i]);

      if ( $ds  )
      {
		if($this->ldap_protocol_version[$i] != 0)
        	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $this->ldap_protocol_version[$i]) ;
		// Support Active Directory
		ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
        //$bound = @ldap_bind($ds, $this->ldap_user[$i], $this->ldap_pass[$i]);
  	 if ($this->ldap_user[$i] == "")
  	 	$bound = @ldap_bind($ds);
  	 else   $bound = @ldap_bind($ds, $this->ldap_user[$i], $this->ldap_pass[$i]);

  	if ( $bound )
  	{


          if  ( $this->ldap_group[$i] )
          {

            if ($this->DEBUG) print "<p>Filtre sur memberOf=" . $this->ldap_group[$i] ."</p>";

            $sr   = ldap_search($ds,$this->searchdn[$i],"(&(".$config_ldap_login."=".$this->user_login.")(memberof=" . $this->ldap_group[$i] ."))" );


          }
          else
          {
            $filtre = $this->ldap_login."=".$this->user_login;
            if ($this->DEBUG) print "filtre : $filtre";
            $sr   = ldap_search($ds,$this->searchdn[$i],$filtre   );
          }


          $info = ldap_get_entries($ds,$sr);
          if( isset($info['count']) && $info['count']>0)
            $dn=$info[0]["dn"];
          else 
            $dn=false;


          if ($this->DEBUG) print "<p>".$this->user_login." = $dn</p>";


          if ( $dn && !empty($this->user_password))
          {
            if (@ldap_bind($ds,$dn,$this->user_password))
	    {

              $this->user_auth = 1;

              if ($this->DEBUG) print " OUI ";
            }
            else
	    {

	      if ($this->DEBUG) print " NON ";

	    }
          }
          else
          {
            if ($this->DEBUG) print "<p>Utilisateur introuvable</p>";
          }
        }
        else
          if ($this->DEBUG) print "<p>serveur injoignable</p>";
      }

    }
  }

  function is_authentificated()
  {
    return $this->user_auth;
  }

}



?>
