<?php
namespace Tests\Units\App\Libraries;

use App\Libraries\InjectableCreator as _InjectableCreator;

/**
 * Classe de test du gestionnaire de dépendances
 *
 * @since 1.10
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see _InjectableCreator
 */
class InjectableCreator extends \Tests\Units\TestUnit
{
    /**
    * @var \includes\SQL
    */
    private $db;
    private $result;
    private $config;

    /**
     * @var _InjectableCreator
     */
    private $class;

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

        $this->config = new \mock\App\Libraries\Configuration($this->db);
        $this->class = new _InjectableCreator($this->db, $this->config);
    }

    public function testConstruct()
    {
        $this->object(new _InjectableCreator($this->db, $this->config))
            ->isInstanceOf(_InjectableCreator::class);
    }

    public function testGetForbiddenClass()
    {
        $this->exception(function () {
            $this->class->get(\stdClass::class);
        })->isInstanceOf('\LogicException');
    }

    public function testGetUnknownClass()
    {
        $this->exception(function () {
            $this->class->get(_InjectableCreator::class);
        })->isInstanceOf('\LogicException');
    }

    public function testGetAuthorizedClass()
    {
        $classname = \App\Libraries\Calendrier\Evenements\Weekend::class;

        $this->object($this->class->get($classname))->isInstanceOf($classname);
    }
}
