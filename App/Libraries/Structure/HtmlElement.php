<?php
namespace App\Libraries\Structure;

use \App\Libraries\Interfaces;

/**
 * Élément html
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
abstract class HtmlElement implements Interfaces\IHtmlElement
{
    /**
     * Classes applicables sur l'élément
     *
     * @var array
     *
     * @access protected
     */
    protected $classes = [];

    /**
     * Attributs divers de l'élément
     *
     * @var array
     *
     * @access protected
     */
    protected $attributes = [];

    /**
     * Id unique de l'élément html
     *
     * @var string
     *
     * @access protected
     */
    protected $id = '';

    /**
     * @inheritdoc
     * @see Interfaces\IHtmlElement
     */
    public function addClass($class)
    {
        if (!in_array($class, $this->classes)) {
            $this->classes[] = $class;
        }
    }

    /**
     * @inheritdoc
     * @see Interfaces\IHtmlElement
     */
    public function addClasses(array $classes)
    {
        foreach ($classes as $class) {
            $this->addClass($class);
        }
    }

    /**
     * @inheritdoc
     * @see Interfaces\IHtmlElement
     */
    public function addAttribute($name, $value)
    {
        if (!isset($this->attributes[$name])) {
            $this->attributes[$name] = $value;
        }
    }

    public function addAttributes(array $list)
    {
        foreach ($list as $name => $value) {
            $this->addAttribute($name, $value);
        }
    }

    /**
     * @inheritdoc
     * @see Interfaces\IHtmlElement
     */
    public function getId()
    {
        if ('' === $this->id) {
            $this->id = uniqid();
        }
        return $this->id;
    }

    /**
     * @inheritdoc
     * @see Interfaces\IHtmlElement
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     * @see Interfaces\IRenderable
     */
    public abstract function render();

    /**
     * Render les classes de l'élément
     *
     * @access protected
     * @since  1.9
     * @return void
     */
    protected function renderClasses()
    {
        if (!empty($this->classes)) {
            echo ' class="' . implode(' ', $this->classes) . '"';
        }
    }

    /**
     * Render les attributs quelconques de l'élément
     *
     * @access protected
     * @since  1.9
     * @return void
     * @deprecated Ne devrait pas être utilisé dans les nouveaux codes
     */
    protected function renderAttributes()
    {
        if (!empty($this->attributes)) {
            foreach ($this->attributes as $name => $value) {
                echo ' ' . $name . '="' . $value . '"';
            }
        }
    }
}
