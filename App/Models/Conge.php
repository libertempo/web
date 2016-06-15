<?php
namespace App\Models;

/**
 * Modèle d'un congé
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
class Conge
{
    /**
     * Constantes de statut
     * @var int
     */
    const STATUT_DEMANDE = 'demande';

    /**
     * Le valide est pour la première validation, le « ok » pour la seconde
     * @var int
     */
    const STATUT_VALIDE  = 'valid';
    const STATUT_OK      = 'ok';
    const STATUT_REFUS   = 'refus';
    const STATUT_ANNUL   = 'annul';

    /**
     * Retourne les options de select des statuts
     *
     * @return array
     */
    public static function getOptionsStatuts()
    {
        $statuts = [
            static::STATUT_DEMANDE,
            static::STATUT_VALIDE,
            static::STATUT_OK,
            static::STATUT_REFUS,
            static::STATUT_ANNUL
        ];
        $options = [];
        foreach ($statuts as $value) {
            $options[$value] = static::statusText($value);
        }

        return $options;
    }

    /**
     * Affiche le statut en format texte
     *
     * @param int $status
     *
     * @return string
     */
    public static function statusText($status)
    {
        switch ($status) {
            case static::STATUT_DEMANDE:
                $text = 'statut_demande';
                break;
            case static::STATUT_VALIDE:
                $text = 'statut_valide';
                break;
            case static::STATUT_OK:
                $text = 'statut_ok';
                break;
            case static::STATUT_REFUS:
                $text = 'statut_refus';
                break;
            case static::STATUT_ANNUL:
                $text = 'statut_annul';
                break;

            default:
                $text = 'statut_inconnu';
                break;
        }

        return _($text);
    }
}
