<?php
namespace Intra\Service;

use Gnf\db\base;
use Gnf\db\PDO;
use Illuminate\Database\Capsule\Manager as Capsule;

class IntraDb
{
    private static $gnfdb;

    /**
     * @return base
     */
    public static function getGnfDb()
    {
        self::bootDB();

        return self::$gnfdb;
    }

    public static function bootDB()
    {
        if (self::$gnfdb === null) {
            $capsule = new Capsule();
            $capsule->addConnection(
                [
                    'driver' => 'mysql',
                    'host' => $_ENV['INTRA_DBHOST'],
                    'database' => $_ENV['INTRA_DBNAME'],
                    'username' => $_ENV['INTRA_DBUSER'],
                    'password' => $_ENV['INTRA_DBPASS'],
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                ]
            );
            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            $db = new PDO($capsule->getConnection()->getPdo());
            self::$gnfdb = $db;
        }
    }
}
