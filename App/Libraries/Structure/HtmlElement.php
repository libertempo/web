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
}
