<?php 
/**
 * This file is part of school_manager
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category Assessment
 * @package  SchoolManager
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
namespace App\SchoolManager
{
    use App\SchoolManager\Http\Response;
    use App\SchoolManager\Attribute\Router;
    use App\SchoolManager\Exception\RouteException;

    /**
     * Class Application 
     * @package  SchoolManager
     */
    class App 
    {
        /**
         * Method run
         *
         * @return void
         */
        public static function run(): void
        {
            try {
                $router = new Router();
                $configRoutes = include join(
                    DS, [SCHOOL_ROOT, 'config', '_router.php']
                );
                $router->initGlobales($configRoutes);
                $router->call();
            } catch (RouteException $e) {
                $response = new Response;
                $response->json(statusCode: $e->getCode(), success: false, message: $e->getMessage());
            }
        }
    }
}