<?php
namespace App\Libraries;

/**
 * gestion des requetes LDAP
 *
 * @since 1.11
 * @author Wouldsmina <wouldsmina@gmail.com.com>
 * @see Tests\Units\App\Libraries\Ldap
 *
 * Peut être contacté par tout ceux qui requierent un acces LDAP
 */
class Ldap
{
    private $ds;

    public function __construct() {
        $this->initLdapConn();
    }

    private function initLdapConn()
    {
        include CONFIG_PATH . 'config_ldap.php';
        $this->ds = \ldap_connect($config_ldap_server);
        if($config_ldap_protocol_version != 0) {
            ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, $config_ldap_protocol_version);
            ldap_set_option($this->ds, LDAP_OPT_REFERRALS, 0);
        }
        if ($config_ldap_user == "") {
            $bound = ldap_bind($this->ds);  // connexion anonyme au serveur
        } else {
            $bound = ldap_bind($this->ds, $config_ldap_user, $config_ldap_pass);
        }

    }

    public function searchLdap($search)
    {
        $nom = htmlentities($search, ENT_QUOTES | ENT_HTML401);;
        return json_encode($this->getInfosUser($nom));
    }

    public function getInfosUser($nom)
    {
        include CONFIG_PATH . 'config_ldap.php';
        $data = [];
        $filter = "(&(" . $config_ldap_nomaff . "=" . $nom . "*)
                    (" . $config_ldap_filtre . "=" . $config_ldap_filrech . "))";

        $attributs = array($config_ldap_login, $config_ldap_nom, $config_ldap_prenom);
        
        $sr   = ldap_search($this->ds, $config_searchdn, $filter, $attributs, 0, 10);
        $entries = ldap_get_entries($this->ds,$sr);

        if(0 < $entries['count']){
            for ($i=0; $i<$entries["count"]; $i++) {
                $data[] = [
                    'login' => $entries[$i][$config_ldap_login][0],
                    'nom' => $entries[$i][$config_ldap_nom][0],
                    'prenom' => $entries[$i][$config_ldap_prenom][0],
                    ];
            }
        }
        return $data;
    }

    public function getEmailUser($login)
    {
        include CONFIG_PATH . 'config_ldap.php';

        $filter = "(&(" . $config_ldap_login . "=" . $login . ")
                    (" . $config_ldap_filtre . "=" . $config_ldap_filrech . "))";

        $attributs = array($config_ldap_login, $config_ldap_mail);
        
        $sr   = ldap_search($this->ds, $config_searchdn, $filter, $attributs, 0, 10);
        $entries = ldap_get_entries($this->ds,$sr);

        if(0 < $entries['count']){
            return $entries[0][$config_ldap_mail][0];
        } else {
            return "";
        }
    }
}