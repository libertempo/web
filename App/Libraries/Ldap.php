<?php
namespace App\Libraries;

/**
 * gestion des requetes LDAP
 *
 * @since 1.11
 * @author Wouldsmina <wouldsmina@gmail.com>
 * @see Tests\Units\App\Libraries\Ldap
 *
 * Peut être contacté par tout ceux qui requierent un acces LDAP
 */
class Ldap
{
    private $configuration = [];

    public function __construct($confLdap) {
        $this->configuration['server'] = $confLdap['server'];
        $this->configuration['version'] = $confLdap['version'];
        $this->configuration['bindUser'] = "" == $confLdap['bindUser'] ? null : $confLdap['bindUser'];
        $this->configuration['bindPassword'] = "" == $confLdap['bindPassword'] ? null : $confLdap['bindPassword'];
        $this->configuration['searchdn'] = $confLdap['searchdn'];
        $this->configuration['attrNom'] = $confLdap['attrNom'];
        $this->configuration['attrPrenom'] = $confLdap['attrPrenom'];
        $this->configuration['attrLogin'] = $confLdap['attrLogin'];
        $this->configuration['attrNomAff'] = $confLdap['attrNomAff'];
        $this->configuration['attrMail'] = $confLdap['attrMail'];
        $this->configuration['attrFiltre'] = $confLdap['attrFiltre'];
        $this->configuration['filtre'] = $confLdap['filtre'];

        $this->configuration['ldapConn'] = \ldap_connect($this->configuration['server']);

        if ($this->configuration['version'] != 0) {
            ldap_set_option($this->configuration['ldapConn'], LDAP_OPT_PROTOCOL_VERSION, $this->configuration['version']);
            ldap_set_option($this->configuration['ldapConn'], LDAP_OPT_REFERRALS, 0);
        }

        if (!ldap_bind($this->configuration['ldapConn'], $this->configuration['bindUser'], $this->configuration['bindPassword'])) {
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
        $filter = "(&(" . $this->configuration['attrNomAff'] . "=" . $nom . "*)
                    (" . $this->configuration['attrFiltre'] . "=" . $this->configuration['filtre'] . "))";

        $attributs = [$this->configuration['attrLogin'], $this->configuration['attrNom'], $this->configuration['attrPrenom']];
        
        $searchResult = ldap_search($this->configuration['ldapConn'], $this->configuration['searchdn'], $filter, $attributs, 0, 10);
        $entries = ldap_get_entries($this->configuration['ldapConn'],$searchResult);

        for ($i=0; $i<$entries["count"]; $i++) {
            $data[] = [
                'login' => $entries[$i][$this->configuration['attrLogin']][0],
                'nom' => $entries[$i][$this->configuration['attrNom']][0],
                'prenom' => $entries[$i][$this->configuration['attrPrenom']][0],
                ];
        }
        return $data;
    }

    public function getEmailUser($login)
    {
        $filter = "(&(" . $this->configuration['attrLogin'] . "=" . $login . ")
                    (" . $this->configuration['attrFiltre'] . "=" . $this->configuration['filtre'] . "))";

        $attributs = [$this->configuration['attrLogin'], $this->configuration['attrMail']];
        
        $searchResult = ldap_search($this->configuration['ldapConn'], $this->configuration['searchdn'], $filter, $attributs, 0, 1);
        $entries = ldap_get_entries($this->configuration['ldapConn'],$searchResult);

        if (0 < $entries['count'] && array_key_exists($this->configuration['attrMail'], $entries[0])) {
            return $entries[0][$this->configuration['attrMail']][0];
        }

        return "";
    }
}