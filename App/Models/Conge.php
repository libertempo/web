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
     * Constantes de statut de demande
     * @var string
     */
    const STATUT_DEMANDE = 'demande';

    /**
     * Constante de statut de première validation
     * @var string
     */
    const STATUT_VALIDE = 'valid';

    /**
     * Constante de statut de seconde validation
     * @var string
     */
    const STATUT_OK = 'ok';

    /**
     * Constante de refus par l'un des validateurs
     * @var string
     */
    const STATUT_REFUS = 'refus';

    /**
     * Constante d'annulation par l'employé
     * @var string
     */
    const STATUT_ANNUL = 'annul';

    /**
     * Constante de congé ajouté par un responsable
     * @var string
     */
    const STATUT_AJOUT = 'ajout';

    /**
     * Constantes du formulaire de traitement des demandes
     * à terme, vu que c'est une logique métier qui dépend de plusieurs modèles, à mettre dans un service ou une spécification
     */
    const ACCEPTE       = '1';
    const REFUSE        = '2';

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
