<?php
namespace ApiSchool\V1;

/**
 * This file is part of SchoolManager
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category ApiSchool\V1
 * @package  ApiSchool\V1
 * @author   User: Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */

require_once join(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'core', 'app.php']);
if (str_contains($_SERVER['REQUEST_URI'], "api") 
    || str_contains($_SERVER['REQUEST_URI'], "v1")
) {
    App::run();
}
