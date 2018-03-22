<?php
namespace LibertAPI\Tests\Units\Absence\Type;

use LibertAPI\Absence\Type\TypeEntite;

/**
 * Classe de test du DAO de type d'absence
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 */
final class TypeDao extends \LibertAPI\Tests\Units\Tools\Libraries\ADao
{
    /*************************************************
     * POST
     *************************************************/

    /**
     * Teste la méthode post quand tout est ok
     */
    public function testPostOk()
    {
        $this->calling($this->connector)->lastInsertId = 314;
        $this->newTestedInstance($this->connector);

        $postId = $this->testedInstance->post(new TypeEntite($this->entiteContent));

        $this->integer($postId)->isIdenticalTo(314);
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * Teste la méthode put quand tout est ok
     */
    public function testPutOk()
    {
        $this->newTestedInstance($this->connector);

        $put = $this->testedInstance->put(new TypeEntite($this->entiteContent));

        $this->variable($put)->isNull();
    }

    protected function getStorageContent()
    {
        return [
            'ta_id' => 38,
            'ta_type' => 81,
            'ta_libelle' => 'libelle',
            'ta_short_libelle' => 'li',
        ];
    }

    private $entiteContent = [
        'id' => 33,
        'type' => 'top',
        'libelle' => 'ellebil',
        'libelleCourt' => 'el',
    ];
}
