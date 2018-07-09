<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Groupe\Responsable;

/**
 * Classe de test du repository de responsable de groupe
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 */
final class ResponsableRepository extends \LibertAPI\Tests\Units\Tools\Libraries\ARepository
{
    public function testGetOneEmpty()
    {
        $this->newTestedInstance($this->connector);
        $this->exception(function () {
            $this->testedInstance->getOne(4);
        })->isInstanceOf(\RuntimeException::class);
    }

    /**
     * Duplication de la fonction dans UtilisateurRepository (Cf. decisions.md #2018-02-17)
     */
    final protected function getStorageContent() : array
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

    public function testPostOne()
    {
        $this->newTestedInstance($this->connector);

        $this->exception(function () {
            $this->testedInstance->postOne([], new \mock\LibertAPI\Tools\Libraries\AEntite([]));
        })->isInstanceOf(\RuntimeException::class);
    }

    public function testPutOne()
    {
        $this->newTestedInstance($this->connector);

        $this->exception(function () {
            $this->testedInstance->putOne(new \mock\LibertAPI\Tools\Libraries\AEntite([]));
        })->isInstanceOf(\RuntimeException::class);
    }

    public function testDeleteOne()
    {
        $this->newTestedInstance($this->connector);

        $this->exception(function () {
            $this->testedInstance->deleteOne(new \mock\LibertAPI\Tools\Libraries\AEntite([]));
        })->isInstanceOf(\RuntimeException::class);
    }
}
