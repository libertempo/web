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
    private $ldapConn;
    private $server;
    private $version;
    private $bindUser;
    private $bindPassword;
    private $searchdn;
    private $attrPrenom;
    private $attrNom;
    private $attrMail;
    private $attrLogin;
    private $attrNomAff;
    private $attrFiltre;
    private $filtre;

    public function __construct($confLdap) {
        $this->server = $confLdap['server'];
        $this->version = $confLdap['version'];
        $this->bindUser = "" == $confLdap['bindUser'] ? null : $confLdap['bindUser'];
        $this->bindPassword = "" == $confLdap['bindPassword'] ? null : $confLdap['bindPassword'];
        $this->searchdn = $confLdap['server'];
        $this->attrNom = $confLdap['attrNom'];
        $this->attrPrenom = $confLdap['attrPrenom'];
        $this->attrLogin = $confLdap['attrLogin'];
        $this->attrNomAff = $confLdap['attrNomAff'];
        $this->attrMail = $confLdap['attrMail'];
        $this->attrFiltre = $confLdap['attrFiltre'];
        $this->filtre = $confLdap['filtre'];

        $this->ldapConn = \ldap_connect($this->server);

        if ($this->version != 0) {
            ldap_set_option($this->ldapConn, LDAP_OPT_PROTOCOL_VERSION, $this->version);
            ldap_set_option($this->ldapConn, LDAP_OPT_REFERRALS, 0);
        }

        if (!ldap_bind($this->ldapConn, $this->bindUser, $this->bindPassword)) {
          throw new \Exception(_('Erreur ldap'));
        }
    }

    public function searchLdap($search)
    {
        $nom = htmlentities($search, ENT_QUOTES | ENT_HTML401);
        return json_encode($this->getInfosUser($nom));
    }

    private function getInfosUser($nom)
    {
        $data = [];
        $filter = "(&(" . $this->attrNomAff . "=" . $nom . "*)
                    (" . $this->attrFiltre . "=" . $this->filtre . "))";

        $attributs = [$this->attrLogin, $this->attrNom, $this->attrPrenom];
        
        $searchResult = ldap_search($this->ldapConn, $this->searchdn, $filter, $attributs, 0, 10);
        $entries = ldap_get_entries($this->ldapConn,$searchResult);

        if (0 < $entries['count']) {
            for ($i=0; $i<$entries["count"]; $i++) {
                $data[] = [
                    'login' => $entries[$i][$this->attrLogin][0],
                    'nom' => $entries[$i][$this->attrNom][0],
                    'prenom' => $entries[$i][$this->attrPrenom][0],
                    ];
            }
        }
        return $data;
    }

    public function getEmailUser($login)
    {
        $filter = "(&(" . $this->attrLogin . "=" . $login . ")
                    (" . $this->attrFiltre . "=" . $this->filtre . "))";

        $attributs = [$this->attrLogin, $this->attrMail];
        
        $searchResult = ldap_search($this->ldapConn, $this->searchdn, $filter, $attributs, 0, 10);
        $entries = ldap_get_entries($this->ldapConn,$searchResult);

        if (0 < $entries['count']) {
            return $entries[0][$this->attrMail][0];
        }

        return "";
    }
}