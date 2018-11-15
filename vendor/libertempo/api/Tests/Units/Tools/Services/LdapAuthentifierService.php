<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Tools\Services;

/**
 * Classe de test du service d'authentification via LDAP
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.3
 */
class LdapAuthentifierService extends \Atoum
{
    /**
     * Init des tests
     */
    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);

        $this->connection = new \mock\Adldap\Connections\Ldap();
        $this->provider = new \mock\Adldap\Connections\Provider();
        $this->ldap = new \mock\Adldap\Adldap();
        $this->calling($this->ldap)->addProvider = '';
        $this->calling($this->ldap)->connect = $this->provider;
        $this->calling($this->provider)->getConnection = $this->connection;
        $this->mockGenerator->orphanize('__construct');
        $this->request = new \mock\Slim\Http\Request();
        $configuration = json_decode(json_encode($this->configuration));
        $this->calling($this->request)->getAttribute = $configuration;
        $this->calling($this->request)->getHeaderLine = 'Basic QWxhZGRpbjpPcGVuU2VzYW1l';
    }

    public function testIsAuthentificationSucceedFalse()
    {
        $this->calling($this->connection)->bind = false;
        $this->newTestedInstance($this->ldap);
        $succeed = $this->testedInstance->isAuthentificationSucceed($this->request);

        $this->boolean($succeed)->isFalse();
    }

    public function testIsAuthentificationSucceedTrue()
    {
        $this->calling($this->connection)->bind = true;
        $this->newTestedInstance($this->ldap);
        $succeed = $this->testedInstance->isAuthentificationSucceed($this->request);

        $this->boolean($succeed)->isTrue();
    }

    /**
    * @var \Adldap\AdldapInterface Mock du service LDAP
    */
    private $ldap;

    /**
     * @var \Adldap\Connections\ProviderInterface
     */
    private $provider;

    /**
     * @var \Adldap\Connections\ConnectionInterface
     */
    private $connection;

    /**
     * @var array
     */
    private $configuration = [
        'ldap' => [
            'serveur' => '',
            'up_serveur' => '',
            'base' => '',
            'utilisateur' => '',
            'mot_de_passe' => '',
            'login' => '',
            'domaine' => '',
        ],
    ];

    /**
     * @var \Slim\Http\Request Mock de la requête HTTP
     */
    protected $request;
}
