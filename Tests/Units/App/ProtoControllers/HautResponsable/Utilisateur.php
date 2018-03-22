<?php
namespace Test\Units\App\ProtoControllers\HautResponsable;

use App\ProtoControllers\HautResponsable\Utilisateur as _Utilisateur;

class Utilisateur extends \Tests\Units\TestUnit
{
    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->result = new \mock\MYSQLIResult();
        $this->db = new \mock\includes\SQL();
        $this->calling($this->db)->query = $this->result;

        $this->config = new \mock\App\Libraries\Configuration($this->db);

    }

    private $db;
    private $result;
    private $config;

    public function testdataForm2ArraySansLdap()
    {
        $this->calling($this->config)->isUsersExportFromLdap = false;
        $this->calling($this->config)->getHowToConnectUser = "dbconges";

        $htmlPost = [
            'new_login' => 'monkey',
            'new_nom' => 'monkey d.',
            'new_prenom' => 'luffy',
            'new_quotite' => '80',
            'new_solde_heure' => '00:00',
            'new_is_active' => 'Y',
            'new_is_resp' => 'N',
            'new_is_admin' => 'o',
            'new_is_hr' => '1',
            'new_email' => 'luffy@dragon',
            'new_password1' => '1234',
            'new_password2' => '1234',
            'tab_new_jours_an' => [],
            'tab_new_solde' => [],
            'tab_new_reliquat' => [],
            'checkbox_user_groups' => [4 => '', 5 => '']
        ];

        $expected = [
            'login' => "monkey",
            'oldLogin' => "monkey",
            'nom' => "monkey d.",
            'prenom' => "luffy",
            'quotite' => 80,
            'soldeHeure' => "00:00",
            'isActive' => "Y",
            'isResp' => "N",
            'isAdmin' => "N",
            'isHR' => "N",
            'email' => "luffy@dragon",
            'pwd1' => "81dc9bdb52d04dc20036dbd8313ed055",
            'pwd2' => "81dc9bdb52d04dc20036dbd8313ed055",
            'groupesId' => [4,5]
        ];
        $data = _Utilisateur::dataForm2Array($htmlPost, $this->db, $this->config);
        $this->array($data)->isIdenticalTo($expected);
    }

    public function testisFormValideOk()
    {
        $this->calling($this->config)->isUsersExportFromLdap = false;
        $this->calling($this->config)->isHeuresAutorise = true;

        $data = [
            'login' => "monkey",
            'nom' => "monkey d.",
            'prenom' => "luffy",
            'quotite' => 80,
            'soldeHeure' => "00:00",
            'email' => "luffy@dragon.com",
            'groupesId' => [4,5],
            'joursAn' => [],
            'soldes' => [],
            'reliquats' => []
        ];

        $errors = [];
        $resultat = _Utilisateur::isFormValide($data, $errors, $this->db, $this->config);

        $this->boolean($resultat)->isTrue();
        $this->array($errors)->isEmpty();
    }

    public function testisFormValideNotOk()
    {
        $this->calling($this->config)->isUsersExportFromLdap = false;
        $this->calling($this->config)->isHeuresAutorise = true;

        $data = [
            'login' => "",
            'nom' => "",
            'prenom' => "",
            'quotite' => 123,
            'soldeHeure' => "00:00gd",
            'email' => "luffy@dragon",
            'groupesId' => [4,5],
            'joursAn' => [],
            'soldes' => [],
            'reliquats' => []
        ];

        $errors = [];
        $resultat = _Utilisateur::isFormValide($data, $errors, $this->db, $this->config);
        $this->boolean($resultat)->isFalse();
        $this->array($errors)->hasSize(6);
    }

    function testisDeletableOk()
    {
        $this->calling($this->result)->fetch_array[0] = [0];
        $isDeletable = _Utilisateur::isDeletable('monkey', $this->db);
        $this->boolean($isDeletable)->isTrue();
    }

    function testisDeletableNotOk()
    {
        $this->calling($this->result)->fetch_array[0] = [1];
        $isDeletable = _Utilisateur::isDeletable('monkey', $this->db);
        $this->boolean($isDeletable)->isFalse();
    }
}