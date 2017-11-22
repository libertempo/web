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
        $this->ds = \ldap_connect($_SESSION['config']['ldap_server']);
        if($_SESSION['config']['ldap_protocol_version'] != 0) {
            ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, $_SESSION['config']['ldap_protocol_version']) ;
            ldap_set_option($this->ds, LDAP_OPT_REFERRALS, 0);
        }
        if ($_SESSION['config']['ldap_user'] == "") {
            $bound = ldap_bind($this->ds);  // connexion anonyme au serveur
        } else {
            $bound = ldap_bind($this->ds, $_SESSION['config']['ldap_user'], $_SESSION['config']['ldap_pass']);
        }

    }

    public function searchLdap($search)
    {
        $nom = htmlentities($search, ENT_QUOTES | ENT_HTML401);;
        return json_encode($this->getInfosUser($nom));
    }

    public function getInfosUser($nom)
    {
        $data = [];
        $attributLogin = $_SESSION['config']['ldap_login'];
        $attributNom = $_SESSION['config']['ldap_nom'];
        $attributPrenom = $_SESSION['config']['ldap_prenom'];
        $filter = "(&(" . $_SESSION['config']['ldap_nomaff']."=" . $nom . "*)
                    (" . $_SESSION['config']['ldap_filtre'] . "=" . $_SESSION['config']['ldap_filrech'] . "))";

        $attributs = array($attributLogin, $attributNom, $attributPrenom);
        
        $sr   = ldap_search($this->ds, $_SESSION['config']['searchdn'], $filter, $attributs, 0, 10);
        $entries = ldap_get_entries($this->ds,$sr);

        if(0 < $entries['count']){
            for ($i=0; $i<$entries["count"]; $i++) {
                $data[$entries[$i][$attributLogin][0]] = [
                    'login' => $entries[$i][$attributLogin][0],
                    'nom' => $entries[$i][$attributNom][0],
                    'prenom' => $entries[$i][$attributPrenom][0],
                    ];
            }
        }
        return $data;
    }

    public function getEmailUser($login)
    {
        $attributLogin = $_SESSION['config']['ldap_login'];
        $attributEmail = $_SESSION['config']['email'];
        $filter = "(&(" . $attributLogin . "=" . $login . ")
                    (" . $_SESSION['config']['ldap_filtre'] . "=" . $_SESSION['config']['ldap_filrech'] . "))";

        $attributs = array($attributLogin, $attributEmail);
        
        $sr   = ldap_search($this->ds, $_SESSION['config']['searchdn'], $filter, $attributs, 0, 10);
        $entries = ldap_get_entries($this->ds,$sr);

        if(0 < $entries['count']){
            return $entries[0][$attributEmail][0];
        } else {
            return "";
        }
    }
}