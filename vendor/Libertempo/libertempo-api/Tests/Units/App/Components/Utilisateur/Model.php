<?php
namespace Tests\Units\App\Components\Utilisateur;

use \App\Components\Utilisateur\Model as _Model;

/**
 * Classe de test du modèle de l'utilisateur
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 */
final class Model extends \Tests\Units\App\Libraries\AModel
{
    /**
     * @inheritDoc
     */
    public function testConstructWithId()
    {
        $id = 'Balin';

        $model = new _Model(['id' => $id]);

        $this->string($model->getId())->isIdenticalTo($id);
    }

    /**
     * @inheritDoc
     */
    public function testConstructWithoutId()
    {
        $model = new _Model(['token' => 'token']);

        $this->variable($model->getId())->isNull();
    }

    /**
     * @inheritDoc
     */
    public function testReset()
    {
        $model = new _Model(['id' => 'Balin', 'token' => 'token']);

        $this->assertReset($model);
    }

    /**
     * Teste la méthode populateToken avec un mauvais domaine de définition
     */
    public function testPopulateTokenBadDomain()
    {
        $model = new _Model([]);
        $token = '';

        $this->exception(function () use ($model, $token) {
            $model->populateToken($token);
        })->isInstanceOf('\DomainException');
    }


    /**
     * Teste la méthode populateToken avec ok
     */
    public function testPopulateTokenOk()
    {
        $model = new _Model([]);
        $token = 'AZP3401GJE9#';

        $model->populateToken($token);

        $this->string($model->getToken())->isIdenticalTo($token);
    }
}
