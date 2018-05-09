<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Groupe;

use LibertAPI\Groupe\GroupeEntite;

/**
 * Classe de test du DAO de groupe
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.7
 */
final class GroupeDao extends \LibertAPI\Tests\Units\Tools\Libraries\ADao
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

        $postId = $this->testedInstance->post(new GroupeEntite($this->entiteContent));

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

        $put = $this->testedInstance->put(new GroupeEntite($this->entiteContent));

        $this->variable($put)->isNull();
    }

    protected function getStorageContent()
    {
        return [
            'g_gid' => 42,
            'g_groupename' => 'name',
            'g_comment' => 'this is a storage comment',
            'g_double_valid' => 'Y'
        ];
    }

    private $entiteContent = [
        'id' => 72,
        'name' => 'name',
        'comment' => 'text',
        'double_validation' => true,
    ];
}
