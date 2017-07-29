<?php
namespace Tests\Units\utilisateur;

use \install\Fonctions as _Fonctions;

/**
 * Classe de test des fonctions de l'installation
 *
 * @since  1.10
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see    \utilisateur\Fonctions
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
     * Test l'insertion des données de configuration pour l'api en cas d'échec
     */
    public function testSetDataConfigurationApiError()
    {
        $this->function->file_put_contents = false;

        $this->exception(function () {
            _Fonctions::setDataConfigurationApi($this->server, $this->database, $this->user, $this->password);
        })->isInstanceOf(\Exception::class)
        ->function('session_start')->wasCalled()->once();
    }
}
