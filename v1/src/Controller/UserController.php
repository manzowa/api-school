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

namespace ApiSchool\V1\Controller {

    use ApiSchool\V1\Database\Connexion;
    use ApiSchool\V1\Attribute\Route;
    use ApiSchool\V1\Exception\UserException;
    use ApiSchool\V1\Model\User;
    use ApiSchool\V1\Mapper\VendorMapper;

    #[Route(path: '/api/v1')]
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
        #[Route(path: '/users', name: 'users.post', method: 'POST')]
        public function postAction(
            \ApiSchool\V1\Http\Request $request,
            \ApiSchool\V1\Http\Response $response
        ) {
            // Establish the connection Database
            $connexionWrite = Connexion::write();
            // Check the Connection Database
            if (!Connexion::is($connexionWrite)) {
                return $response->json(
                    statusCode: 500,
                    success: false,
                    message: 'Database connection error'
                );
            }

            // Check Content-Type 
            if (!$request->isJsonContentType()) {
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: 'Content type header is not set to JSON'
                );
            }

            // Check Body if it's Json
            if (!$body = $request->contentJsonDecode()) {
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: 'Request body is not valid JSON'
                );
            }

            // Check fullname, username, password and email
            if (
                !isset($body->fullname) || empty($body->fullname)
                || !isset($body->username) || empty($body->username)
                || !isset($body->email) || empty($body->email)
                || !isset($body->password) || empty($body->password)
            ) {
            
                (isset($body->fullname) ?: $response->setMessage('Fullname field is mandatory and must be provider'));
                (!empty($body->fullname) ?: $response->setMessage('Fullname field cannot be blank'));
                (isset($body->username) ?: $response->setMessage('Username field is mandatory and must be provider'));
                (!empty($body->username) ?: $response->setMessage('Username field cannot be blank'));
                (isset($body->email) ?: $response->setMessage('Email field is mandatory and must be provider'));
                (!empty($body->email) ?: $response->setMessage('Email field cannot be blank'));
                (isset($body->password)?: $response->setMessage('Password field is mandatory and must be provider'));
                (!empty($body->password) ?: $response->setMessage('Password field cannot be blank'));
                return $response->json(statusCode: 400, success: false);
            }

            try 
            {
                $mapper = new VendorMapper($connexionWrite);
                //Variables
                $fullname = $body->fullname;
                $username = $body->username;
                $email = $body->email;
                $password = $body->password;

                $mapper
                    ->findUserByUsername(username: $username)
                    ->executeQuery();

                if ($mapper->rowCount() !== 0) {
                    return $response->json(
                        statusCode:500, success:false, 
                        message:'Username already exists'
                    );
                }
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                
                // Check if username and email already exists
                // Create the User object
                $user = new User(
                    id: null, fullname: $fullname,
                    username: $username, email: $email,
                    password: $hashed_password
                );
                // Insert the User in the database
                $mapper
                    ->userAdd(user: $user)
                    ->executInsert();
                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:500, success:false, 
                        message:'Failed to create user'
                    );
                }
                $lastInsertId = (int) $mapper->lastInsertId();

                // Return the created user
                $row = $mapper->userRetrieve(id: $lastInsertId)
                ->executeQuery()
                ->getResults();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:500, success:false, 
                        message:'Failed to retrieve user after to create.'
                    );
                }
                $newuser = User::fromState($row);
                return $response->json(
                    statusCode: 201,
                    success: true,
                    message: 'User created successfully',
                    data: $newuser->toArray()
                );
            } catch (UserException $e) {
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: $e->getMessage()
                );
            }
        }
        /**
         * Method optionsAction [OPTIONS]
         * 
         * Permet de déclarer les options HTTP
         * 
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(path: '/users', name: 'users.option', method: 'OPTIONS')]
        public function optionsAction(
            \ApiSchool\V1\Http\Request $request,
            \ApiSchool\V1\Http\Response $response
        ) {
            header('Access-Control-Allow-Methods: POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            header('Access-Control-Allow-Max-Age: 86400');
            header('Access-Control-Allow-Origin: *');

            // $headers = [
            //     'Access-Control-Allow-Methods' => 'POST, OPTIONS',
            //     'Access-Control-Allow-Headers' => 'Content-Type',
            //     'Access-Control-Max-Age' => '86400',
            //     'Access-Control-Allow-Origin' => '*'
            // ];

            $response
                //->setHeader('Access-Control-Allow-Methods', ' POST, OPTIONS')
                // ->setHeader('Access-Control-Allow-Headers', 'Content-Type')
                // ->setHeader('Access-Control-Max-Age', '86400')
                // ->setHeader('Access-Control-Allow-Origin', '*')
                // ->withHeaders(headers : $headers)
                ->json(statusCode: 200, success: true);
        }
    }
}
