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

class DemandeHeure
{


    /**
     * Vérifie qu'une demande est éditable
     *
     * @param int $id
     *
     * @return bool
     */
    public static function isEditable($id)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (
                    SELECT id_heure
                    FROM conges_heure_periode
                    WHERE id_heure = ' . (int) $id . '
                    AND statut = ' . \App\Models\HeureRecuperation::STATUT_DEMANDE . '
                )';
        $query = $sql->query($req);

        return 0 < (int) $query->fetch_array()[0];
    }

    /**
     * Vérifie que l'utilisateur peut accéder à une demande
     *
     * @param int $id
     * @param string $user
     *
     * @return bool
     */
    public static function isUserOwner($id,$user)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT login
                FROM conges_heure_periode
                WHERE id_heure = ' . (int) $id;
        $query = $sql->query($req);
        $login = $query->fetch_assoc();
        return $user === $login['login'];
    }
}
