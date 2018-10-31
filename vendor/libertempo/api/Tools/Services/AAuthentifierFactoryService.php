<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Services;

use LibertAPI\Tools\Libraries\ARepository;
use LibertAPI\Tools\Libraries\StorageConfiguration;
use Psr\Http\Message\ServerRequestInterface as IRequest;

/**
 * Fabrique de service d'authentification. C'est elle et elle seule qui a conscience des critières de sélection de tel ou tel service.
 * Les clients ne manipulent que des contrats.
 *
 * Si l'on suit Oncle Bob, le test est plus important. La construction des fils aurait pu être restreinte à la fabrique, mais je préfère ouvrir.
 * Compte tenu que ces dernièrs accèdent à l'extérieur, ils *doivent* être vérifiés.
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
                return new LdapAuthentifierService($repository);
            case 'dbconges':
                return new InterneAuthentifierService($repository);
            default:
                throw new \UnexpectedValueException("Unknown Service");
        }
    }

    public function __construct(ARepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Contrat standard des services d'authentification
     * @return true si l'authentification s'est bien déroulée
     * @throws BadRequestException Si la requête n'est pas bien formée
     */
    abstract public function isAuthentificationSucceed(IRequest $request) : bool;

    /**
     * Retourne le login de l'utilisateur pour être consommé par la DB
     */
    abstract public function getLogin() : string;

    protected function getRepository() : ARepository
    {
        return $this->repository;
    }

    /**
     * @var ARepository Repository utilisateur
     */
    private $repository;
}
