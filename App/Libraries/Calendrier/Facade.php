<?php
namespace App\Libraries\Calendrier;

/**
 * Construction du calendrier.
 * Application du design pattern Facade pour obfusquer la complexité du calendrier
 *
 * @link https://en.wikipedia.org/wiki/Facade_pattern
 * @since 1.10
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see Tests\Units\App\Libraries\Calendrier\Facade
 */
class Facade
{
    public function __construct(array $employesATrouver, \includes\SQL $db, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin)
    {
        $this->db = $db;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->fetchEvenements($employesATrouver);
    }

    private $evenements;

    private $db;

    /**
    * @var \DateTimeInterface
    */
    protected $dateDebut;

    /**
    * @var \DateTimeInterface
    */
    protected $dateFin;

    /**
     * Recupère la liste ordonnée des événements des employés
     */
    private function fetchEvenements(array $employesATrouver)
    {
        $this->fetchWeekends($employesATrouver);
        // construction de jours feries, fermeture, cp, weekend, ...
    }

    /**
     * Recupère la liste ordonnée des weekend des employés
     */
    private function fetchWeekends(array $employesATrouver)
    {
        $weekends = (new Collection\Weekend($this->db, $this->dateDebut, $this->dateFin))->getListe();
        sort($weekends);
        foreach ($employesATrouver as $employe) {
            foreach ($weekends as $date) {
                $this->evenements[$employe]['dates'][$date]['types'][] = 'weekend';
            }
        }
    }

    private function isDayWeekend()
    {
        // pour vérifier l'élément absorbant
        // pareil pour ferie ?
    }

    public function getEmploye($idEmploye)
    {
        $this->verificationExistenceEmploye($idEmploye);
    }

    public function getEvenementsDate($idEmploye, $date)
    {
        $this->verificationExistenceEmploye($idEmploye);
        $this->verificationExistenceDateEmploye($idEmploye, $date);
        return $this->evenements[$idEmploye]['dates'][$date]['types'];
    }

    public function getTitleDate($idEmploye, $date)
    {
        $this->verificationExistenceEmploye($idEmploye);
        $this->verificationExistenceDateEmploye($idEmploye, $date);
    }

    private function verificationExistenceEmploye($idEmploye)
    {
        if (!isset($this->evenements[$idEmploye])) {
            throw new \DomainException('Employé inconnu');
        }
    }

    private function verificationExistenceDateEmploye($idEmploye, $date)
    {
        if (!isset($this->evenements[$idEmploye]['dates'][$date])) {
            throw new \DomainException('Date inexistante pour cet employé');
        }
    }

    /*
     * $a = [
     *  'employeX' => [
     *      'nom' => 'Nom complet',
     *      'dates'  => [
     *          'dateY' => [
     *              'types' => ['cp, 'rtt'],
     *              'title' => '...'
     *          ],
     *      ],
     *  ],
     * ];
     */
}
