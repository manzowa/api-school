<?php
/**
 * GLobal Functions
 * 
 * This file is part of school_manager
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category Assessment
 * @package  SchoolManager
 * @author   User: Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
namespace App\SchoolManager;

if (!function_exists("App\SchoolManager\logger")) {
    /**
     * Function logger
     * 
     * @param string $message       - Le message d'erreur qui doit être stocké
     * @param int    $type          - Spécifie la destination du message d'erreur
     * @param string $destination   - 
     * @param string $extra_headers - 
     * 
     * @return void
     */
    function logger(
        string $message,
        int $type = 0,
        $destination = "",
        $extra_headers = ""
    ): void {
        error_log($message, $type, $destination, $extra_headers);
    }
}

if (!function_exists("App\SchoolManager\loggerException")) {
    /**
     * Function loggerException
     * 
     * @param Exception $e - Le message d'erreur qui doit être stocké
     * 
     * @return void
     */
    function loggerException(\Exception $e): void {
        $msg= sprintf("%s sur la ligne ( %s ) : %s", 
            $e->getFile(), $e->getLine(), $e->getMessage()
        );
        logger($msg);
    }
}
