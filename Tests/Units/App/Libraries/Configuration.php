<?php
namespace Test\Units\App\Libraries;

use App\Libraries\Configuration as _Configuration;

class Configuration extends \Tests\Units\TestUnit
{
    private $result;
    private $db;
    private $config;

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->result = new \mock\Mysqli\Result();
        $this->db = new \mock\includes\SQL();
        $this->calling($this->db)->query = $this->result;

        $this->calling($this->result)->fetch_array[1] =  [
                        'conf_nom' => 'installed_version',
                        'conf_valeur' => '1.9',
                        'conf_groupe' => '00_libertempo',
                        'conf_type' => 'texte',
                        'conf_commentaire' => 'config_comment_installed_version'
                    ];

        $this->calling($this->result)->fetch_array[2] = null;
        $this->config = new _Configuration($this->db);

    }

    public function testgetInstalledVersion()
    {
        $version = $this->config->getInstalledVersion();
        $this->variable($version)->isIdenticalTo('1.9');
    }

}
