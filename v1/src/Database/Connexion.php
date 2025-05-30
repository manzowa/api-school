<?php 

/**
 * File Connexion
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category ApiSchool\V1
 * @package  ApiSchool\V1
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
namespace ApiSchool\V1\Database;

class Connexion 
{

    private static $writeDb;
    private static $readDb;

    public static function write(): ?\PDO {
        if (self::$writeDb === null) {
            self::$writeDb = Db::getInstance();
        }
        return self::$writeDb;
    }

    public static function read(): ?\PDO {
        if (self::$readDb === null) {
            self::$readDb = Db::getInstance();
        }
        return self::$readDb;
    }

    public static function is($objet): bool {
        return $objet instanceof \PDO;
    }
}