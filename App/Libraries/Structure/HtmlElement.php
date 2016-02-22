<?php
namespace App\Libraries\Structure;

/**
 * Élément html
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
abstract class HtmlElement implements \App\Libraries\Interfaces\IHtmlElement
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
     * Id unique de l'élément html
     *
     * @var string
     *
     * @access protected
     */
    protected $id = '';

    /**
     * @inheritdoc
     */
    public function addClass($class)
    {
        if (!in_array($class, $this->classes)) {
            $this->classes[] = $class;
        }
    }

    /**
     * @inheritdoc
     */
    public function addClasses(array $classes)
    {
        foreach ($classes as $class) {
            $this->addClass($class);
        }
    }

    /**
     * @inheritdoc
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
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
