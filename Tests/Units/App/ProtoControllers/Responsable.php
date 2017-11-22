<?php
namespace Test\Units\App\ProtoControllers;

use App\ProtoControllers\Responsable as _Responsable;

class Responsable extends \Tests\Units\TestUnit
{
    private $result;
    private $db;

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->result = new \mock\Mysqli\Result();
        $this->db = new \mock\includes\SQL();
        $this->calling($this->db)->query = $this->result;
    }
    
    public function testgetInfosResponsablesRempli()
    {
        $data = [
            'u_login' => 'paolo',
            'u_nom' => 'dupont',
            'u_prenom' => 'paolo',
            'u_is_resp' => 'Y',
            'u_is_admin' => 'N',
            'u_is_hr' => 'N',
            'u_is_active' => 'Y',
            'u_passwd' => '969044ea4df948fb0392308cfff9cdce',
            'u_quotite' => '100',
            'u_email' => 'paolo@libertempo.fr',
            'u_num_exercice' => '1',
            'planning_id' => '1',
            'u_heure_solde' => '10'
        ];
        
        $this->calling($this->result)->fetch_all = $data;
        $resultat = _Responsable::getInfosResponsables($this->db, true);
        
        $this->array($resultat)->isIdenticalTo($data);
    }
    
    public function testgetInfosResponsablesVide()
    {
        $this->calling($this->result)->fetch_all = [];
        $resultat = _Responsable::getInfosResponsables($this->db, true);
        $this->array($resultat)->isEmpty();
    }
}