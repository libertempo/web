<?php
namespace Api\Tests\Units\App\Planning;

use \Api\App\Planning\Repository as _Repository;

/**
 * Classe de test du contrôleur de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
class Repository extends \Atoum
{
    // getOneFound
    // getOneNotFound
    // getListeFound
    // getListeNotFound

    /**
     *
     */
    public function testGetOneNotFound()
    {
        /*
            cas d'erreur :
                id pas dans le domaine de def (pas de préconditions, impossible à deviner)
                retour pas de type model (postconditions)
        */
        $connector = '';
        $repository = new _Repository($connector);

        $this->exception(function () use ($repository) {
            $get = $repository->getOne(999);
            d($get);
        })->isInstanceOf('\Exception');
    }
}
