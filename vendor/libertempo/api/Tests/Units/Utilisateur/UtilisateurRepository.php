<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Utilisateur;

use LibertAPI\Tools\Libraries\AEntite;

/**
 * Classe de test du repository de l'utilisateur
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 */
final class UtilisateurRepository extends \LibertAPI\Tests\Units\Tools\Libraries\ARepository
{
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
            $this->testedInstance->postOne($this->getConsumerContent());
        })->isInstanceOf(\RuntimeException::class);
    }

    protected function getConsumerContent() : array
    {
        return [
        ];
    }

    public function testDeleteOne()
    {
        $this->newTestedInstance($this->connector);

        $this->exception(function () {
            $this->testedInstance->deleteOne(345);
        })->isInstanceOf(\RuntimeException::class);
    }
}
