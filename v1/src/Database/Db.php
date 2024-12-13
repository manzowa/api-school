<?php 

/**
 * File Db
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

use \Exception;
use \PDO;
use \PDOException;

class Db 
{
    /**
     * Db
     * 
     * @var    $_instance
     * @access private 
     * @static 
     */
    private static $_instance;
    
    /**
     * Crée et retourne l'objet Db
     * 
     * @access public
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            try 
            {
                self::$_instance = new \PDO(
                    getenv('DATABASE_SCHOOL_V1_DNS', true) ?: getenv('DATABASE_SCHOOL_V1_DNS'),
                    getenv('DATABASE_SCHOOL_V1_USER', true) ?: getenv('DATABASE_SCHOOL_V1_USER'),
                    getenv('DATABASE_SCHOOL_V1_PASSWORD', true) ?: getenv('DATABASE_SCHOOL_V1_PASSWORD'),
                    [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_EMULATE_PREPARES => false,
                        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                        \PDO::ATTR_PERSISTENT => true
                    ]
                );
            } catch (PDOException | Exception $e) {
                $msgError =sprintf("%s sur la ligne ( %s ) : %s", 
                    $e->getFile(), $e->getLine(), $e->getMessage()
                );
                error_log($msgError, 0);
            }
        }
        return self::$_instance;
    }
}