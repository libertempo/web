<?php
namespace App\Libraries\Calendrier;

/**
 * Construction des événements du calendrier.
 * Application du design pattern Facade pour obfusquer la complexité du calendrier
 *
 * @link https://en.wikipedia.org/wiki/Facade_pattern
 * @since 1.10
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see Tests\Units\App\Libraries\Calendrier\Facade
 */
class Facade
{
    public function __construct(\App\Libraries\InjectableCreator $injectableCreator, array $employesATrouver, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin)
    {
        $this->injectableCreator = $injectableCreator;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->employesATrouver = $employesATrouver;
        $this->fetchEvenements();
    }

    private $injectableCreator;

    /**
     * @var array Liste de résultats événements
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
     * @var array
     */
    private $employesATrouver;

    /**
     * Recupère la liste ordonnée des événements des employés
     */
    private function fetchEvenements()
    {
        $this->fetchWeekends();
        $this->fetchFeries();
        $this->fetchFermeture();
        $this->fetchConges();
        $this->fetchHeuresAdditionnelles();
        // construction de cp, heures...
    }

    /**
     * Recupère la liste ordonnée des weekend des employés
     */
    private function fetchWeekends()
    {
        $weekend = $this->injectableCreator->get(Collection\Weekend::class);
        $weekendsListe = $weekend->getListe($this->dateDebut, $this->dateFin);
        foreach ($weekendsListe as $date) {
            foreach ($this->employesATrouver as $employe) {
                $this->setEvenementDate($employe, $date, 'weekend');
                // pareil pour title
            }
        }
    }

    private function fetchFeries()
    {
        $feries = $this->injectableCreator->get(Collection\Ferie::class);
        $feriesListe = $feries->getListe($this->dateDebut, $this->dateFin);
        foreach ($feriesListe as $date) {
            foreach ($this->employesATrouver as $employe) {
                $this->setEvenementDate($employe, $date, 'ferie');
                // pareil pour title
            }
        }
    }

    private function fetchFermeture()
    {
        $fermeture = $this->injectableCreator->get(Collection\Fermeture::class);
        $fermetureListe = $fermeture->getListe($this->dateDebut, $this->dateFin, []);
        foreach ($fermetureListe as $date) {
            foreach ($this->employesATrouver as $employe) {
                $this->setEvenementDate($employe, $date, 'fermeture');
                // pareil pour title
            }
        }
    }

    private function fetchConges()
    {
        $conge = $this->injectableCreator->get(Collection\Conge::class);
        $congesListe = $conge->getListe($this->dateDebut, $this->dateFin, $this->employesATrouver, false);
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

    private function fetchHeuresAdditionnelles()
    {
        $heure = $this->injectableCreator->get(Collection\Heure\Additionnelle::class);
        $heureListe = $heure->getListe($this->dateDebut, $this->dateFin, $this->employesATrouver, false);
        foreach ($heureListe as $jour => $evenementsJour) {
            foreach ($evenementsJour as $evenement) {
                $this->setEvenementDate($evenement['employe'], $jour, 'heure_additionnelle_' . $evenement['statut']);
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

    // avoir un système de « get » pour tout récup à partir de là ? et on boucle à l'extérieur, plutôt que demander à chaque fois à la structure de données

    /*
     * $a = [
     *  'employeX' => [
     *      'nom' => 'Nom complet',
     *      'dates'  => [
     *          'dateY' => [
     *              'evenements' => ['cp, 'rtt'],
     *              'title' => '...'
     *          ],
     *      ],
     *  ],
     * ];
     */
}
