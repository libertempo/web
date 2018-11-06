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

        $config = [
          'hosts'    => [$configurationLdap->serveur, $configurationLdap->up_serveur],
          'base_dn'  => $configurationLdap->base,
          'username' => $configurationLdap->utilisateur,
          'password' => $configurationLdap->mot_de_passe,
        ];

        $this->ldap->addProvider($config);

        try {
            $wheres = [
                $configurationLdap->login . '=' . $this->getLogin(),
                $configurationLdap->domaine,
            ];
            $provider = $this->ldap->connect();
            $result = $provider->search()->findByDnOrFail(implode(',', $wheres));

            return $this->getPassword() === $result->getFirstAttribute('userpassword');
        } catch (\Adldap\Auth\BindException $e) {
            return false;
        } catch (\Adldap\Models\ModelNotFoundException $e) {
            return false;
        }
    }

    /**
     * @var AdldapInterface Service LDAP
     */
    private $ldap;
}
