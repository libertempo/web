<?php
namespace App\ProtoControllers\Employe;

use App\Models;
use App\Models\Planning\Creneau;

/**
 * ProtoContrôleur d'un congé, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
class Conge
{
    /**
     * Liste des congés
     *
     * @return string
     */
    public function getListe()
    {
        $return = '';
        $errorsLst = [];
        if ($_SESSION['config']['where_to_find_user_email'] == "ldap") {
            include_once CONFIG_PATH . 'config_ldap.php';
        }

        if (!empty($_POST) && !$this->isSearch($_POST)) {
            if (0 < (int) \utilisateur\Fonctions::postDemandeCongesHeure($_POST, $errorsLst)) {
                $return .= '<div class="alert alert-info">' . _('suppr_succes') . '</div>';
            }
        }
        // on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
        init_tab_jours_feries();

        if( $_SESSION['config']['user_saisie_demande'] || $_SESSION['config']['user_saisie_mission'] ) {
            $return .= '<a href="' . ROOT_PATH . 'utilisateur/user_index.php?onglet=nouvelle_absence" style="float:right" class="btn btn-success">' . _('divers_nouvelle_absence') . '</a>';
        }
        $return .= '<h1>' . _('user_liste_conge_titre') . '</h1>';

        if (!empty($_POST) && $this->isSearch($_POST)) {
            $champsRecherche = $_POST['search'];
            $champsSql = $this->transformChampsRecherche($_POST);
        } else {
            $champsRecherche = [];
            $champsSql = [];
        }
        $params = $champsSql + [
            'p_login' => $_SESSION['userlogin'],
        ]; // champs par défaut écrasés par posté
        $return .= $this->getFormulaireRecherche($champsRecherche);

        $table = new \App\Libraries\Structure\Table();
        $table->addClasses([
            'table',
            'table-hover',
            'table-responsive',
            'table-condensed',
            'table-striped',
        ]);
        $childTable = '<thead><tr><th>' . _('divers_debut_maj_1') . '</th><th>' . _('divers_fin_maj_1') . '</th><th>' . _('divers_type_maj_1') . '</th><th>' . _('divers_nb_jours_pris_maj_1') . '</th><th>Statut</th><th></th><th></th>';
        $childTable .= '</tr>';
        $childTable .= '</thead><tbody>';
        $listId = $this->getListeId($params);
        if (empty($listId)) {
            $colonnes = 8;
            $childTable .= '<tr><td colspan="' . $colonnes . '"><center>' . _('aucun_resultat') . '</center></td></tr>';
        } else {
            $i = true;
            $listeConges = $this->getListeSQL($listId);
            $interdictionModification = $_SESSION['config']['interdit_modif_demande'];
            $affichageDateTraitement = $_SESSION['config']['affiche_date_traitement'];
            foreach ($listeConges as $conges) {
                /** Dates demande / traitement */
                $dateDemande = '';
                $dateReponse = '';
                if ($affichageDateTraitement) {
                    if (!empty($conges["p_date_demande"])) {
                        list($date, $heure) = explode(' ', $conges["p_date_demande"]);
                        $dateDemande = '(' . \App\Helpers\Formatter::dateIso2Fr($date) . ' ' . $heure . ') ';
                    }
                    if (null != $conges["p_date_traitement"]) {
                        list($date, $heure) = explode(' ', $conges["p_date_traitement"]);
                        $dateReponse = '(' . \App\Helpers\Formatter::dateIso2Fr($date) . ' ' . $heure . ') ';
                    }
                }

                /** Messages complémentaires */
                if (!empty($conges["p_commentaire"])) {
                    $messageDemande = '> Demande ' . $dateDemande . ":\n" . $conges["p_commentaire"];
                } else {
                    $messageDemande = '';
                }
                if (!empty($conges["p_motif_refus"])) {
                    $messageReponse = '> Réponse ' . $dateReponse . ":\n" . $conges["p_motif_refus"];
                } else {
                    $messageReponse = '';
                }

                $demi_j_deb = ($conges["p_demi_jour_deb"] == "am") ? 'matin' : 'après-midi';
                $demi_j_fin = ($conges["p_demi_jour_fin"] == "am") ? 'matin' : 'après-midi';

                $childTable .= '<tr class="' . ($i ? 'i' : 'p') . '">';

                $childTable .= '<td class="histo">' . \App\Helpers\Formatter::dateIso2Fr($conges["p_date_deb"]) . ' <span class="demi">' . schars($demi_j_deb) . '</span></td>';
                $childTable .= '<td class="histo">' . \App\Helpers\Formatter::dateIso2Fr($conges["p_date_fin"]) . ' <span class="demi">' . schars($demi_j_fin) . '</span></td>';
                $childTable .= '<td class="histo">' . schars($conges["ta_libelle"]) . '</td>';
                $childTable .= '<td class="histo">' . affiche_decimal($conges["p_nb_jours"]) . '</td>';
                $childTable .= '<td>' . \App\Models\Conge::statusText($conges["p_etat"]) . '</td>';
                $childTable .= '<td class="histo">';
                if (!empty($messageDemande) || !empty($messageReponse)) {
                    $childTable .= '<i class="fa fa-comments" aria-hidden="true" title="' . $messageDemande . "\n\n" . $messageReponse . '"></i>';
                }
                $childTable .= '</td>';
                $childTable .= '<td class="histo">';

                $user_modif_demande = '<i class="fa fa-pencil disabled"></i>';
                $user_suppr_demande = '<i class="fa fa-times-circle disabled"></i>';

                // si on peut modifier une demande on defini le lien à afficher
                if ($conges["p_etat"] == \App\Models\Conge::STATUT_DEMANDE) {
                    if (!$interdictionModification) {
                        $user_modif_demande = '<a href="user_index.php?p_num=' . $conges['p_num'] . '&onglet=modif_demande"><i class="fa fa-pencil"></i></a>';
                    }
                    $user_suppr_demande = '<a href="user_index.php?p_num=' . $conges['p_num'] . '&onglet=suppr_demande"><i class="fa fa-times-circle"></i></a>';
                }

                if (!$interdictionModification) {
                    $childTable .= $user_modif_demande . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

                }
                $childTable .= ($user_suppr_demande) . '</td>' . "\n";
                $childTable .= '</tr>';
                $i = !$i;
            }
        }
        $childTable .= '</tbody>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();

        return $return;
    }

    /**
     * Y-a-t-il une recherche dans l'avion ?
     *
     * @param array $post
     *
     * @return bool
     */
    protected function isSearch(array $post)
    {
        return !empty($post['search']);
    }

    /**
     * Retourne le formulaire de recherche de la liste
     *
     * @param array $champs Champs de recherche (postés ou défaut)
     *
     * @return string
     */
    protected function getFormulaireRecherche(array $champs)
    {
        $form = '';
        $form = '<form method="post" action="" class="form-inline search" role="form">';
        $form .= '<div class="form-group"><label class="control-label col-md-4" for="statut">Statut&nbsp;:</label><div class="col-md-8"><select class="form-control" name="search[p_etat]" id="statut">';
        $form .= '<option value="all">' . _('tous') . '</option>';
        foreach (\App\Models\Conge::getOptionsStatuts() as $key => $value) {
            $selected = (isset($champs['p_etat']) && $key == $champs['p_etat'])
                ? 'selected="selected"'
                : '';
            $form .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
        }
        $form .= '</select></div></div>';
        $form .= '<div class="form-group "><label class="control-label col-md-4" for="type">Type&nbsp;:</label><div class="col-md-8"><select class="form-control" name="search[type]" id="type">';
        $form .= '<option value="all">' . _('tous') . '</option>';
        foreach (\utilisateur\Fonctions::getOptionsTypeConges() as $key => $value) {
            $selected = (isset($champs['type']) && $key == $champs['type'])
            ? 'selected="selected"'
            : '';
            $form .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
        }
        $form .= '</select></div></div>';
        $form .= '<div class="form-group"><label class="control-label col-md-4" for="annee">Année&nbsp;:</label><div class="col-md-8"><select class="form-control" name="search[annee]" id="sel1">';
        foreach (\utilisateur\Fonctions::getOptionsAnnees() as $key => $value) {
            $selected = (isset($champs['annee']) && $key == $champs['annee'])
            ? 'selected="selected"'
            : '';
            $form .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
        }
        $form .= '</select></div></div><div class="form-group"><div class="input-group"><button type="submit" class="btn btn-default"><i class="fa fa-search" aria-hidden="true"></i></button>&nbsp;<a href="' . ROOT_PATH . 'utilisateur/user_index.php?onglet=liste_conge" type="reset" class="btn btn-default">Reset</a></div></div></form>';

        return $form;
    }

    /**
     * Transforme les champs de recherche afin d'être compris par la bdd
     *
     * @param array $post
     *
     * @return array
     */
    protected function transformChampsRecherche(array $post)
    {
        $champs = [];
        $search = $post['search'];
        foreach ($search as $key => $value) {
            if ('annee' === $key) {
                $champs['dateDebut'] = ((int) $value) . '-01-01';
                $champs['dateFin'] = ((int) $value) . '-12-31';
            } else {
                if ($value !== "all") {
                    // si la valeur est différent de tout le paramètres est passé au champ pour la futur requête sql
                    $champs[$key] = $value;
                }
            }
        }

        return $champs;
    }

    /*
     * SQL
     */

    /**
     * Retourne une liste d'id de congés
     *
     * @param array $params Paramètres de recherche
     *
     * @return array
     */
    protected function getListeId(array $params)
    {
        $sql = \includes\SQL::singleton();
        if (!empty($params)) {
            $where = [];
            foreach ($params as $key => $value) {
                $value = $sql->quote($value);
                switch ($key) {
                    case 'dateDebut':
                        $where[] = 'p_date_deb >= "' . $value . '"';
                        break;
                    case 'dateFin':
                        $where[] = 'p_date_deb <= "' . $value . '"';
                        break;
                    case 'type':
                        $where[] = 'CTA.ta_short_libelle = "' . $value . '"';
                        break;
                    default:
                        $where[] = $key . ' = "' . $value . '"';
                        break;
                }
            }
        }
        $ids = [];
        $req = 'SELECT p_num AS id
                FROM conges_periode CP
                    INNER JOIN conges_type_absence CTA ON (CP.p_type = CTA.ta_id) '
                . ((!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '');
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * Retourne une liste de congés
     *
     * @param array $listId
     *
     * @return array
     */
    protected function getListeSQL(array $listId)
    {
        if (empty($listId)) {
            return [];
        }
        $sql = \includes\SQL::singleton();
        $req = 'SELECT CP.*, CTA.ta_libelle
                FROM conges_periode CP
                    INNER JOIN conges_type_absence CTA ON (CP.p_type = CTA.ta_id)
                WHERE p_num IN (' . implode(',', $listId) . ')
                ORDER BY p_date_deb DESC';

        return $sql->query($req)->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Retourne les demandes d'un employé
     *
     */
    public static function getIdDemandesUtilisateur($user)
    {

        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT p_num AS id
                FROM conges_periode
                WHERE p_login = \'' . $sql->quote($user) . '\'
                AND p_etat = \'' . \App\Models\Conge::STATUT_DEMANDE . '\'';
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * Vérifie l'existence de congé basée sur les critères fournis
     *
     * @param array $params
     *
     * @return bool
     * @TODO: à terme, à baser sur le getList()
     */
    public function exists(array $params)
    {
        $sql = \includes\SQL::singleton();

        $where = [];
        foreach ($params as $key => $value) {
            $where[] = $key . ' = "' . $sql->quote($value) . '"';
        }
        $req = 'SELECT EXISTS (
                    SELECT *
                    FROM conges_periode
                    WHERE ' . implode(' AND ', $where) . '
        )';

        return 0 < (int) $sql->query($req)->fetch_array()[0];
    }

    /**
     * Vérifie le chevauchement des conges demandés et les heures,
     * additionnelles comme repos
     *
     * @param string $user Login de l'utilisateur
     * @param string $dateDebut Date au format YYYY-mm-dd
     * @param int $typeCreneauDebut Type de période parmi Creneau::TYPE_PERIODE_*
     * @param string $dateFin Date au format YYYY-mm-dd
     * @param int $typeCreneauFin Type de période parmi Creneau::TYPE_PERIODE_*
     *
     * @return bool
     */
    public function isChevauchement($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin)
    {
        return $this->isChevauchementHeuresRepos($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin)
        || $this->isChevauchementHeuresAdditionnelles($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin);
    }

    /**
     * Vérifie le chevauchement des conges demandés et les heures de repos
     *
     * @param string $user Login de l'utilisateur
     * @param string $dateDebut Date au format YYYY-mm-dd
     * @param int $typeCreneauDebut Type de période parmi Creneau::TYPE_PERIODE_*
     * @param string $dateFin Date au format YYYY-mm-dd
     * @param int $typeCreneauFin Type de période parmi Creneau::TYPE_PERIODE_*
     *
     * @return bool
     */
    private function isChevauchementHeuresRepos($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin)
    {
        return $this->isChevauchementHeures($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin, 'heure_repos');
    }

    /**
     * Vérifie le chevauchement des conges demandés et les heures additionnelles
     *
     * @param string $user Login de l'utilisateur
     * @param string $dateDebut Date au format YYYY-mm-dd
     * @param int $typeCreneauDebut Type de période parmi Creneau::TYPE_PERIODE_*
     * @param string $dateFin Date au format YYYY-mm-dd
     * @param int $typeCreneauFin Type de période parmi Creneau::TYPE_PERIODE_*
     *
     * @return bool
     */
    private function isChevauchementHeuresAdditionnelles($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin)
    {
        return $this->isChevauchementHeures($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin, 'heure_additionnelle');
    }

    /**
     * Vérifie le chevauchement des conges demandés et les heures, selon son type
     *
     * @param string $user Login de l'utilisateur
     * @param string $dateDebut Date au format YYYY-mm-dd
     * @param int $typeCreneauDebut Type de période parmi Creneau::TYPE_PERIODE_*
     * @param string $dateFin Date au format YYYY-mm-dd
     * @param int $typeCreneauFin Type de période parmi Creneau::TYPE_PERIODE_*
     * @param string $typeHeure Heure de repos ou additionnelle
     *
     * @return bool
     */
    private function isChevauchementHeures($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin, $typeHeure)
    {
        $sql = \includes\SQL::singleton();
        $filtresDates[] = '(dateDebutHeure > "' . $dateDebut . '" AND dateDebutHeure < "' . $dateFin . '")';
        if (Creneau::TYPE_PERIODE_MATIN_APRES_MIDI === $typeCreneauDebut) {
            $filtresDates[] = '(dateDebutHeure = "' . $dateDebut . '")';
        } else {
            $filtresDates[] = '(dateDebutHeure = "' . $dateDebut . '" AND type_periode IN (' . $typeCreneauDebut . ',' . Creneau::TYPE_PERIODE_MATIN_APRES_MIDI . '))';
        }
        if (Creneau::TYPE_PERIODE_MATIN_APRES_MIDI === $typeCreneauFin) {
            $filtresDates[] = '(dateDebutHeure = "' . $dateFin . '")';
        } else {
            $filtresDates[] = '(dateDebutHeure = "' . $dateFin . '" AND type_periode IN (' . $typeCreneauFin . ',' . Creneau::TYPE_PERIODE_MATIN_APRES_MIDI . '))';
        }
        $etats = [
            Models\AHeure::STATUT_DEMANDE,
            Models\AHeure::STATUT_PREMIERE_VALIDATION,
            Models\AHeure::STATUT_VALIDATION_FINALE,
        ];

        $req = 'SELECT EXISTS (
            SELECT *
            FROM
                (SELECT *, DATE_FORMAT(FROM_UNIXTIME(debut), "%Y-%m-%d") AS dateDebutHeure
            FROM ' . $typeHeure . ') tmp
            WHERE statut IN ("' . implode('","', $etats) . '")
                AND login = "' . $sql->quote($user) . '"
                AND (' . implode(' OR ', $filtresDates) . ')
        )';
        $queryConges = $sql->query($req);

        return 0 < (int) $queryConges->fetch_array()[0];
    }
}
