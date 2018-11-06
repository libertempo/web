<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Tools\Services;

use LibertAPI\Tools\Services;

/**
 * Classe de test de la fabrique de services d'authentification
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.1
 */
class AAuthentifierFactoryService extends \Atoum
{
    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);

        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->configuration = new \mock\LibertAPI\Tools\Libraries\StorageConfiguration();
        $this->mockGenerator->orphanize('__construct');
        $this->repository = new \mock\LibertAPI\Tools\Libraries\ARepository();
    }

    public function testGetLdapService()
    {
        $this->calling($this->configuration)->getHowToConnectUser = 'ldap';

        $testedClass = $this->testedClass->getClass();
        $service = $testedClass::getAuthentifier($this->configuration, $this->repository);
        $this->object($service)->isInstanceOf(Services\LdapAuthentifierService::class);
    }

    public function testGetInterneService()
    {
        $this->calling($this->configuration)->getHowToConnectUser = 'dbconges';

        $testedClass = $this->testedClass->getClass();
        $service = $testedClass::getAuthentifier($this->configuration, $this->repository);
        $this->object($service)->isInstanceOf(Services\InterneAuthentifierService::class);
    }

    public function testGetCASService()
    {
        $this->calling($this->configuration)->getHowToConnectUser = 'cas';

        $testedClass = $this->testedClass->getClass();
        $service = $testedClass::getAuthentifier($this->configuration, $this->repository);
        $this->object($service)->isInstanceOf(Services\WorkaroundAuthentifierService::class);
    }

    public function testGetUnknownService()
    {
        $this->calling($this->configuration)->getHowToConnectUser = 'foobar';

        $this->exception(function () {
            $testedClass = $this->testedClass->getClass();
            $testedClass::getAuthentifier($this->configuration, $this->repository);
        })->isInstanceOf(\UnexpectedValueException::class);
    }

    /**
     * @var LibertAPI\Tools\Libraries\StorageConfiguration Mock de la configuration
     */
    private $configuration;

    /**
     * @var LibertAPI\Tools\Libraries\ARepository Mock d'un repository lambda
     */
    private $repository;
}
