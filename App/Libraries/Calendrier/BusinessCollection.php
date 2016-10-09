<?php
namespace App\Libraries\Calendrier;

use \CalendR\Event\EventInterface;

/**
 * Constructeur de la liste des événements selon des règles métiers
 *
 * Ne doit contacter que \App\Libraries\Calendrier\Collection\Ferie
 * Ne doit être contacté que par \App\ProtoControllers\Calendrier
 *
 * @TODO rendre testable en créant les modèles (et bannir le static)
 * @TODO trouver un meilleur nom pour représenter ce qu'il fait
 */
class BusinessCollection
{
    /**
     * @var EventInterface[] Liste des événements satisfaisant aux critères du métier
     */
    private $evenements;

    /**
     * @var \DateTime
     */
    private $dateDebut;

    /**
     * @var \DateTime
     */
    private $dateFin;

    /**
     * @var string Identifiant de l'utilisateur
     */
    private $utilisateur;

    /**
    * @var bool Si la gestion des groupes est demandée
     */
    private $isGroupesGeres;

    /**
    * @var int Groupe dont on veut voir les événements
     */
    private $groupeAConsulter;

    /**
     * Construit une collection d'événements en suivant des critères métiers
     *
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @param string $utilisateur Identifiant de l'observateur
     * @param bool $isGroupesGeres Si la gestion des groupes est demandée
     * @param int $groupeAConsulter Groupe dont on veut voir les événements
     */
    public function __construct(\DateTime $dateDebut, \DateTime $dateFin, $utilisateur, $isGroupesGeres, $groupeAConsulter = NIL_INT)
    {
        $this->dateDebut = clone $dateDebut;
        $this->dateFin = clone $dateFin;
        $this->utilisateur = (string) $utilisateur;
        $this->isGroupesGeres = (bool) $isGroupesGeres;
        $this->groupeAConsulter = (int) $groupeAConsulter;
    }

    /**
     * Retourne la liste des évenements satisfaisant aux critères de l'objet
     *
     * @return EventInterface[]
     */
    public function getListe()
    {
        /* Logique métier « application wide » */
        $utilisateursATrouver = [];
        if (null === $this->evenements) {
            if($this->isGroupesGeres) {
                $groupesVisiblesUtilisateur = \App\ProtoControllers\Utilisateur::getListeGroupesVisibles($this->utilisateur);
                $groupesATrouver = (NIL_INT !== $this->groupeAConsulter)
                    ? array_intersect($groupesVisiblesUtilisateur, [$this->groupeAConsulter])
                    : $groupesVisiblesUtilisateur;
                $utilisateursATrouver = \App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds($groupesATrouver);
            } else {
                $utilisateursATrouver = \App\ProtoControllers\Utilisateur::getListId();
            }

            $weekEnd = new \App\ProtoControllers\Ajax\WeekEnd();
            //$lstWeekEnd = $weekEnd->getListe($rechercheCommune);
            $this->evenements = array_merge(
                (new \App\Libraries\Calendrier\Collection\Ferie($this->dateDebut, $this->dateFin))->getListe(),
                //$lstWeekEnd
                []
            );

            // obtient les événements en fonction de groupe / rôle (les dates sont gérés par le calendrier)
            // en bouclant sur tous les types d'événements
        }
        return $this->evenements;
    }
}
