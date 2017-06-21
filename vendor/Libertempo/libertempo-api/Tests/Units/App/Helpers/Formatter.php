<?php
namespace Tests\Units\App\Helpers;

use \App\Helpers\Formatter as _Formatter;

/**
 * Test des petits utilitaires de l'application
 */
final class Formatter extends \Atoum
{
    /**
     * Teste la mise en StudlyCaps d'un mot en snake_case
     */
    public function testGetStudlyCapFromSnakeStudly()
    {
        $mot = 'snake_case_long';

        $studly = _Formatter::getStudlyCapsFromSnake($mot);

        $this->string($studly)->isIdenticalTo('SnakeCaseLong');
    }

    /**
     * Teste la mise en StudlyCaps d'un mot en snake_case final
     */
    public function testGetStudlyCapFromSnakeSnakeFinal()
    {
        $mot = 'snake_case_long_';

        $studly = _Formatter::getStudlyCapsFromSnake($mot);

        $this->string($studly)->isIdenticalTo('SnakeCaseLong');
    }

    /**
     * Teste la mise en StudlyCaps d'un mot en snake_case
     */
    public function testGetStudlyCapFromSnakeSnake()
    {
        $mot = 'snake_case_long';

        $studly = _Formatter::getStudlyCapsFromSnake($mot);

        $this->string($studly)->isIdenticalTo('SnakeCaseLong');
    }

    /**
     * Teste la mise en StudlyCaps d'un seul mot
     */
    public function testGetStudlyCapFromSnakeOneWord()
    {
        $mot = 'mot';

        $studly = _Formatter::getStudlyCapsFromSnake($mot);

        $this->string($studly)->isIdenticalTo('Mot');
    }

    /**
     * Teste la mise au singulier d'une phrase avec « s »  final
     */
    public function testGetSingularTermWithFinalS()
    {
        $term = 'Phrase avec s';

        $singular = _Formatter::getSingularTerm($term);

        $this->string($singular)->isIdenticalTo('Phrase avec ');
    }

    /**
     * Teste la mise au singulier d'une phrase avec « x »  final
     */
    public function testGetSingularTermWithFinalX()
    {
        $term = 'Phrase avec x';

        $singular = _Formatter::getSingularTerm($term);

        $this->string($singular)->isIdenticalTo('Phrase avec ');
    }

    /**
     * Teste la mise au singulier d'une phrase sans « s » final
     */
    public function testGetSingulaTermWithoutFinalS()
    {
        $term = 'Phrase sans esse';

        $singular = _Formatter::getSingularTerm($term);

        $this->string($singular)->isIdenticalTo($term);
    }
}
