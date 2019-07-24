<?php
namespace Test\Units\App\ProtoControllers\HautResponsable;

use App\ProtoControllers\HautResponsable\ClotureExercice as _ClotureExercice;

class ClotureExercice extends \Tests\Units\TestUnit
{

    private $db;
    private $result;
    private $config;
    private $error;

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->result = new \mock\MYSQLIResult();
        $this->db = new \mock\includes\SQL();
        $this->calling($this->db)->query = $this->result;

        $this->config = new \mock\App\Libraries\Configuration($this->db);
    }

    public function testUpdateDateLimiteReliquatsNoLimit() {
        $this->calling($this->config)->getDateLimiteReliquats = 0;
        
        $result = _ClotureExercice::updateDateLimiteReliquats("2020", $this->error, $this->db, $this->config);
        $this->boolean($result)->isTrue;
    }

    public function testUpdateDateLimiteReliquatsPreMatch() {
        $this->calling($this->config)->getDateLimiteReliquats = "/12-06";

        $result = _ClotureExercice::updateDateLimiteReliquats("2020", $this->error, $this->db, $this->config);
        $this->boolean($result)->isFalse;
    }
}