<?php
namespace App\Libraries\Interfaces;

/**
 * Décrit le contrat d'une structure pouvant avoir des enfants
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
interface IHeritable
{
    /**
     * Ajoute un enfant à la liste
     *
     * @param mixed $child
     *
     * @return void
     * @since 1.9
     */
    public function addChild($child);

    /**
     * Ajoute une liste d'enfants à la liste
     *
     * @param array $children
     *
     * @return void
     * @since 1.9
     */
    public function addChildren(array $children);
}
