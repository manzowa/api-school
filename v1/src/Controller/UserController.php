<?php 

/**
 * File EcoleController
 * 
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
namespace App\SchoolManager\Controller
{
    use App\SchoolManager\Database\Connexion;
    use App\SchoolManager\Attribute\Route;
    use App\SchoolManager\Exception\UserException;
    use App\SchoolManager\Model\User;
    use App\SchoolManager\Mapper\VendorMapper;

    #[Route(path:'/api/v1')]
    class UserController
    {
        /**
         * Method postAction [POST]
         * 
         * Il permet d'ajouter une école
         *
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(path:'/users', name:'users.post', method: 'POST')]
        public function postAction(
            \App\SchoolManager\Http\Request $request, 
            \App\SchoolManager\Http\Response $response
        ) {
            // Establish the connection Database
            $connexionWrite = Connexion::write();
            // Check the Connection Database
            if (!Connexion::is($connexionWrite)){
                return $response->json(
                    statusCode:500, success:false,
                    message:'Database connection error'
                );
            }
            
            // Check Content-Type 
            if (!$request->isJsonContentType()) {
                return $response->json(
                    statusCode:400, success:false,
                    message:'Content type header is not set to JSON'
                );
            }

            // Check Body if it's Json
            if (!$body = $request->contentJsonDecode()) {
                return $response->json(
                    statusCode:400, success:false,
                    message:'Request body is not valid JSON'
                );
            }
        }
    }
}