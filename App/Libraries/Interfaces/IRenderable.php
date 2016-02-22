<?php
namespace App\Libraries\Interfaces;

/**
 * Décrit le contrat d'un élément renderable
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
interface IRenderable
{
    /**
     * Render l'élément
     *
     * @return void
     * @since 1.9
     */
    public function render();
}
