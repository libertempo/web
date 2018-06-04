<?php declare(strict_types = 1);
namespace LibertAPI\JourFerie;

use LibertAPI\Tools\Exceptions\MissingArgumentException;

/**
 * @inheritDoc
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.0
 *
 * Ne devrait être contacté que par le JourFerieRepository
 * Ne devrait contacter personne
 */
class JourFerieEntite extends \LibertAPI\Tools\Libraries\AEntite
{
    /**
     * Retourne la donnée la plus à jour du champ date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->getFreshData('date');
    }

    /**
     * @inheritDoc
     */
    public function populate(array $data)
    {
    }
}
