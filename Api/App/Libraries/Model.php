<?php
namespace Api\App\Libraries;

/**
 * Domain Model
 *
 * @see https://en.wikipedia.org/wiki/Domain_model
 */
class Model
{
    /**
     * @var int $id Identifiant unique de l'élément dans la liste
     */
    protected $id;

    /**
     * @var array $data Données de l'objet
     */
    protected $data;

    public function __construct($id, array $data)
    {
        $this->id = (int) $id;
        $this->data = $data;
    }

    public function getId()
    {
        return $this->id;
    }
}
