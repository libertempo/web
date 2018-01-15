<?php
namespace LibertAPI\Tools\Libraries;

use Doctrine\DBAL\Driver\Connection;

/**
 * Bibliothèque d'accès aux données stockées de l'application
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 *
 * Ne devrait être contacté que par AControllerFactory
 * Ne devrait contacter personne
 */
class Application
{
    /**
     * @var Connection Connecteur à la BDD
     */
    private $storageConnector;

    /**
     * @var array Données de l'application
     */
    private $data;

    public function __construct(Connection $storageConnector)
    {
        $this->storageConnector = $storageConnector;
        $data = [];
        foreach ($this->fetchAllData() as $applicationVariable) {
            $data[$applicationVariable['appli_variable']] = $applicationVariable['appli_valeur'];
        }
        $this->data = $data;
    }

    /**
     * Récupère la totalité des données stockées
     *
     * @return array
     */
    private function fetchAllData()
    {
        $req = 'SELECT * FROM `conges_appli`';
        $res = $this->storageConnector->query($req);

        return $res->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTokenInstance()
    {
        return $this->data['token_instance'];
    }
}
