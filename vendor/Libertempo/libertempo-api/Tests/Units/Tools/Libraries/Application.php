<?php
namespace LibertAPI\Tests\Units\Tools\Libraries;

use LibertAPI\Tools\Libraries\Application as _Application;

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
     * @var \Doctrine\DBAL\Connection Mock du connecteur
     */
    private $connector;

    /**
     * @var \Doctrine\DBAL\Statement Mock du curseur de résultat
     */
    protected $result;

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->result = new \mock\Doctrine\DBAL\Statement();
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->connector = new \mock\Doctrine\DBAL\Connection();
        $this->calling($this->connector)->query = $this->result;
    }

    /**
     * Teste la récupération du token
     */
    public function testGetTokenInstance()
    {
        $this->calling($this->result)->fetchAll = [
            [
                'appli_variable' => 'token_instance',
                'appli_valeur' => 'Abracadabra',
            ],
        ];
        $application = new _Application($this->connector);

        $this->string($application->getTokenInstance())->isIdenticalTo('Abracadabra');
    }
}
