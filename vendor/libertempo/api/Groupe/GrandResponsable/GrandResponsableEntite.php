<?php declare(strict_types = 1);
namespace LibertAPI\Groupe\GrandResponsable;

/**
 * @inheritDoc
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.1
 *
 * Ne devrait être contacté que par GrandResponsableRepository
 * Ne devrait contacter personne
 */
class GrandResponsableEntite extends \LibertAPI\Tools\Libraries\AEntite
{
    public function getGroupeId() : int
    {
        return (int) $this->getFreshData('groupeId');
    }

    public function getLogin()
    {
        return $this->getFreshData('login');
    }

    /**
     * @inheritDoc
     */
    public function populate(array $data)
    {
    }
}
