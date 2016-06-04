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
namespace App\ProtoControllers;

/**
 * ProtoContrôleur abstrait d'heures, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
abstract class Heure
{
    /**
     * Encapsule le comportement du formulaire d'édition d'heures
     *
     * @param int $id
     *
     * @return string
     * @access public
     */
    abstract public function getForm($id = NIL_INT);

    /**
     * Traite la demande/modification/suppression
     *
     * @param array $post
     * @param array &$errorsLst
     *
     * @return int
     */
    protected function post(array $post, array &$errorsLst)
    {
        if (!empty($post['_METHOD'])) {
            switch ($post['_METHOD']) {
                case 'DELETE':
                    return $this->delete($post['id_heure'], $_SESSION['userlogin'], $errorsLst);
                    break;
                case 'PUT':
                    return $this->put($post, $errorsLst, $_SESSION['userlogin']);
                    break;
            }
        } else {
            if (!$this->hasErreurs($post, $errorsLst)) {
                $id = $this->insert($post, $_SESSION['userlogin']);
                if (0 < $id) {

                    return $id;
                }
            }

            return NIL_INT;
        }
    }

    /**
     * Met à jour une demande d'heures
     *
     * @param array  $put
     * @param array  &$errorsLst
     * @param string $user
     *
     * @return int
     */
    abstract protected function put(array $put, array &$errorsLst, $user);

    /**
     * Contrôle l'éligibilité d'une demande d'heures
     *
     * @param array  $post
     * @param array  &$errorsLst
     * @param int    $id
     *
     * @return bool True s'il y a des erreurs
     */
    protected function hasErreurs(array $post, array &$errorsLst, $id = NIL_INT)
    {
        $localErrors = [];

        /* Syntaxique : champs requis et format */
        if (empty($post['jour'])) {
            $localErrors['Jour'] = _('champ_necessaire');
        }
        if (empty($post['debut_heure'])) {
            $localErrors['Heure de début'] = _('champ_necessaire');
        } elseif (!\App\Helpers\Formatter::isHourFormat($post['debut_heure'])) {
            $localErrors['Heure de début'] = _('Format_heure_incorrect');
        }
        if (empty($post['fin_heure'])) {
            $localErrors['Heure de fin'] = _('champ_necessaire');
        } elseif (!\App\Helpers\Formatter::isHourFormat($post['fin_heure'])) {
            $localErrors['Heure de fin'] = _('Format_heure_incorrect');
        }
        if (!empty($localErrors)) {
            $errorsLst = array_merge($errorsLst, $localErrors);

            return empty($localErrors);
        }

        /* Sémantique : sens de prise d'heure */
        if (NIL_INT !== strnatcmp($post['debut_heure'], $post['fin_heure'])) {
            $localErrors['Heure de début / Heure de fin'] = _('verif_saisie_erreur_heure_fin_avant_debut');
        }
        if ($this->isChevauchement($post['jour'], $post['debut_heure'], $post['fin_heure'], $id, $_SESSION['userlogin'])) {
            $localErrors['Cohérence'] = _('Chevauchement_heure_avec_existant');
        }

        $errorsLst = array_merge($errorsLst, $localErrors);

        return !empty($localErrors);
    }

    /**
     * Liste des heures
     *
     * @return string
     */
    abstract public function getListe();

    /*
     * SQL
     */

    /**
     * Vérifie le chevauchement entre les heures demandées et l'existant
     *
     * @param string $jour
     * @param string $heureDebut
     * @param string $heureFin
     * @param int    $id
     * @param string $user
     *
     * @return bool
     */
    abstract protected function isChevauchement($jour, $heureDebut, $heureFin, $id, $user);

    /**
     * Ajoute une demande d'heures dans la BDD
     *
     * @param array  $post
     * @param string $user
     *
     * @return int
     */
    abstract protected function insert(array $post, $user);

    /**
     * Met à jour une demande d'heures dans la BDD
     *
     * @param array  $put
     * @param string $user
     * @param int    $id
     *
     * @return int
     */
    abstract protected function update(array $put, $user, $id);

    /**
     * Compte la vraie durée entre le début et la fin
     *
     * @param int $debut
     * @param int $fin
     *
     * @return int
     */
    abstract protected function countDuree($debut, $fin);
}
