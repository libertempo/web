<?php
namespace App\Libraries\Calendrier;

use \CalendR\Event\EventInterface;

/**
 * Constructeur de la liste des événements selon des règles métiers
 *
 * Ne doit contacter que Collection\*
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
     * @var \DateTimeInterface
     */
    private $dateDebut;

    /**
     * @var \DateTimeInterface
     */
    private $dateFin;

    /**
     * @var string Identifiant de l'utilisateur
     */
    private $utilisateur;

    /**
     * @var bool Si l'utilisateur a la possiblité de voir les événements non encore validés final
     */
    private $canVoirEnTransit;

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
     * @param \DateTimeInterface $dateDebut
     * @param \DateTimeInterface $dateFin
     * @param string $utilisateur Identifiant de l'observateur
     * @param bool $isGroupesGeres Si la gestion des groupes est demandée
     * @param int $groupeAConsulter Groupe dont on veut voir les événements
     */
    public function __construct(
        \DateTimeInterface $dateDebut,
        \DateTimeInterface $dateFin,
        $utilisateur,
        $canVoirEnTransit,
        $isGroupesGeres,
        $groupeAConsulter = NIL_INT
    ){
        $this->dateDebut = clone $dateDebut;
        /* Extension des bordures de dates */
        $this->dateDebut->modify('-1 week');
        $this->dateFin = clone $dateFin;
        $this->dateFin->modify('+1 week');
        $this->utilisateur = (string) $utilisateur;
        $this->canVoirEnTransit = (bool) $canVoirEnTransit;
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
                $fermeture = new Collection\Fermeture($this->dateDebut, $this->dateFin, $groupesATrouver);
            } else {
                $utilisateursATrouver = \App\ProtoControllers\Utilisateur::getListId();
                $fermeture = new Collection\Fermeture($this->dateDebut, $this->dateFin, []);
            }

            $ferie = new Collection\Ferie($this->dateDebut, $this->dateFin);
            $weekend = new Collection\Weekend($this->dateDebut, $this->dateFin);
            $this->evenements = array_merge(
                $ferie->getListe(),
                $weekend->getListe(),
                $fermeture->getListe()
            );

            if (!empty($utilisateursATrouver)) {
                $conge = new Collection\Conge($this->dateDebut, $this->dateFin, $utilisateursATrouver, $this->canVoirEnTransit);
                $repos = new Collection\Heure\Repos($this->dateDebut, $this->dateFin, $utilisateursATrouver, $this->canVoirEnTransit);
                $additionnelle = new Collection\Heure\Additionnelle($this->dateDebut, $this->dateFin, $utilisateursATrouver, $this->canVoirEnTransit);
                $this->evenements = array_merge(
                    $this->evenements,
                    $conge->getListe(),
                    $repos->getListe(),
                    $additionnelle->getListe()
                );
            }
        }

        return $this->evenements;
    }
}
