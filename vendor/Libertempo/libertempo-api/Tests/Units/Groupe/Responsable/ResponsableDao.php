<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Groupe\Responsable;

use LibertAPI\Utilisateur\UtilisateurEntite;

/**
 * Classe de test du DAO de responsable de groupe
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.7
 */
final class ResponsableDao extends \LibertAPI\Tests\Units\Tools\Libraries\ADao
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

    /**
     * Teste la méthode post quand tout est ok
     */
    public function testPostOk()
    {
        $this->newTestedInstance($this->connector);

        $this->exception(function () {
            $this->testedInstance->post(new UtilisateurEntite([]));
        })->isInstanceOf(\RuntimeException::class);
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

        $this->exception(function () {
            $this->testedInstance->put(new UtilisateurEntite([]));
        })->isInstanceOf(\RuntimeException::class);
    }

    /*************************************************
     * DELETE
     *************************************************/

    /**
     * Teste la méthode delete
     */
    public function testDeleteOk()
    {
        $this->newTestedInstance($this->connector);

        $this->exception(function () {
            $this->testedInstance->delete(0);
        })->isInstanceOf(\RuntimeException::class);
    }

    /**
     * Duplication de la fonction dans UtilisateurDao (Cf. decisions.md #2018-02-17)
     */
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
            'u_see_all' => 'Y',
            'u_passwd' => 'Sésame Ouvre toi',
            'u_quotite' => '21220',
            'u_email' => 'aladdin@example.org',
            'u_num_exercice' => '3',
            'planning_id' => 12,
            'u_heure_solde' => 1,
            'date_inscription' => 123456789,
        ];
    }
}
