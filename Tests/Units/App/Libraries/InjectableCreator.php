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

    /**
     * @var _InjectableCreator
     */
    private $class;

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->db = new \mock\includes\SQL();
        $this->class = new _InjectableCreator($this->db);
    }

    public function testConstruct()
    {
        $this->object(new _InjectableCreator($this->db))
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
