<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2015 (Wouldsmina)
Copyright (C) 2015 (Prytoegrian)
Copyright (C) 2005 (cedric chauvineau)
Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les
termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE,
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation
dans un but spécifique. Reportez-vous à la Licence Publique Générale GNU pour plus de détails.
Vous devez avoir reçu une copie de la Licence Publique Générale GNU en même temps
que ce programme ; si ce n'est pas le cas, écrivez à la Free Software Foundation,
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*************************************************************************************************
This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either
version 2 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*************************************************************************************************/
namespace App\Models;
/**
 * Modèle d'heures
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
abstract class AHeure
{
    /**
     * Constantes de statut de demande
     * @var string
     */
    const STATUT_DEMANDE = 1;

    /**
     * Constante de statut de première validation
     * @var string
     */
    const STATUT_VALIDE  = 2;

    /**
     * Constante de statut de seconde validation
     * @var string
     */
    const STATUT_OK      = 3;

    /**
     * Constante de refus par l'un des validateurs
     * @var string
     */
    const STATUT_REFUS   = 4;

    /**
     * Constante d'annulation par l'employé
     * @var string
     */
    const STATUT_ANNUL   = 5;


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
