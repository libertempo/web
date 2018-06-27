<?php declare(strict_types = 1);
namespace LibertAPI\JourFerie;

use LibertAPI\Tools\Libraries\AEntite;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.0
 *
 * Ne devrait être contacté que par le JourFerieController
 * Ne devrait contacter que le JourFerieEntite
 */
class JourFerieRepository extends \LibertAPI\Tools\Libraries\ARepository
{
    /**
     * @inheritDoc
     */
    public function getOne(int $id) : AEntite
    {
        throw new \RuntimeException('#' . $id . ' is not a callable resource');
    }

    final protected function getEntiteClass() : string
    {
        return JourFerieEntite::class;
    }

    /**
     * @inheritDoc
     */
    final protected function getParamsConsumer2Storage(array $paramsConsumer) : array
    {
        unset($paramsConsumer);
        return [];
    }

    /**
     * @inheritDoc
     * @TODO : cette méthode le montre, JourFerie n'est pas une entité, mais un value object.
     * L'id n'est donc pas nécessaire, et l'arbo habituelle est remise en cause
     */
    final protected function getStorage2Entite(array $dataStorage)
    {
        return [
            'id' => uniqid(),
            'date' => $dataStorage['jf_date'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function postOne(array $data, AEntite $entite) : int
    {
        throw new \RuntimeException('Action is forbidden');
    }

    /**
     * @inheritDoc
     */
    public function putOne(AEntite $entite)
    {
        throw new \RuntimeException('Action is forbidden');
    }

    /**
     * @inheritDoc
     */
    final protected function getEntite2Storage(AEntite $entite) : array
    {
        return [
            'jf_date' => $entite->getDate(),
        ];
    }

    /**
     * @inheritDoc
     */
    final protected function setValues(array $values)
    {
        unset($values);
    }

    /**
     * @inheritDoc
     */
    final protected function setSet(array $parametres)
    {
        unset($parametres);
    }

    /**
     * @inheritDoc
     */
    public function deleteOne(AEntite $entite) : int
    {
        throw new \RuntimeException('Action is forbidden');
    }

    /**
     * @inheritDoc
     */
    final protected function setWhere(array $parametres)
    {
        unset($parametres);
    }

    /**
     * @inheritDoc
     */
    final protected function getTableName() : string
    {
        return 'conges_jours_feries';
    }
}
