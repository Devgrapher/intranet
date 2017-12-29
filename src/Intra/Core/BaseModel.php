<?php
namespace Intra\Core;

use Gnf\db\base;
use Intra\Service\IntraDb;

class BaseModel
{
    /**
     * @var base
     */
    protected $db;

    public function __construct(base $db = null)
    {
        if (is_null($db)) {
            $db = IntraDb::getGnfDb();
        }
        $this->db = $db;
    }

    /**
     * @return \Gnf\db\base
     */
    protected static function getDb()
    {
        return IntraDb::getGnfDb();
    }

    public function transactional($function)
    {
        return $this->getInstanceDb()->transactional($function);
    }

    /**
     * @return base
     */
    protected function getInstanceDb()
    {
        return $this->db;
    }

    /**
     * @param base $db
     *
     * @return $this
     */
    public static function create($db = null)
    {
        $called_class = get_called_class();

        return new $called_class($db);
    }
}
