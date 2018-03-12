<?php
namespace LibertAPI\Tests\Units\Journal;

/**
 * Classe de test de l'entité journal
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 */
final class JournalEntite extends \LibertAPI\Tests\Units\Tools\Libraries\AEntite
{
    /**
     * @inheritDoc
     */
    public function testConstructWithId()
    {
        $id = 3;
        $numero = 93;
        $utilisateurActeur = 'tintin';

        $entite = $this->newTestedInstance([
            'id' => $id,
            'numeroPeriode' => $numero,
            'utilisateurActeur' => $utilisateurActeur,
            'utilisateurObjet' => 'milou',
            'etat' => 'haddock',
            'commentaire' => 'moulinsart',
            'date' => '43',
        ]);

        $this->assertConstructWithId($entite, $id);
        $this->integer($entite->getNumeroPeriode())->isIdenticalTo($numero);
        $this->string($entite->getUtilisateurActeur())->isIdenticalTo($utilisateurActeur);
    }

    /**
     * @inheritDoc
     */
    public function testConstructWithoutId()
    {
        $entite = $this->newTestedInstance(['']);

        $this->variable($entite->getId())->isNull();
    }

    /**
     * Teste la méthode populate
     */
    public function testPopulate()
    {
        $this->newTestedInstance([]);

        $this->exception(function () {
            $this->testedInstance->populate([]);
        })->isInstanceOf(\RuntimeException::class);
    }

    /**
     * @inheritDoc
     */
    public function testReset()
    {
        $entite = $this->newTestedInstance(['id' => 3, 'name' => 'name', 'status' => 'status']);

        $this->assertReset($entite);
    }
}
