<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Services;

use LibertAPI\Tools\Libraries\ARepository;
use LibertAPI\Tools\Libraries\StorageConfiguration;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use LibertAPI\Tools\Exceptions\BadRequestException;

/**
 * Fabrique de service d'authentification. C'est elle et elle seule qui a conscience des critières de sélection de tel ou tel service.
 * Les clients ne manipulent que des contrats.
 *
 * Si l'on suit Oncle Bob, le test est plus important. La construction des fils aurait pu être restreinte à la fabrique, mais je préfère ouvrir.
 * Compte tenu que ces derniers accèdent à l'extérieur, ils *doivent* être vérifiés.
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.1
 */
abstract class AAuthentifierFactoryService
{
    /**
     * Retourne la bonne implémentation du service d'authentification en fonction des paramètres transmis
     */
    final public static function getAuthentifier(StorageConfiguration $configuration, ARepository $repository) : self
    {
        switch ($configuration->getHowToConnectUser()) {
            case 'ldap':
                return new LdapAuthentifierService(new \Adldap\Adldap());
            case 'dbconges':
               return new InterneAuthentifierService($repository);
            case 'cas':
            case 'sso':
                // Dans l'intervalle où CAS et SSO ne sont pas fait, workaround, même avec un mdp null /!\
                return new WorkaroundAuthentifierService($repository);
            default:
                throw new \UnexpectedValueException("Unknown Service");
        }
    }

    /**
     * Contrat standard des services d'authentification
     * @return true si l'authentification s'est bien déroulée
     * @throws BadRequestException Si la requête n'est pas bien formée
     */
    abstract public function isAuthentificationSucceed(IRequest $request) : bool;

    /**
     * Store in memory login / password
     * @throws BadRequestException Si la requête n'est pas bien formée
     */
    final protected function storeBasicIdentificants(IRequest $request)
    {
        $authentificationType = 'Basic';
        $authentification = $request->getHeaderLine('Authorization');
        if (0 !== stripos($authentification, $authentificationType)) {
            throw new BadRequestException();
        }

        $authentification = substr($authentification, strlen($authentificationType) + 1);
        list($login, $password) = explode(':', base64_decode($authentification));
        $this->setLogin($login);
        $this->setPassword($password);
    }

    protected function getRepository() : ARepository
    {
        return $this->repository;
    }

    public function getLogin() : string
    {
        return $this->login;
    }

    private function setLogin($login)
    {
        $this->login = $login;
    }

    protected function getPassword() : string
    {
        return $this->password;
    }

    protected function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @var string Login de l'utilisateur en cours de connexion
     */
    private $login;

    /**
     * @var string MDP de l'utilisateur en cours de connexion
     */
    private $password;
}
