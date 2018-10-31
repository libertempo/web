<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Libraries;

use Doctrine\DBAL\Driver\Connection;

/**
 * Objet de lecture de la configuration stockée. Honteusement volée de libertempo/web 1.12
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.1
 */
class StorageConfiguration
{
    private $data;

    public function __construct(Connection $storageConnector)
    {
        $req = 'SELECT * FROM conges_config ORDER BY conf_groupe';
        $res = $storageConnector->query($req);
        foreach ($res->fetchAll(\PDO::FETCH_ASSOC) as $value) {
            $groupe = $value['conf_groupe'];
            $nom = $value['conf_nom'];
            $this->data[$groupe][$nom] = [
                'valeur' => $value['conf_valeur'],
                'type' => $value['conf_type'],
            ];
        }
    }

    public function getHowToConnectUser() : string
    {
        return $this->getGroupeAuthentificationValeur('how_to_connect_user');
    }

    public function isUsersExportFromLdap() : bool
    {
        return $this->getGroupeAuthentificationValeur('export_users_from_ldap');
    }

    /**
     * Retourne une valeur du groupe d'authentification par son nom
     *
     * @param string $nom
     *
     * @return mixed
     */
    private function getGroupeAuthentificationValeur($nom)
    {
        return $this->getValeur($nom, '04_Authentification');
    }

    /**
     * Retourne la valeur d'une configuration en fonction de son groupe et de son nom
     *
     * @param string $nom
     * @param string $groupe
     *
     * @return mixed
     * @require that $nom et $groupe sont des offsets connus
     */
    private function getValeur($nom, $groupe) {
        assert(isset($this->data[$groupe]) && isset($this->data[$groupe][$nom]));

        $config = $this->data[$groupe][$nom];

        if ('boolean' === $config['type']) {
            return 'TRUE' === $config['valeur'];
        }

        return $config['valeur'];
    }

}
