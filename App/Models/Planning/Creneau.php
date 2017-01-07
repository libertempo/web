<?php
namespace App\Models\Planning;

/**
 * Modèle de créneau de planning
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
class Creneau
{
    /**
     * @var int Types de semaines
     */
    const TYPE_SEMAINE_COMMUNE = 1;
    const TYPE_SEMAINE_IMPAIRE = 2;
    const TYPE_SEMAINE_PAIRE   = 3;

    /**
     * @var int Types d'heures
     */
    const TYPE_HEURE_DEBUT     = 1;
    const TYPE_HEURE_FIN       = 2;

    /**
     * @var int Types de périodes
     */
    const TYPE_PERIODE_MATIN      = 1;
    const TYPE_PERIODE_APRES_MIDI = 2;
    const TYPE_PERIODE_MATIN_APRES_MIDI = 3;

    public static function getListeTypePeriode()
    {
        return [
            static::TYPE_PERIODE_MATIN,
            static::TYPE_PERIODE_APRES_MIDI,
            static::TYPE_PERIODE_MATIN_APRES_MIDI,
        ];
    }
}
