<?php
namespace App\Libraries\Calendrier;

/**
 * Construction des événements du calendrier.
 * Application du design pattern Facade pour obfusquer la complexité du calendrier
 *
 * @since 1.10
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see Tests\Units\App\Libraries\Calendrier\Facade
 */
class Facade
{
    public function __construct(
        \App\Libraries\InjectableCreator $injectableCreator)
    {
        $this->injectableCreator = $injectableCreator;
    }

    private $injectableCreator;

    private $employesATrouver;

    /**
     * Recupère la liste ordonnée des événements des employés
     *
     * @param \DateTimeInterface $dateDebut
     * @param \DateTimeInterface $dateFin
     * @param array $employesATrouver Liste d'utilisateurs dont on veut voir les événements
     * @param bool $canVoirEnTransit Si l'utilisateur a la possiblité de voir les événements non encore validés
     */
    public function fetchEvenements(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, array $employesATrouver, $canVoirEnTransit)
    {
        $canVoirEnTransit = (bool) $canVoirEnTransit;
        $this->employesATrouver = $employesATrouver;
        $this->fetchWeekends($dateDebut, $dateFin);
        $this->fetchFeries($dateDebut, $dateFin);
        $this->fetchFermeture($dateDebut, $dateFin);
        $this->fetchConges($dateDebut, $dateFin);
        $this->fetchHeuresAdditionnelles($dateDebut, $dateFin);
        $this->fetchHeuresRepos($dateDebut, $dateFin);
    }

    /**
     * Recupère la liste ordonnée des weekend des employés
     */
    private function fetchWeekends(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin)
    {
        $weekend = $this->injectableCreator->get(Collection\Weekend::class);
        $weekendsListe = $weekend->getListe($dateDebut, $dateFin);
        foreach ($weekendsListe as $date) {
            foreach ($this->employesATrouver as $employe) {
                $this->setEvenementDate($employe, $date, 'weekend');
                // pareil pour title
            }
        }
    }

    /**
     * Recupère la liste ordonnée des jours fériés des employés
     */
    private function fetchFeries(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin)
    {
        $feries = $this->injectableCreator->get(Collection\Ferie::class);
        $feriesListe = $feries->getListe($dateDebut, $dateFin);
        foreach ($feriesListe as $date) {
            foreach ($this->employesATrouver as $employe) {
                $this->setEvenementDate($employe, $date, 'ferie');
                // pareil pour title
            }
        }
    }

    private function fetchFermeture(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin)
    {
        $fermeture = $this->injectableCreator->get(Collection\Fermeture::class);
        $fermetureListe = $fermeture->getListe($dateDebut, $dateFin, []);
        foreach ($fermetureListe as $date) {
            foreach ($this->employesATrouver as $employe) {
                $this->setEvenementDate($employe, $date, 'fermeture');
                // pareil pour title
            }
        }
    }

    private function fetchConges(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin)
    {
        $conge = $this->injectableCreator->get(Collection\Conge::class);
        $congesListe = $conge->getListe($dateDebut, $dateFin, $this->employesATrouver, false);
        foreach ($congesListe as $jour => $evenementsJour) {
            foreach ($evenementsJour as $evenement) {
                $suffixe = '*' !== $evenement['demiJournee']
                ? '_' . $evenement['demiJournee']
                : '';
                $this->setEvenementDate($evenement['employe'], $jour, 'conge' . $suffixe . ' conge_' . $evenement['statut']);
                // pareil pour title
            }
        }
    }

    private function fetchHeuresAdditionnelles(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin)
    {
        $heure = $this->injectableCreator->get(Collection\Heure\Additionnelle::class);
        $heureListe = $heure->getListe($dateDebut, $dateFin, $this->employesATrouver, false);
        foreach ($heureListe as $jour => $evenementsJour) {
            foreach ($evenementsJour as $evenement) {
                $this->setEvenementDate($evenement['employe'], $jour, 'heure_additionnelle_' . $evenement['statut']);
            }
        }
    }

    private function fetchHeuresRepos(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin)
    {
        $heure = $this->injectableCreator->get(Collection\Heure\Repos::class);
        $heureListe = $heure->getListe($dateDebut, $dateFin, $this->employesATrouver, false);
        foreach ($heureListe as $jour => $evenementsJour) {
            foreach ($evenementsJour as $evenement) {
                $this->setEvenementDate($evenement['employe'], $jour, 'heure_repos_' . $evenement['statut']);
            }
        }
    }

    private function setEvenementDate($idEmploye, $date, $nomEvenement)
    {
        if ($this->isEvenementWeekend($nomEvenement)) {
            unset($this->evenements[$idEmploye]['dates'][$date]['evenements']);
        }
        if (!$this->isDayWeekend($idEmploye, $date)) {
            $this->evenements[$idEmploye]['dates'][$date]['evenements'][] = $nomEvenement;
        }
    }

    private function isDayWeekend($idEmploye, $date)
    {
        if (!isset($this->evenements[$idEmploye]) || !isset($this->evenements[$idEmploye]['dates'][$date])) {
            return false;
        }

        return in_array('weekend', $this->evenements[$idEmploye]['dates'][$date]['evenements'], true);
        // pour vérifier l'élément absorbant
        // pareil pour ferie ?
    }

    private function isEvenementWeekend($nomEvenement)
    {
        return 'weekend' === $nomEvenement;
    }

    public function getEmployes()
    {
        return $this->employesATrouver;
    }

    /**
     * @TODO: utile ?
     */
    public function getEmploye($idEmploye)
    {
        $this->verificationExistenceEmploye($idEmploye);
    }

    public function getEvenementsDate($idEmploye, $date)
    {
        $this->verificationExistenceEmploye($idEmploye);
        if (!isset($this->evenements[$idEmploye]['dates'][$date])) {
            return [];
        }

        return $this->evenements[$idEmploye]['dates'][$date]['evenements'];
    }

    public function getTitleDate($idEmploye, $date)
    {
        $this->verificationExistenceEmploye($idEmploye);
        if (!isset($this->evenements[$idEmploye]['dates'][$date])) {
            return [];
        }
    }

    private function verificationExistenceEmploye($idEmploye)
    {
        if (!isset($this->evenements[$idEmploye])) {
            throw new \DomainException('Employé inconnu');
        }
    }

    /**
     * @TODO : à but de test, à supprimer quand c'est terminé
     */
    public function getEvenements()
    {
        return $this->evenements;
    }
}
