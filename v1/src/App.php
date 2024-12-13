<?php 
/**
 * This file is part of school_manager
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
namespace ApiSchool\V1
{
    use \ApiSchool\V1\Http\Response;
    use \ApiSchool\V1\Attribute\Router;
    use \ApiSchool\V1\Exception\RouteException;

    /**
     * Class Application 
     * @package  ApiSchool\V1
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