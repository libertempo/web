<?php
namespace Api\App\Libraries;

/**
 * Domain Model. Par essence, ne peut pas être immuable.
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 *
 * @see https://en.wikipedia.org/wiki/Domain_model
 * Ne devrait être contacté par personne
 * Ne devrait contacter personne
 */
abstract class AModel
{
    /**
     * @var int $id Identifiant unique de l'élément dans la liste
     */
    protected $id;

    /**
     * @var array $data Données de l'objet
     */
    protected $data = [];

    /**
     * @var array $data Données d'édition de l'objet
     */
    protected $dataUpdated = [];

    /**
     * @var array $erreurs Erreurs de domaine sur l'édition de l'objet
     */
    private $erreurs = [];

    /**
     * Construit l'objet de manière pure,
     * autrement dit, avec données vérifiées au sens du domaine, donc venant du stockage
     *
     * Oblige par construction à avoir un offset 'id' dans le paramètre
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (isset($data['id'])) {
            $id = $data['id'];
            $this->id = (int) $id;
            unset($data['id']);
            $this->data = $data;
        }
    }

    /**
     * Retourne l'identifiant unique de l'objet
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Insère massivement des nouvelles données dans le modèle
     *
     * @param array $data Données à insérer / mettre à jour
     *
     * @throws \DomainException Si une ou plusieurs données ne sont pas dans les bons domaines de définition, où les erreurs sont jsonEncodée dans le message
     * @example ['nomChamp' => [listeErreurs]]
     */
    abstract public function populate(array $data);

    /**
     * Ajoute une erreur au champ
     *
     * @param string $champ Champ
     * @param string $message Message d'erreur
     */
    protected function setErreur($champ, $message)
    {
        $this->erreurs[$champ][] = $message;
    }

    /**
     * Retourne la liste des erreurs de domaine
     *
     * @return array
     */
    protected function getErreurs()
    {
        return $this->erreurs;
    }

    /**
     * Purge toutes les données de l'objet
     */
    public function reset()
    {
        $this->id = null;
        $this->data = [];
        $this->dataUpdated = [];
    }

    /**
     * Retourne la donnée la plus à jour du champ $data
     *
     * @param string $data
     *
     * @return string
     */
    protected function getFreshData($data)
    {
        if (isset($this->dataUpdated[$data])) {
            return $this->dataUpdated[$data];
        }

        return $this->data[$data];
    }
}
