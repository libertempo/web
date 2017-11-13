<?php
namespace LibertAPI\Tests\Units\Tools\Libraries;

use LibertAPI\Tools\Libraries\AControllerFactory as _AControllerFactory;
use LibertAPI\Tools\Libraries\AController as _AController;
/**
 * Test de la fabrication de contrôleurs
 *
 * @since 0.2
 */
final class AControllerFactory extends \Atoum
{
    /**
     * @var \Doctrine\DBAL\Connection Connecteur BD
     */
    private $storageConnector;

    /**
     * @var \Doctrine\DBAL\Statement Mock du curseur de résultat
     */
    private $result;

    /**
     * @var \Slim\Slim\Router Mock du routeur
     */
    private $router;

    /**
     * Init des tests
     */
    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->result = new \mock\Doctrine\DBAL\Statement();
        $this->calling($this->result)->fetchAll = [['appli_variable' => '', 'appli_valeur' => '']];
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $queryBuilder = new \mock\Doctrine\DBAL\Query\QueryBuilder();
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->storageConnector = new \mock\Doctrine\DBAL\Connection();
        $this->calling($this->storageConnector)->createQueryBuilder = $queryBuilder;
        $this->calling($this->storageConnector)->query = $this->result;

        $this->router = new \mock\Slim\Router();
    }

    /**
     * Test de la création de contrôleur pour une ressource inconnue
     */
    public function testCreateControllerNotFound()
    {
        $this->exception(function () {
            $class = $this->testedClass()->getClass();
            $class::createControllerAuthentification('notFoundNs', $this->storageConnector, $this->router);
        })->isInstanceOf('\DomainException');
    }

    /**
     * Test de la création de contrôleur pour l'authentification
     */
    public function testCreateControllerAuthentification()
    {
        $class = $this->testedClass()->getClass();
        $controller = $class::createControllerAuthentification('Authentification', $this->storageConnector, $this->router);

        $this->object($controller)->isInstanceOf(\LibertAPI\Authentification\AuthentificationController::class);
    }

    /**
     * Test de la création de contrôleur pour la plupart des ressources connues
     */
    public function testCreateControllerDefault()
    {
        $class = $this->testedClass()->getClass();
        $controller = $class::createControllerWithUser('Planning', $this->storageConnector, $this->router, new \LibertAPI\Utilisateur\UtilisateurEntite([]));

        $this->object($controller)->isInstanceOf(_AController::class);
    }

    /**
     * Test de la résolution de namespace pour le contrôleur
     */
    public function testGetControllerClassname()
    {
        $ressource = 'Planning\Creneau';
        $class = $this->testedClass()->getClass();
        $this->string($class::getControllerClassname($ressource))
            ->isIdenticalTo('\LibertAPI\Planning\Creneau\CreneauController')
        ;
    }
}
