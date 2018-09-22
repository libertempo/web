<?php declare(strict_types = 1);
namespace LibertAPI\Groupe\Responsable;

use LibertAPI\Tools\Libraries\AEntite;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 */
class ResponsableRepository extends \LibertAPI\Tools\Libraries\ARepository
{
    /**
     * @inheritDoc
     */
    public function getOne($id) : AEntite
    {
        throw new \RuntimeException('#' . $id . ' is not a callable resource');
    }

    final protected function getEntiteClass() : string
    {
        return ResponsableEntite::class;
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
     *
     * @TODO : cette méthode le montre, ResponsableEntite n'est pas une entité, mais un value object.
     * L'id n'est donc pas nécessaire, et l'arbo habituelle est remise en cause
     */
    final protected function getStorage2Entite(array $dataStorage) : array
    {
        return [
            'id' => uniqid(),
            'groupeId' => $dataStorage['gr_gid'],
            'login' => $dataStorage['gr_login'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function postOne(array $data) : int
    {
        throw new \RuntimeException('Action is forbidden');
    }

    /**
     * @inheritDoc
     */
    public function putOne($id, array $data) : AEntite
    {
        throw new \RuntimeException('Action is forbidden');
    }

    /**
     * @inheritDoc
     */
    final protected function getEntite2Storage(AEntite $entite) : array
    {
        return [];
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
    public function deleteOne(int $id) : int
    {
        throw new \RuntimeException('Action is forbidden');
    }

    /**
     * @inheritDoc
     */
    final protected function setWhere(array $parametres)
    {
        if (array_key_exists('id', $parametres)) {
            $this->queryBuilder->andWhere('g_gid = :id');
            $this->queryBuilder->setParameter(':id', (int) $parametres['id']);
        }
    }

    /**
     * @inheritDoc
     */
    final protected function getTableName() : string
    {
        return 'conges_groupe_resp';
    }
}
