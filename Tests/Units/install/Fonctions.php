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
    }

    /**
     * @var array Données de configuration API
     */
    private $data;

    /**
     * Test de l'insertion des données de configuration pour l'api en cas d'échec
     */
    public function testSetDataConfigurationApiError()
    {
        $this->function->file_put_contents = false;

        $this->exception(function () {
            _Fonctions::setDataConfigurationApi($this->data);
        })->isInstanceOf(\Exception::class)
        ->function('file_put_contents')->wasCalled()->once();
    }

    /**
     * Test de l'insertion des données de configuration pour l'api avec succès
     */
    public function testSetDataConfigurationApiOk()
    {
        $this->function->file_put_contents = 314;

        $result = _Fonctions::setDataConfigurationApi($this->data);
        $this
            ->variable($result)->isNull()
            ->function('file_put_contents')->wasCalled()->once();

    }
}
