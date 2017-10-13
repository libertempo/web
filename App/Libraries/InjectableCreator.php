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

    public function __construct(\includes\SQL $db)
    {
        $this->db = $db;
    }

    /**
     * @var \includes\SQL
     */
    private $db;

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
            case Evenements\Ferie::class:
            case Evenements\Fermeture::class:
            case Evenements\Conge::class:
            case Evenements\EchangeRtt::class:
            case Evenements\Heure\Additionnelle::class:
            case Evenements\Heure\Repos::class:
                return new $classname($this->db);
            case \App\Libraries\ApiClient::class:
                // TODO à supprimer quand on aura un vrai DI
                $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
                $baseURIApi = $config->getUrlAccueil() . '/api/';

                $client = new \GuzzleHttp\Client([
                    'base_uri' => $baseURIApi,
                ]);
                return new $classname($client);

            default:
                throw new \LogicException('Unknown « ' . $classname . ' »');
        }
    }
}
