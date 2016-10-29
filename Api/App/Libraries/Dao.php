<?php
namespace Api\App\Libraries;

/**
 *
 */
abstract class Dao
{
    /**
     * Connecteur à la BDD
     */
    protected $storageConnector;

    public function __construct($storageConnector)
    {
        $this->storageConnector = $storageConnector;
    }

    public function getStorageConnector()
    {
        return $this->storageConnector;
    }

    public function getById($id)
    {

    }

    /**
     * 
     */
    abstract protected function getTableName();
}
