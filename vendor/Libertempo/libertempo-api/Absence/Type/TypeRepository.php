<?php declare(strict_types = 1);
namespace LibertAPI\Absence\Type;

use LibertAPI\Tools\Libraries\AEntite;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 * @see \Tests\Units\Absence\Type\TypeRepository
 */
class TypeRepository extends \LibertAPI\Tools\Libraries\ARepository
{
    /**
     * {@inheritDoc}
     */
    final protected function getParamsConsumer2Dao(array $paramsConsumer) : array
    {
        unset($paramsConsumer);
        return [];
    }

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
