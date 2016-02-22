<?php
namespace App\Libraries\Structure;


use \App\Libraries\Interfaces;

/**
 * Table html
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
class Table extends HtmlElement implements Interfaces\IRenderable, Interfaces\IHeritable
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
     */
    public function render()
    {
        echo '<table id="' .  $this->getId() . '" class="' . implode(' ', $this->classes) . '">';
        foreach ($this->children as $child) {
            if ($child instanceOf Interfaces\IRenderable) {
                $child->render();
            } else {
                echo $child;
            }
        }
        echo '</table>';
    }

    /**
     * @inheritdoc
     */
    public function addChild($child)
    {
        $this->children[] = $child;
    }

    /**
     * @inheritdoc
     */
    public function addChildren(array $children)
    {
        foreach ($children as $child) {
            $this->addChild($child);
        }
    }
}
