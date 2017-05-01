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

    public function __construct(array $employesATrouver, \includes\SQL $db, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin)
    {
        $this->db = $db;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->fetchEvenements($employesATrouver);
        // construction de jours feries, fermeture, weekend, ...
    }

    /**
     *
     */
    private function fetchEvenements(array $employesATrouver)
    {
        $this->fetchWeekends($employesATrouver);
    }

    /**
     *
     */
    private function fetchWeekends(array $employesATrouver)
    {
        $weekends = (new Collection\Weekend($this->db, $this->dateDebut, $this->dateFin))->getListe();
        $types['types'] = 'weekend';
        foreach ($employesATrouver as $employe) {
            foreach ($weekends as $weekend) {
                $this->evenements[$employe]['dates'][] = [
                    $weekend => $types,
                ];
            }
        }
        //ddd($this->evenements);
    }

    private function isDayWeekend()
    {
        // pour vérifier l'élément absorbant
        // pareil pour ferie ?
    }

    public function getEmploye($idEmploye)
    {

    }

    public function getEvenement($idEmploye, $date)
    {

    }

    public function getTitle($idEmploye, $date)
    {

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
