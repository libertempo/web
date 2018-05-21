<?php declare(strict_types = 1);
/*
 * Doit être importé après la création de $app. Ne créé rien.
 */
$app->get('/authentification', 'controller:get')->setName('authentification');
