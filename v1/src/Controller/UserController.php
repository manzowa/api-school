<?php 

/**
 * File EcoleController
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
namespace ApiSchool\V1\Controller
{
    use ApiSchool\V1\Database\Connexion;
    use ApiSchool\V1\Attribute\Route;
    use ApiSchool\V1\Exception\UserException;
    use ApiSchool\V1\Model\User;
    use ApiSchool\V1\Mapper\VendorMapper;

    #[Route(path:'/api/v1')]
    class UserController
    {
        /**
         * Method postAction [POST]
         * 
         * Il permet d'ajouter une école
         *
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(path:'/users', name:'users.post', method: 'POST')]
        public function postAction(
            \ApiSchool\V1\Http\Request $request, 
            \ApiSchool\V1\Http\Response $response
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