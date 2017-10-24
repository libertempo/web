<?php
namespace Tests\Units\App\Libraries;

use App\Libraries\Application as _Application;

/**
 * Classe de test des accès aux données de l'application
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 */
final class Application extends \Atoum
{
    /**
     * @var \mock\PDO Mock du connecteur
     */
    private $connector;

    /**
     * @var \mock\PDOStatement Mock du curseur de résultat PDO
     */
    protected $statement;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->statement = new \mock\PDOStatement();
        $this->statement->getMockController()->execute = '';
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->connector = new \mock\PDO();
        $this->connector->getMockController()->query = $this->statement;
    }

    /**
     * Teste la récupération du token
     */
    public function testGetTokenInstance()
    {
        $this->statement->getMockController()->fetchAll = [
            [
                'appli_variable' => 'token_instance',
                'appli_valeur' => 'Abracadabra',
            ],
        ];
        $application = new _Application($this->connector);

        $this->string($application->getTokenInstance())->isIdenticalTo('Abracadabra');
    }
}
