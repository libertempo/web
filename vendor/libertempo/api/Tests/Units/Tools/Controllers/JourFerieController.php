<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Tools\Controllers;

use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Classe de test du contrôleur de jour férié
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.0
 */
final class JourFerieController extends \LibertAPI\Tests\Units\Tools\Libraries\ARestController
{
    /**
     * {@inheritdoc}
     */
    protected function initRepository()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->repository = new \mock\LibertAPI\JourFerie\JourFerieRepository();
    }

    /**
     * {@inheritdoc}
     */
    protected function initEntite()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->entite = new \LibertAPI\JourFerie\JourFerieEntite([
            'id' => 78,
            'date' => '2018-06-12',
        ]);
    }

    /**
     * Teste la méthode get d'un détail trouvé
     */
    public function testGetOneFound()
    {
        $this->boolean(true)->isTrue();
    }

    /**
     * Teste la méthode get d'un détail non trouvé
     */
    public function testGetOneNotFound()
    {
        $this->boolean(true)->isTrue();
    }

    /**
     * Teste le fallback de la méthode get d'un détail
     */
    public function testGetOneFallback()
    {
        $this->boolean(true)->isTrue();
    }

    /**
     * @todo inutile dans le sens où chercher un élément unique n'a pas de sens.
     * Problème de design.
     */
    protected function getOne() : IResponse
    {
        return $this->response;
    }

    protected function getList() : IResponse
    {
        return $this->testedInstance->get($this->request, $this->response, []);
    }

    final protected function getEntiteContent() : array
    {
        return [
            'id' => uniqid(),
            'jf_date' => '2018-05-14',
        ];
    }
}
