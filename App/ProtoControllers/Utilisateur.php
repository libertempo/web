<?php
namespace App\ProtoControllers;

/**
 * ProtoContrôleur d'utilisateur, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina <wouldsmina@tuxfamily.org>
 */
class Utilisateur
{
    /*
     * SQL
     */

    public static function getListId($activeSeul = false, $withAdmin = false)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT u_login
                FROM conges_users';
        if(!$withAdmin){
            $req .= ' WHERE u_login != "admin"';
        }
        if($activeSeul){
            $req .= ' AND u_is_active = "Y"';
        }
        $result = $sql->query($req);

        $users = [];
        while ($data = $result->fetch_array()) {
            $users[] = $data['u_login'];
        }

        return $users;
    }

    /**
     * Retourne la liste des groupes visibles par un utilisateur
     *
     * @param string $utilisateur
     *
     * @return array
     * @todo À déporter dans un objet droit associé au modèle utilisateur
     */
    public static function getListeGroupesVisibles($utilisateur)
    {
        $groupesVisibles = [];
        if (\App\ProtoControllers\Utilisateur::isRH($utilisateur)
            || \App\ProtoControllers\Utilisateur::isAdmin($utilisateur)
        ) {
            $groupesVisibles = \App\ProtoControllers\Groupe::getListeId(\includes\SQL::singleton());
        } elseif (\App\ProtoControllers\Utilisateur::isResponsable($utilisateur)) {
            $groupesResponsable = \App\ProtoControllers\Responsable::getIdGroupeResp($utilisateur);
            $groupesGrandResponsable = \App\ProtoControllers\Responsable::getIdGroupeGrandResponsable($utilisateur);
            $groupesEmploye = \App\ProtoControllers\Utilisateur::getGroupesId($utilisateur);
            $groupesVisibles = $groupesResponsable + $groupesGrandResponsable + $groupesEmploye;
        } else {
            $groupesVisibles = \App\ProtoControllers\Utilisateur::getGroupesId($utilisateur);
        }

        return $groupesVisibles;
    }

    /**
     * Retourne si un utilisateur a le rôle de RH
     *
     * @param string $utilisateur
     *
     * @return bool
     * @todo On devrait pouvoir factoriser les isX via getRole() mais actuellement un utilisateur peut avoir plusieurs rôle en simultanée. Il faudra empêcher ça, et ainsi faire pointer les isX() sur getRole(). Et mettre des constantes
     */
    public static function isRH($utilisateur)
    {
        $donneesUtilisateur = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($utilisateur);

        return (!empty($donneesUtilisateur))
            ? 'Y' === $donneesUtilisateur['u_is_hr']
            : false;
    }

    /**
     * Retourne si un utilisateur a le rôle d'admin
     *
     * @param string $utilisateur
     *
     * @return bool
     * @todo On devrait pouvoir factoriser les isX via getRole() mais actuellement un utilisateur peut avoir plusieurs rôle en simultanée. Il faudra empêcher ça, et ainsi faire pointer les isX() sur getRole(). Et mettre des constantes
     */
    public static function isAdmin($utilisateur)
    {
        $donneesUtilisateur = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($utilisateur);

        return (!empty($donneesUtilisateur))
            ? 'Y' === $donneesUtilisateur['u_is_admin']
            : false;
    }

    /**
     * Retourne si un utilisateur a le rôle de responsable
     *
     * @param string $utilisateur
     *
     * @return bool
     * @todo On devrait pouvoir factoriser les isX via getRole() mais actuellement un utilisateur peut avoir plusieurs rôle en simultanée. Il faudra empêcher ça, et ainsi faire pointer les isX() sur getRole(). Et mettre des constantes
     */
    public static function isResponsable($utilisateur)
    {
        $donneesUtilisateur = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($utilisateur);

        return (!empty($donneesUtilisateur))
            ? 'Y' === $donneesUtilisateur['u_is_resp']
            : false;
    }

    /**
     * Retourne les informations d'un utilisateur
     *
     * @param string $login
     *
     * @return string $donnees
     */
    public static function getDonneesUtilisateur($login)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_users
                WHERE u_login = \''.  \includes\SQL::quote($login).'\'';
        $query = $sql->query($req);
        $donnees = $query->fetch_array();

        return $donnees;
    }

    /**
     * Retourne la liste des utilisateurs associés à un planning
     *
     * @param int $planningId
     *
     * @return array
     */
    public static function getListByPlanning($planningId)
    {
        $planningId = (int) $planningId;
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_users
                WHERE planning_id = ' . $planningId . '
                    AND u_login <> "admin"
                ORDER BY u_login';

        return $sql->query($req)->fetch_all(\MYSQLI_ASSOC);
    }

    /**
     * Retourne les identifiants de groupe auquel un utilisateur appartient
     *
     * @param string $user
     *
     * @return array $ids
     */
    public static function getGroupesId($user)
    {
        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT gu_gid AS id
                    FROM conges_groupe_users
                    WHERE gu_login ="'.\includes\SQL::quote($user).'"';
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * Retourne le solde de conges (selon le type) d'un utilisateur
     *
     * @param string $login
     * @param int $typeId
     *
     * @return int $solde
     */
    public static function getSoldeconge($login, $typeId)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT su_solde FROM conges_solde_user WHERE su_login = \'' . \includes\SQL::quote($login) . '\'
                AND su_abs_id ='. (int) $typeId;
        $query = $sql->query($req);
        $solde = $query->fetch_array()[0];

        return $solde;
    }

     /**
     * Retourne le solde d'heure au format timestamp d'un utilisateur
     *
     * @param string $login
     * @param int $typeId
     *
     * @return int $timestamp
     */
    public static function getSoldeHeure($login)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT u_heure_solde FROM conges_users WHERE u_login = \'' . \includes\SQL::quote($login) . '\'';
        $query = $sql->query($req);
        $timestamp = $query->fetch_array()[0];

        return $timestamp;
    }

    /**
     * Vérifie si l'utilisateur a des sorties en cours
     *
     * @param string $login
     *
     * @return bool
     */
    public static function hasSortiesEnCours($login)
    {
        return static::hasCongesEnCours($login)
            || static::hasHeureReposEnCours($login)
            || static::hasHeureAdditionnelleEnCours($login)
        ;
    }

    /**
     * Récupère l'adresse email de l'utilisateur
     *
     * @todo En attendant l'objet ldap utilisation de find_email_adress_for_user
     *
     * @param string $login
     * @return string $mail
     */
    public static function getEmailUtilisateur($login)  {
        require_once ROOT_PATH.'fonctions_conges.php';
        return find_email_adress_for_user($login)[1];
    }

    /**
     * Vérifie si l'utilisateur a des congés en cours
     *
     * @param string $login
     *
     * @return bool
     */
    public static function hasCongesEnCours($login)
    {
        $params = ['p_login' => $login, 'p_etat' => \App\Models\Conge::STATUT_DEMANDE];
        $conge = new \App\ProtoControllers\Employe\Conge();

        return $conge->exists($params);
    }

    /**
     * Vérifie si l'utilisateur a des heures de repos en cours
     *
     * @param string $login
     *
     * @return bool
     */
    public static function hasHeureReposEnCours($login)
    {
        $params = ['login' => $login, 'statut' => \App\Models\Heure\Repos::STATUT_DEMANDE];
        $repos = new \App\ProtoControllers\Employe\Heure\Repos();

        return $repos->exists($params);
    }

    /**
     * Vérifie si l'utilisateur a des heures additionnelles en cours
     *
     * @param string $login
     *
     * @return bool
     */
    public static function hasHeureAdditionnelleEnCours($login)
    {
        $params = ['login' => $login, 'statut' => \App\Models\Heure\Additionnelle::STATUT_DEMANDE];
        $additionnelle = new \App\ProtoControllers\Employe\Heure\Additionnelle();

        return $additionnelle->exists($params);
    }

    /**
     * Retourne le nom formaté de l'employé
     *
     * @param string $prenom
     * @param string $nom
     * @param bool $initialPrenomSeulement
     *
     * @return string
     */
    public static function getNomComplet($prenom, $nom, $initialPrenomSeulement = false) {
        $prenom = ucfirst($prenom);
        $nom = ucfirst($nom);
        if ($initialPrenomSeulement && 0 < strlen($prenom)) {
            $prenom = $prenom[0] . '.';
        }

        return $prenom . ' ' . $nom;
    }

    /**
     * Supprime les associations des utilisateurs à ce planning
     *
     * @param int $idPlanning
     * @param array $utilisateurs Liste des utilisateurs dont on veut supprimer les associations
     *
     * @return bool
     */
    public static function deleteListAssociationPlanning($idPlanning, array $utilisateurs = [])
    {
        $where[] = 'planning_id = ' . (int) $idPlanning;
        $sql = \includes\SQL::singleton();
        if (!empty($utilisateurs)) {
            $utilisateurs = array_map([$sql, 'quote'], $utilisateurs);
            $where[] = 'u_login IN ("' . implode('","', $utilisateurs) . '")';
        }
        $req = 'UPDATE conges_users
            SET planning_id = 0
            WHERE ' . implode(' AND ', $where);
        $sql->query($req);

        return (bool) $sql->affected_rows;
    }

    /**
     * Définit une association massive entre les utilisateurs et le planning
     *
     * @param array $utilisateurListe
     * @param int $idPlanning
     *
     * @return int Nombre d'utilisateurs affectés
     */
    public static function putListAssociationPlanning(array $utilisateursListe, $idPlanning)
    {
        $sql = \includes\SQL::singleton();
        $utilisateursListe = array_map([$sql, 'quote'], $utilisateursListe);
        $req = 'UPDATE conges_users
            SET planning_id = ' . (int) $idPlanning  . '
            WHERE u_login IN ("' . implode('","', $utilisateursListe) . '")';
        $sql->query($req);

        return (bool) $sql->affected_rows;
    }
}
