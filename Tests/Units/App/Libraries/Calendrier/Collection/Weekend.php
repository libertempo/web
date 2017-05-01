<?php
namespace Tests\Units\App\Libraries\Calendrier\Collection;

use App\Libraries\Calendrier\Collection\Weekend as _Weekend;

class Weekend extends \Tests\Units\TestUnit
{
    // getListeWithSamedi
    // getListeWithDimanche
    // getListeWithNothing

    public function testGetListeWithNothing()
    {
        $this->mockGenerator->shuntParentClassCalls();
        $this->mockGenerator->orphanize('__construct');
        $db = new \mock\includes\SQL;
        ddd($db);
    }
}
