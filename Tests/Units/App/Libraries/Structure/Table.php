<?php
namespace Tests\Units\App\Libraries\Structure;

use \App\Libraries\Structure\Table as _Table;

/**
 * Classe de tests des tables html
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see    \App\Libraries\Structure\Table
 */
class Table extends \Tests\Units\TestUnit
{
    /**
     * Test du render d'une table avec un fils non renderable
     *
     * @return void
     * @access public
     * @since 1.9
     */
    public function testRenderWithoutRenderable()
    {
        $table = new _Table();
        $table->addChild('Child');

        $this->output(function () use ($table) {
            $table->render();
        })->contains('Child');
    }

    /**
     * Test du render d'une table avec un fils renderable
     *
     * @return void
     * @access public
     * @since 1.9
     */
    public function testRenderWithRenderable()
    {
        $table = new _Table();
        $child = new \Mock\App\Libraries\Structure\Table();
        $table->addChild($child);

        $this->output(function () use ($table, $child) {
            $this->when($table->render())
                ->mock($child)
                    ->call('render')
                        ->once();
        });
    }
}
