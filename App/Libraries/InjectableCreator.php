<?php
namespace App\Libraries;

use App\Libraries\Calendrier\Evenements;

/**
 * Gestionnaire minimal d'injection de dépendances
 *
 * @since 1.10
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see Tests\Units\App\Libraries\InjectableCreator
 *
 * Peut être contacté par tout ceux qui requierent un injectable
 */
class InjectableCreator
{

    public function __construct(\includes\SQL $db, \App\Libraries\Configuration $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * @var \includes\SQL
     * @var \App\Libraries\Configuration
     */
    private $db;
    private $config;

    /**
     * Retourne un injectable bien construit (avec ses propres dépendances)
     *
     * @param string $classname
     *
     * @return object
     */
    public function get($classname)
    {
        if (!class_exists($classname) || 'App\\' !== substr($classname, 0, 4)) {
            throw new \LogicException('Class « ' . $classname . ' » loading is forbidden');
        }

        switch ($classname) {
            case Evenements\Weekend::class:
                return new $classname($this->config);
            case Evenements\Ferie::class:
            case Evenements\Fermeture::class:
            case Evenements\Conge::class:
            case Evenements\EchangeRtt::class:
            case Evenements\Heure\Additionnelle::class:
            case Evenements\Heure\Repos::class:
                return new $classname($this->db);
            case \App\Libraries\ApiClient::class:
                // TODO à supprimer quand on aura un vrai DI
                $config = new \App\Libraries\Configuration($this->db);
                $baseURIApi = $config->getUrlAccueil() . '/api/';

                $client = new \GuzzleHttp\Client([
                    'base_uri' => $baseURIApi,
                ]);
                return new $classname($client);
            case \App\Libraries\Ldap::class:
                include CONFIG_PATH . 'config_ldap.php';
                $confLdap = [
                    'server' => $config_ldap_server,
                    'version' => $config_ldap_protocol_version,
                    'bindUser' => $config_ldap_user,
                    'bindPassword' => $config_ldap_pass,
                    'searchdn' => $config_searchdn,
                    'attrPrenom' => $config_ldap_prenom,
                    'attrNom' => $config_ldap_nom,
                    'attrMail' => $config_ldap_mail,
                    'attrLogin' => $config_ldap_login,
                    'attrNomAff' => $config_ldap_nomaff,
                    'attrFiltre' => $config_ldap_filtre,
                    'filtre' => $config_ldap_filrech,
                    ];
                return new $classname($confLdap);
            default:
                throw new \LogicException('Unknown « ' . $classname . ' »');
        }
    }
}
