<?php
namespace LibertAPI\Tests\Units\Utilisateur;

use \LibertAPI\Utilisateur\UtilisateurDao as _Dao;

use LibertAPI\Utilisateur\UtilisateurEntite;

/**
 * Classe de test du DAO de l'utilisateur
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 */
final class UtilisateurDao extends \LibertAPI\Tests\Units\Tools\Libraries\ADao
{
    /*************************************************
     * GET
     *************************************************/

    /**
     * Teste la méthode getById avec un id non trouvé
     */
    public function testGetByIdNotFound()
    {
        $this->exception(function () {
            $this->newTestedInstance($this->connector)->getById(0);
        });
    }

    /**
     * Teste la méthode getById avec un id trouvé
     */
    public function testGetByIdFound()
    {
        $this->exception(function () {
            $this->newTestedInstance($this->connector)->getById(0);
        });
    }

    /*************************************************
     * POST
     *************************************************/

    public function testPost()
    {
        $dao = new _Dao($this->connector);
        $this->variable($dao->post(new UtilisateurEntite($this->entiteContent)))->isNull();
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * Teste la méthode put quand tout est ok
     */
    public function testPutOk()
    {
        $dao = new _Dao($this->connector);

        $put = $dao->put(new UtilisateurEntite($this->entiteContent));

        $this->variable($put)->isNull();
    }

    /*************************************************
     * DELETE
     *************************************************/

    /**
     * Teste la méthode delete quand tout est ok
     */
    public function testDeleteOk()
    {
        $this->calling($this->result)->rowCount = 1;
        $this->newTestedInstance($this->connector);

        $res = $this->testedInstance->delete(7);

        $this->variable($res)->isNull();
    }

    protected function getStorageContent()
    {
        return [
            'id' => 'Aladdin',
            'token' => 'token',
            'date_last_access' => 'date_last_access',
            'u_login' => 'Aladdin',
            'u_prenom' => 'Aladdin',
            'u_nom' => 'Genie',
            'u_is_resp' => 'Y',
            'u_is_admin' => 'Y',
            'u_is_hr' => 'N',
            'u_is_active' => 'Y',
            'u_passwd' => 'Sésame Ouvre toi',
            'u_quotite' => '21220',
            'u_email' => 'aladdin@example.org',
            'u_num_exercice' => '3',
            'planning_id' => 12,
            'u_heure_solde' => 1,
            'date_inscription' => 123456789,
        ];
    }

    private $entiteContent = [
        'id' => 'Aladdin',
        'token' => 'token',
        'dateLastAccess' => 'date_last_access',
    ];
}
