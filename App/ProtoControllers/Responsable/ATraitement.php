<?php
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
     * Traite les demandes
     *
     * @param array  $put
     * @param string $resp
     * @param string $notice
     * @param array $errors
     *
     * @return int
     */
    abstract public function put(array $put, $resp, &$notice, array &$errors);


    /**
     * Vérifie et traite une demande en tant que responsable
     *
     * @param array $infoDemande
     * @param int $statut
     * @param array $put
     * @param array $errors
     *
     * @return int
     */
    abstract protected function putResponsable(array $infoDemande, $statut, array $put, array &$errors);

    /**
     * Vérifie et traite une demande en tant que grand responsable
     *
     * @param array $infoDemande
     * @param int $statut
     * @param array $put
     * @param array $errors
     *
     * @return int
     */
    abstract protected function putGrandResponsable(array $infoDemande, $statut, array $put, array &$errors);

    /**
     * Retourne une liste d'id des demandes pour le responsable
     *
     * @param array $resp
     *
     * @return array $ids
     */
    abstract protected function getIdDemandesResponsable($resp);



    /**
     * Retourne la liste détaillée des demandes
     *
     * @param array $listId
     *
     * @return array $infoDemande
     *
     */
    abstract protected function getInfoDemandes(array $listId);

    /**
     * Traite les demandes
     *
     * @param array  $post
     * @param string $notice
     * @param array $errorLst
     *
     * @return int
     */
    public function post(array $post, &$notice, array &$errorLst)
    {
        if (!empty($post['_METHOD']) && $post['_METHOD'] == "PUT") {
            return $this->put($post, $_SESSION['userlogin'], $notice, $errorLst);
        } else {
            return NIL_INT;
        }
    }

    /**
     * Traitement d'une validation avec modification du solde
     *
     * @param int $demandeId
     *
     * @return int
     */
    protected function putValidationFinale($demandeId)
    {
        $sql = \includes\SQL::singleton();
        $sql->getPdoObj()->begin_transaction();

        $updateSolde = $this->updateSolde($demandeId);
        $updateStatut = $this->updateStatutValidationFinale($demandeId);
        if (0 < $updateSolde && 0 < $updateStatut) {
            $sql->getPdoObj()->commit();
        } else {
            $sql->getPdoObj()->rollback();
            return NIL_INT;
        }
        return $updateStatut;
    }

    /**
     * Retourne les demandes en cours d'un responsable
     *
     * @param string $resp login du responsable
     *
     * @return array $demandes
     */
    public function getDemandesResponsable($resp)
    {
        $demandesId = $this->getIdDemandesResponsable($resp);
        if (empty($demandesId)) {
            return [];
        }
        $demandes = $this->getInfoDemandes($demandesId);

        return $demandes;
    }

    /**
     * Retourne les demandes des utilisateurs responsable et absent
     *
     * @param string $resp
     * @return array
     */
    public function getDemandesResponsableAbsent($resp)
    {
        $demandesId = $this->getIdDemandesResponsableAbsent($resp);
        if (empty($demandesId)) {
            return [];
        }
        return $this->getInfoDemandes($demandesId);
    }

    /**
     * Retourne un tableau html des demandes à traiter
     *
     * @param array $demandes
     *
     * @return string
     */
    public function getFormDemandes(array $demandes)
    {
        $i=true;
        $Table='';

        foreach ($demandes as $demande) {
            $jour   = date('d/m/Y', $demande['debut']);
            $debut  = date('H\:i', $demande['debut']);
            $fin    = date('H\:i', $demande['fin']);
            $duree  = \App\Helpers\Formatter::timestamp2Duree($demande['duree']);
            $id = $demande['id_heure'];
            $infoUtilisateur = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($demande['login']);
            $solde = \App\Helpers\Formatter::timestamp2Duree($infoUtilisateur['u_heure_solde']);
            $Table .= '<tr class="'.($i?'i':'p').'">';
            $Table .= '<td><b>'.$infoUtilisateur['u_nom'].'</b><br>'.$infoUtilisateur['u_prenom'].'</td><td>'.$jour.'</td><td>'.$debut.'</td><td>'.$fin.'</td><td>'.$duree.'</td><td>'.$solde.'</td>';
            $Table .= '<input type="hidden" name="_METHOD" value="PUT" />';
            $Table .= '<td>' . $demande['comment'] . '</td>';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="1"></td>';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="2"></td>';
            $Table .= '<td><input type="radio" name="demande['.$id.']" value="NULL" checked></td>';

            /* Informations pour le positionnement du calendrier */
            $mois = new \DateTimeImmutable(date('Y-m', $demande['debut']) . '-01');
            $paramsCalendrier = [
                'mois' => $mois->format('Y-m'),
            ];
            $Table .= '<td><a href="' . ROOT_PATH . 'calendrier.php?' . http_build_query($paramsCalendrier) . '" title="' . _('consulter_calendrier_de_periode') . '"><i class="fa fa-lg fa-calendar" aria-hidden="true"></i></a></td>';
            $Table .= '<td><input class="form-control" type="text" name="comment_refus['.$id.']" size="20" maxlength="100"></td></tr>';

            $i = !$i;
        }

        return $Table;
    }

    /**
     * Retourne le détail des demandes à traiter en tant que grand responsable
     *
     * @param string $resp
     *
     * @return array $demandes
     */
    public function getDemandesGrandResponsable($resp)
    {
        $demandesId = $this->getIdDemandesGrandResponsable($resp);
        if (empty($demandesId)) {
            return [];
        }
        $demandes = $this->getInfoDemandes($demandesId);

        return $demandes;
    }

    /**
     * Verifie si la demande n'a pas déja été traité
     *
     * @param string $statutDb
     * @param string $statut
     *
     * @return bool
     */
    public function isDemandeTraitable($statut)
    {
        return ($statut != \App\Models\AHeure::STATUT_ANNUL || $statut != \App\Models\AHeure::STATUT_VALIDATION_FINALE || $statut != \App\Models\AHeure::STATUT_REFUS);
    }

    /**
     * Retourne le nombre de demande en cours d'un responsable
     *
     * @param $resp
     *
     * @return int
     */
    public function getNbDemandesATraiter($resp)
    {
        $demandesResp= $this->getIdDemandesResponsable($resp);
        $demandesGResp=  $this->getIdDemandesGrandResponsable($resp);

        return count($demandesResp) + count($demandesGResp);
    }
}
