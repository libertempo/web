<?php
namespace App\Libraries\Interfaces;

/**
 * Décrit le contrat d'un élément Html
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
interface IHtmlElement extends IRenderable
{
    /**
     * Ajoute une classe à l'élément
     *
     * @param string $class
     *
     * @return void
     * @since 1.9
     */
    public function addClass($class);

    /**
     * Ajoute un tableau de classes à l'élément
     *
     * @param array $classes
     *
     * @return void
     * @since 1.9
     */
    public function addClasses(array $classes);

    /**
     * Ajoute un attribut quelconque à l'élément
     *
     *
     * @param string $name
     * @param string $value
     *
     * @return void
     * @since 1.9
     * @deprecated Ne devrait pas être utilisé dans les nouveaux codes
     */
    public function addAttribute($name, $value);

    /**
     * Ajoute une liste d'attributs quelconques à l'élément
     * @example ['nomAttr1' => 'val1', ...]
     *
     * @param array $list
     *
     * @return void
     * @since 1.9
     * @deprecated Ne devrait pas être utilisé dans les nouveaux codes
     */
    public function addAttributes(array $list);

    /**
     * Renvoie l'id unique de l'élément
     *
     * @return void
     * @since 1.9
     */
    public function getId();

    /**
     * Force l'id unique de l'élément
     *
     * @return void
     * @since 1.9
     */
    public function setId($id);
}
