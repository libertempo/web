<?php
namespace App\Libraries\Structure;

use \App\Libraries\Interfaces;

/**
 * Table html
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see    \Tests\Units\App\Libraries\Structure\Table
 */
class Table extends HtmlElement implements Interfaces\IHeritable
{
    /**
     * Liste d'enfants de la table
     *
     * @var array
     *
     * @access protected
     */
    private $children = [];

    /**
     * @inheritdoc
     * @see Interfaces\IRenderable
     */
    public function render()
    {
        echo '<table id="' .  $this->getId() . '"';
        $this->renderClasses();
        $this->renderAttributes();
        echo '>';
        foreach ($this->children as $child) {
            if ($child instanceOf Interfaces\IRenderable) {
                $child->render();
            } else {
                /* 1.9 TODO: On peut ajouter n'importe quel fils quitte à faire n'importe quoi,
                c'est à but transitoire. À terme, il sera nécessaire de n'autoriser
                que ce qui peut être fils de <table> (thead, tbody, tfoot, tr) */
                echo $child;
            }
        }
        echo '</table>';
    }

    /**
     * @inheritdoc
     * @see Interfaces\IHeritable
     */
    public function addChild($child)
    {
        $this->children[] = $child;
    }

    /**
     * @inheritdoc
     * @see Interfaces\IHeritable
     */
    public function addChildren(array $children)
    {
        foreach ($children as $child) {
            $this->addChild($child);
        }
    }
}
