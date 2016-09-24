<?php
namespace App\ProtoControllers;

/**
 *
 */
abstract class Ajax {

    public function __construct()
    {
        header('Content-type: application/json');
    }
}
