<?php
namespace Tests\Units\install;

use \install\Fonctions as _Fonctions;

/**
 * Classe de test des fonctions de l'installation
 *
 * @since  1.10
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see    \install\Fonctions
 */
class Fonctions extends \Tests\Units\TestUnit
{
    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->server = '';
        $this->database = '';
        $this->user = '';
        $this->password = '';
    }

    private $server;
    private $database;
    private $user;
    private $password;

    /**
     * Test de l'insertion des données de configuration pour l'api en cas d'échec
     */
    public function testSetDataConfigurationApiError()
    {
        $this->function->file_put_contents = false;

        $this->exception(function () {
            _Fonctions::setDataConfigurationApi($this->server, $this->database, $this->user, $this->password);
        })->isInstanceOf(\Exception::class)
        ->function('file_put_contents')->wasCalled()->once();
    }
}
