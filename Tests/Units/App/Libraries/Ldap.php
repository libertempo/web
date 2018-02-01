<?php

namespace Tests\Units\App\Libraries;

class Ldap extends \Tests\Units\TestUnit {

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);

        $this->function->ldap_connect = false;
    }

    // là tu as ton premier cas d'échec, quand la construction se vautre
    public function testConstructFailed()
    {
        $this->function->ldap_bind = false;
        $this->exception(function() {
            $this->newTestedInstance($this->configuration);
        });
    }

    public function testGetMailUser()
    {
        $this->function->ldap_bind = true;
        $this->function->ldap_search = false;
        $this->function->ldap_get_entries = [
            'count' => 1,
            0 => ['mail' => [0 => 'mail@example.org'],
            ],
        ];
        $this->newTestedInstance($this->configuration);
        $this->string($this->testedInstance->getEmailUser('toto'))->isEqualTo('mail@example.org');
    }

    public function testGetInfosUser()
    {
        $this->function->ldap_bind = true;
        $this->function->ldap_search = false;
        $this->function->ldap_get_entries = [
            'count' => 2,
            0 => ['uid' => [0 => 'login'],
                'sn' => [0 => 'nom'],
                'givenname' => [0 => 'prenom'],
            ],
            1 => ['uid' => [0 => 'login2'],
                'sn' => [0 => 'nom2'],
                'givenname' => [0 => 'prenom2'],
            ],
        ];
        $return = '[{"login":"login","nom":"nom","prenom":"prenom"},{"login":"login2","nom":"nom2","prenom":"prenom2"}]';
        $this->newTestedInstance($this->configuration);
        $this->string($this->testedInstance->searchLdap('toto'))->isEqualTo($return);
    }

    public function testConstructSuccess()
    {
        $this->function->ldap_bind = true;
        $this->newTestedInstance($this->configuration);
        $this->object($this->testedInstance)->isInstanceOf(\App\Libraries\Ldap::class);
    }

    private $configuration = [
        'server' => 'server',
        'version' => 3,
        'attrNom' => 'sn',
        'attrPrenom' => 'givenname',
        'attrLogin' => 'uid',
        'attrNomAff' => 'nomAffichee',
        'attrMail' => 'mail',
        'attrFiltre' => 'attrFiltre',
        'filtre' => 'filtre',
        'bindUser' => 'user',
        'bindPassword' => 'pwd'
    ];

}
