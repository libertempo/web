<?php
namespace LibertAPI\Tests\Units\Planning\Creneau;

/**
 * Classe de test du repository de créneau de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class CreneauRepository extends \LibertAPI\Tests\Units\Tools\Libraries\ARepository
{
    protected function initDao()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->dao = new \mock\LibertAPI\Planning\Creneau\CreneauDao();
    }

    protected function initEntite()
    {
        $this->entite = new \LibertAPI\Planning\Creneau\CreneauEntite(['id' => 42]);
    }

    /*************************************************
     * POST
     *************************************************/

    /**
     * Teste la méthode postList avec un champ manquant
     */
    public function testPostListException()
    {
        $repository = $this->newTestedInstance($this->dao);
        $entite = new \mock\LibertAPI\Planning\Creneau\CreneauEntite([]);
        $entite->getMockController()->populate = function () {
            throw new \LogicException('');
        };
        $data = [
            'planningId' => 34,
            'jourId' => 23,
            'typeSemaine' => 15,
            'typePeriode' => 57,
            'debut' => 83,
            'fin' => 92,
        ];

        $this->exception(function () use ($repository, $data, $entite) {
            $repository->postList([$data], $entite);
        })->isInstanceOf('\LogicException');
    }

    /**
     * Teste la méthode postList tout ok
     */
    public function testPostListOk()
    {
        $repository = $this->newTestedInstance($this->dao);
        $entite = new \mock\LibertAPI\Planning\Creneau\CreneauEntite([]);
        $entite->getMockController()->populate = '';
        $entite->getMockController()->getPlanningId = 3;
        $entite->getMockController()->getJourId = 4;
        $entite->getMockController()->getTypeSemaine = 5;
        $entite->getMockController()->getTypePeriode = 6;
        $entite->getMockController()->getDebut = 7;
        $entite->getMockController()->getFin = 8;
        $data = [
            [
                'planningId' => 34,
                'jourId' => 6,
                'typeSemaine' => 2,
                'typePeriode' => 1,
                'debut' => 13,
                'fin' => 2,
            ]
        ];
        $this->dao->getMockController()->post[1] = 3;
        $this->dao->getMockController()->post[2] = 9;


        $post = $repository->postList($data, $entite);

        foreach ($post as $postId) {
            $this->integer($postId);
        }
    }

    protected function getEntiteContent()
    {
        return [
            'id' => 42,
            'planningId' => 12,
            'jourId' => 7,
            'typeSemaine' => 23,
            'typePeriode' => 2,
            'debut' => 63,
            'fin' => 55,
        ];
    }

    /**
     * En train de faire la méthode put du dao du creneau (au passage il y a aura post à faire)
     */
}
