<?php
namespace Tests\Units\install;

/**
 * Classe de test des fonctions de l'install
 *
 * @since  1.10
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see    \install\Fonctions
 */
class Fonctions extends \Tests\Units\TestUnit
{
    /**
     * @inheritDoc
     */
    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->data = [
            'serveur' => 'serveur',
            'base' => 'base',
            'user' => 'user',
            'password' => 'password',
        ];
        $this->db = new \mock\includes\SQL();
        $this->calling($this->db)->query = '';
        $this->calling($this->db)->quote = '';
        $_SERVER['HTTP_HOST'] = '';
        $_SERVER['REQUEST_URI'] = '';
    }

    /**
     * @var array Données de configuration API
     */
    private $data;

    private $db;

    /**
     * Test de l'insertion des données de configuration pour l'api en cas d'échec
     */
    public function testSetDataConfigurationApiError()
    {
        $this->function->file_put_contents = false;

        $this->exception(function () {
            $class = $this->testedClass()->getClass();
            $class::setDataConfigurationApi($this->data);
        })->isInstanceOf(\Exception::class)
        ->function('file_put_contents')->wasCalled()->once();
    }

    /**
     * Test de l'insertion des données de configuration pour l'api avec succès
     */
    public function testSetDataConfigurationApiOk()
    {
        $this->function->file_put_contents = 314;
        $class = $this->testedClass()->getClass();
        $result = $class::setDataConfigurationApi($this->data);
        $this
            ->variable($result)->isNull()
            ->function('file_put_contents')->wasCalled()->once();
    }

    /**
     * Test de la définition de l'instance déclenchant une exception
     */
    public function testAddInstanceNameKo()
    {
        $this->function->stripos = false;
        $this->exception(function () {
            $class = $this->testedClass()->getClass();
            $class::addInstanceName($this->db);

        })->isInstanceOf(\Exception::class);
    }

    /**
     * Test de la définition de l'instance OK
     */
    public function testAddInstanceNameOk()
    {
        $class = $this->testedClass()->getClass();
        $this->function->stripos = 1;
        $instance = 'libertempo';
        $this->function->mb_substr = $instance;
        $this->function->parse_url = $instance;
        $this->function->file_get_contents = '';
        $this->function->str_replace = '';
        $this->function->file_put_contents = '';

        $this->string($class::addInstanceName($this->db))->isIdenticalTo($instance);
    }
}
