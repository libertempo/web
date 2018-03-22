<?php
namespace LibertAPI\Tests\Units\Planning\Creneau;

use LibertAPI\Planning\Creneau\CreneauEntite;

/**
 * Classe de test du DAO de créneau de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class CreneauDao extends \LibertAPI\Tests\Units\Tools\Libraries\ADao
{
    /*************************************************
     * POST
     *************************************************/

    /**
     * Teste la méthode post quand tout est ok
     */
    public function testPostOk()
    {
        $this->connector->getMockController()->lastInsertId = 314;
        $dao = $this->newTestedInstance($this->connector);

        $postId = $dao->post(new CreneauEntite($this->entiteContent));

        $this->integer($postId)->isIdenticalTo(314);
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * Teste la méthode put quand tout est ok
     */
    public function testPutOk()
    {
        $dao = $this->newTestedInstance($this->connector);

        $put = $dao->put(new CreneauEntite($this->entiteContent));

        $this->variable($put)->isNull();
    }

    /*************************************************
     * DELETE
     *************************************************/

    /**
     * Teste la méthode delete quand tout est ok
     */
    public function testDeleteOk()
    {
        $this->calling($this->result)->rowCount = 1;
        $this->newTestedInstance($this->connector);

        $res = $this->testedInstance->delete(7);

        $this->variable($res)->isNull();
    }

    protected function getStorageContent()
    {
        return [
            'creneau_id' => 42,
            'planning_id' => 12,
            'jour_id' => 7,
            'type_semaine' => 23,
            'type_periode' => 2,
            'debut' => 63,
            'fin' => 55,
        ];
    }

    private $entiteContent = [
        'id' => 42,
        'planningId' => 12,
        'jourId' => 7,
        'typeSemaine' => 23,
        'typePeriode' => 2,
        'debut' => 63,
        'fin' => 55,
    ];
}
