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
namespace App\ProtoControllers\Responsable;

/**
 * ProtoContrôleur abstrait d'heures, en attendant la migration vers le MVC REST
 *
 * TODO: On pourrait davantage faire de chose dans la classe abstraite, mais on est empêché par les log. Ça devrait être un sujet d'étude pour l'avenir
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina <wouldsmina@tuxfamily.org>
 */
abstract class ATraitement
{

    /**
     * Encapsule le comportement du formulaire de traitement des demandes d'heures
     *
     * @return string
     * @access public
     */
    abstract public function getForm();

    /**
     * Traite les demandes d'heures
     *
     * @param array  $put
     * @param string $user
     *
     * @return int
     */
    abstract protected function put(array $put, $resp, &$notice, array &$errorLst);

    /**
     * Liste des demandes
     *
     * @return string
     */
    abstract public function getDemandes($resp);

    /**
     * Retourne une liste d'id des demandes pour le responsable
     *
     * @param array $resp
     *
     * @return array
     */
    abstract protected function getDemandesRespId($resp);

    /**
     * Retourne une liste d'heures
     *
     * @param array $listId
     *
     * @return array
     * 
     * utiliser celui dans Heure/Additionnelle?
     */
    abstract protected function getListeSQL(array $listId);

    /**
     * Traite la demande/modification/suppression
     *
     * @param array  $post
     * @param string $notice
     *
     * @return int
     */
    protected function post(array $post, &$notice, array &$errorLst)
    {
        if (!empty($post['_METHOD']) && $post['_METHOD'] == "PUT") {
            $demandeTraitable = array_intersect($post['demande'], $this->getDemandesRespId($_SESSION['userlogin']));
            return $this->put($post, $_SESSION['userlogin'], $notice, $errorLst);
        } else {
            return NIL_INT;
        }
    }

    /**
     * Retourne le nom d'un utilisateur
     *
     * @param string $login
     *
     * @return string
     */
    public function getNom($login)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT u_nom FROM conges_users WHERE u_login = \''.$login.'\'';
        $query = $sql->query($req);
        $nom = $query->fetch_array()[0];

        return $nom;
    }

    /**
     * Retourne le prenom d'un utilisateur
     *
     * @param string $login
     *
     * @return string
     */
    public function getPrenom($login)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT u_prenom FROM conges_users WHERE u_login = \''.$login.'\'';
        $query = $sql->query($req);
        $nom = $query->fetch_array()[0];

        return $nom;
    }
    
    /**
     * Retourne le solde d'heure d'un utilisateur
     *
     * @param string $login
     *
     * @return int
     */
    public function getSoldeHeure($login)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT u_heure_solde FROM conges_users WHERE u_login = \''.$login.'\'';
        $query = $sql->query($req);
        $solde = $query->fetch_array()[0];

        return $solde;
    }

    /**
     * Retourne l'id des groupes d'un responsable donné
     *
     * @param string $resp
     *
     * @return array
     */
    public function getGroupeRespId($resp)
    {
        $ids = [];

        $sql = \includes\SQL::singleton();
        $req = 'SELECT gr_gid AS id FROM `conges_groupe_resp` WHERE gr_login =\''.$resp.'\'';
        $res = $sql->query($req);

        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * Retourne l'id des groupes d'un grand responsable donné
     *
     * @param string $gresp
     *
     * @return array
     */
    public function getGroupeGrandRespId($gresp)
    {
         $sql = \includes\SQL::singleton();
         $req = 'SELECT ggr_gid AS id FROM `conges_groupe_grd_resp` WHERE gr_login =\''.$gresp.'\'';
         $res = $sql->query($req);

         while ($data = $res->fetch_array()) {
             $ids[] = (int) $data['id'];
         }

         return $ids;
    }

    /**
     * Retourne le login des membres d'un ou plusieurs groupes
     *
     * @param array $groupes
     *
     * @return array
     */
    public function getUsersGroupe(array $groupes)
    {
         $sql = \includes\SQL::singleton();
         $req = 'SELECT gu_login FROM `conges_groupe_users` WHERE gu_gid IN (' . implode(',', $groupes) . ')';
         $res = $sql->query($req);

         while ($data = $res->fetch_array()) {
             $users[] = $data['gu_login'];
         }

         return $users;
    }
}