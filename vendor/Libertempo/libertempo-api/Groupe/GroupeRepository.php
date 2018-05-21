<?php declare(strict_types = 1);
namespace LibertAPI\Groupe;

use LibertAPI\Tools\Libraries\AEntite;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.7
 * @see \LibertAPI\Tests\Units\Planning\PlanningRepository
 *
 * Ne devrait être contacté que par le GroupeController
 * Ne devrait contacter que le GroupeEntite, GroupeDao
 */
class GroupeRepository extends \LibertAPI\Tools\Libraries\ARepository
{
    /*************************************************
     * GET
     *************************************************/

    /**
     * @inheritDoc
     */
    final protected function getParamsConsumer2Dao(array $paramsConsumer) : array
    {
        unset($paramsConsumer);
        return [];
    }


    /*************************************************
     * DELETE
     *************************************************/

    /**
     * @inheritDoc
     */
    public function deleteOne(AEntite $entite)
    {
        try {
            $this->dao->delete($entite->getId());
            $entite->reset();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
