<?php

namespace App\SchoolManager;

use \Dotenv\Dotenv;

/**
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
$userContants = get_defined_constants(true);

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('SCHOOL_ROOT') or define('SCHOOL_ROOT', dirname(__DIR__));
defined('SCHOOL_VERSION') or define('SCHOOL_VERSION', 'v1');
$MAIN_APP_ROOT = $userContants['APP_PUBLIC']?? dirname(dirname(SCHOOL_ROOT));
$IMAGES_APP_ROOT = join(DS, [$MAIN_APP_ROOT, 'public', 'images']);
require_once join(DS, [SCHOOL_ROOT, 'core', 'bootstrap.php']);
require_once join(DS, [SCHOOL_ROOT, 'vendor', 'autoload.php']);

$dotenv = Dotenv::createUnsafeImmutable(SCHOOL_ROOT)->load();