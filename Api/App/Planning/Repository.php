<?php
namespace Api\App\Planning;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 * @see \Api\Tests\Units\App\Planning\Repository
 *
 * Ne devrait être contacté que par le Planning\Controller
 * Ne devrait contacter que le Planning\Model, Planning\Dao
 */
class Repository extends \Api\App\Libraries\Repository
{
    /**
     * Retourne une ressource unique
     *
     * @param int $id Id potentiel de planning
     *
     * @return Model
     * @throws \DomainException Si $id n'est pas dans le domaine de définition
     */
    public function getOne($id)
    {
        $id = (int) $id;
        $data = $this->dao->getById($id);
        if (empty($data)) {
            throw new \DomainException('Planning#' . $id . ' is not a valid ressource');
        }

        return new Model($id, $this->getDataDao2Model($data));
    }

    /**
     *
     */
    public function getList()
    {
        /*
         several params :
            offset (first, !isset => 0) / start-after ?
            Limit (nb elements)
            filter (dimensions forced)
          */
        return [];
    }

    /**
     * Effectue le mapping des éléments venant de la DAO pour qu'ils soient compréhensibles pour le Modèle
     *
     * /!\ Danger : id ne doit jamais être transformé
     *
     * @param array $dataDao
     *
     * @return array
     */
    private function getDataDao2Model(array $dataDao)
    {
        return [
            'name' => $dataDao['name'],
            'status' => $dataDao['status'],
        ];
    }
}
