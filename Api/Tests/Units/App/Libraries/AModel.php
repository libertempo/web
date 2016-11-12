<?php
namespace Api\Tests\Units\App\Libraries;

/**
 * Classe commune de test sur les modèles
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
abstract class AModel extends \Atoum
{
    /**
     * Teste la méthode reset
     */
    abstract public function testReset();

    /**
     * Asserteurs communs sur le reset
     */
    final protected function assertReset(\Api\App\Libraries\AModel $model)
    {
        $model->reset();

        $this->variable($model->getId())->isNull();
    }
}
