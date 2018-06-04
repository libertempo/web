<?php declare(strict_types = 1);
namespace LibertAPI\Journal;

use LibertAPI\Tools\Libraries\AEntite;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 * @see \LibertAPI\Tests\Units\Journal\JournalRepository
 */
class JournalRepository extends \LibertAPI\Tools\Libraries\ARepository
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
        return JournalEntite::class;
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
     */
    final protected function getStorage2Entite(array $dataStorage)
    {
        return [
            'id' => $dataStorage['log_id'],
            'numeroPeriode' => $dataStorage['log_p_num'],
            'utilisateurActeur' => $dataStorage['log_user_login_par'],
            'utilisateurObjet' => $dataStorage['log_user_login_pour'],
            'etat' => $dataStorage['log_etat'],
            'commentaire' => $dataStorage['log_comment'],
            'date' => $dataStorage['log_date'],
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
        throw new \RuntimeException('Action is forbidden');
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
        if (!empty($parametres['id'])) {
            $this->queryBuilder->andWhere('log_id = :id');
            $this->queryBuilder->setParameter(':id', (int) $parametres['id']);
        }
    }

    /**
     * @inheritDoc
     */
    final protected function getTableName() : string
    {
        return 'conges_logs';
    }
}
