<?php
namespace Test\Units\App\ProtoControllers\Groupe;

use App\ProtoControllers\Groupe\Utilisateur as _Utilisateur;

class Utilisateur extends \Tests\Units\TestUnit
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

    public function testisUtilisateurDansGroupeFaux()
    {
        $this->calling($this->result)->fetch_array = null;
        $this->calling($this->db)->quote = 'robert';
        $resultat = _Utilisateur::isUtilisateurDansGroupe('robert', 1, $this->db);
        $this->boolean($resultat)->isfalse();
    }

    public function testisUtilisateurDansGroupeVrai()
    {
        $this->calling($this->result)->fetch_array = [1];
        $this->calling($this->db)->quote = 'robert';
        $resultat = _Utilisateur::isUtilisateurDansGroupe('robert', 1, $this->db);
        $this->boolean($resultat)->istrue();
    }
}