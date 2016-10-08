<?php

/**
 * Interface des événements identifiables : nom, etc.
 *
 * Ne doit contacter personne
 * Ne doit être contacté que par \App\Libraries\Evenement\Commun
 */
interface IIdentifiable
{
    /**
     * Retourne le nom de l'événement
     *
     * @return string
     */
    public function getName();

    /**
     * Retourne le nom de l'événement
     *
     * @return string
     */
    public function getTitle();

    /**
     * Retourne la classe de l'événement (au sens Html)
     *
     * @return string
     */
    public function getClass();
}
