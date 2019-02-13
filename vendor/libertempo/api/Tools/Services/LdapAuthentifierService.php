<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Services;

use \Adldap\AdldapInterface;
use Psr\Http\Message\ServerRequestInterface as IRequest;

/**
 * Service d'authentification via un serveur LDAP
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.3
 */
class LdapAuthentifierService extends AAuthentifierFactoryService
{
    public function __construct(AdldapInterface $ldap)
    {
        $this->ldap = $ldap;
    }

    /**
     * @inheritDoc
     * @require that configuration of ldap is present
     */
    public function isAuthentificationSucceed(IRequest $request) : bool
    {
        $this->storeBasicIdentificants($request);
        assert(isset($request->getAttribute('configurationFileData')->ldap));
        $configurationLdap = $request->getAttribute('configurationFileData')->ldap;

        $scheme = parse_url($configurationLdap->serveur, PHP_URL_SCHEME);
        $hostOne = parse_url($configurationLdap->serveur, PHP_URL_HOST);
        $hostTwo = parse_url($configurationLdap->up_serveur, PHP_URL_HOST);

        $config = [
          'hosts'    => [$hostOne, $hostTwo],
          'base_dn'  => $configurationLdap->base,
          'username' => $configurationLdap->utilisateur,
          'password' => $configurationLdap->mot_de_passe,
          'use_ssl' => isset($scheme) && 's' === substr($scheme, -1, 1),
        ];

        $this->ldap->addProvider($config);


        $provider = $this->ldap->connect();
        $search = $provider->search();
        $search->select(['dn']);
        $user = $search->where($configurationLdap->login, $this->getLogin())->firstOrFail();

        return $provider->auth()->attempt($user->getDn(), $this->getPassword());
    }

    /**
     * @var AdldapInterface Service LDAP
     */
    private $ldap;
}
