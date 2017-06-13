<?php
namespace Tests\Units\App\Libraries;

use App\Libraries\AControllerFactory as _AControllerFactory;
use App\Libraries\AController as _AController;

/**
 * Test de la fabrication de contrôleurs
 *
 * @since 0.2
 */
final class AControllerFactory extends \Atoum
{
    /**
     * @var \mock\PDO Connecteur BD
     */
    private $storageConnector;

    /**
     * @var \mock\PDOStatement Mock du curseur de résultat PDO
     */
    private $statement;

    /**
     * @var \mock\Slim\Slim\Router Mock du routeur
     */
    private $router;

    /**
     * Init des tests
     */
    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->statement = new \mock\PDOStatement();
        $this->statement->getMockController()->fetchAll = [];
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->mockGenerator->orphanize('__construct');
        $this->storageConnector = new \mock\PDO();
        $this->storageConnector->getMockController()->query = $this->statement;
        $this->router = new \mock\Slim\Router();
    }

    /**
     * Test de la création de contrôleur pour une ressource inconnue
     */
    public function testCreateControllerNotFound()
    {
        $this->exception(function () {
            _AControllerFactory::createController('notFoundNs', $this->storageConnector, $this->router);
        })->isInstanceOf('\DomainException');
    }

    /**
     * Test de la création de contrôleur pour l'authentification
     */
    public function testCreateControllerAuthentification()
    {
        $controller = _AControllerFactory::createController('Authentification', $this->storageConnector, $this->router);

        $this->object($controller)->isInstanceOf(\App\Components\Authentification\Controller::class);
    }

    /**
     * Test de la création de contrôleur pour la plupart des ressources connues
     */
    public function testCreateControllerDefault()
    {
        $controller = _AControllerFactory::createController('Planning', $this->storageConnector, $this->router);

        $this->object($controller)->isInstanceOf(_AController::class);
    }

    /**
     * Test de la résolution de namespace pour le contrôleur
     */
    public function testGetControllerClassname()
    {
        $ressource = 'Planning\Creneau';

        $this->string(_AControllerFactory::getControllerClassname($ressource))
            ->isIdenticalTo('\App\Components\Planning\Creneau\Controller')
        ;
    }
}
