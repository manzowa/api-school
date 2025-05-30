<?php

/**
 * File SessionController
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

    use ApiSchool\V1\Auth\Auth;
    use ApiSchool\V1\Database\Connexion;
    use ApiSchool\V1\Attribute\Route;
    use ApiSchool\V1\Exception\UserException;
    use ApiSchool\V1\Model\User;
    use ApiSchool\V1\Model\Token;
    use ApiSchool\V1\Mapper\VendorMapper;

    #[Route(path: '/api/v1')]
    class TokenController
    {
        /**
         * Method postAction [POST]
         *
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         *
         * @return array
         */
        #[Route(path: '/sessions', name:'sessions.post', method: 'POST')]
        public function postAction(
            \ApiSchool\V1\Http\Request $request,
            \ApiSchool\V1\Http\Response $response
        ) {
            sleep(1); // Important
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

            // Check username and password 
            if (
                !isset($body->username) || empty($body->username)
                || !isset($body->password) || empty($body->password)
            ) {
                (isset($body->username) ?: $response->setMessage('Username not supplied'));
                (!empty($body->username) ?: $response->setMessage('Username cannot be empty'));
                (isset($body->password) ?: $response->setMessage('Password not supplied'));
                (!empty($body->password) ?: $response->setMessage('Password cannot be empty'));

                return $response->json(statusCode: 400, success: false);
            }

            if (strlen($body->username) < 1 || strlen($body->username) > 255
                || strlen($body->password) < 1 || strlen($body->password) > 255
            ) {
                (
                    !(strlen($body->username) < 1) 
                    ?: $response->setMessage('Username cannot be blank')
                );
                (
                    !(strlen($body->username) > 255) 
                    ?: $response->setMessage('Username must be less than 255 characters')
                );
                (
                    !(strlen($body->password) < 1) 
                    ?: $response->setMessage('Password cannot be blank')
                );
                (
                    !(strlen($body->password) > 255) 
                    ?: $response->setMessage('Password must be less than 255 characters')
                );
                
                return $response->json(statusCode: 400, success: false);
            }

            try 
            {
                // variables
                $username = $body->username;
                $password = $body->password;
                // Check if user exists
                $mapper = new VendorMapper($connexionWrite);

                $userRow = $mapper
                    ->findUserByUsername($username)
                    ->executeQuery()
                    ->getResults();
                $row = current($userRow);

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode: 401, success: false, 
                        message: 'Username or Password is incorrect'
                    );
                }
                $user = User::fromState(data: $row);
              
                // Check if user is active
                if (!$user->isActive()) {
                    return $response->json(
                        statusCode: 401, success: false,
                        message: 'Your account is not active. Please check your email for the activation link.'
                    );
                }
                // Check attempts to login

                // Check if user has exceeded maximum login attempts
                if ($user->isLocked()) {
                    return $response->json(
                        statusCode: 429, success: false,
                        message: 'Too many login attempts. Please try again later.'
                    );
                }
                
                // Check if password is correct
                if (!password_verify($password, $user->getPassword())) {
                    // Update login attempts
                    $user->incrementAttempts();
                    $mapper
                        ->userUpdate(user: $user)
                        ->executeUpdate();
                    if ($mapper->rowCount() === 0) {
                        return $response->json(
                            statusCode: 401, success: false,
                            message: 'Username or Password is incorrect'
                        );
                    }
                }

                $accessToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)) . time());
                $refreshToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)) . time());
        
                $access_token_expiry_seconds = 1200;
                $refresh_token_expiry_seconds = 1209600;
                
                // Start Transaction
                $mapper->beginTransaction();
                
                $user->resetAttempts();
                $mapper
                    ->userUpdate(user: $user)
                    ->executeUpdate();
            
                $session = new Token(
                    id: null,
                    userid: $user->getId(),
                    accessToken: $accessToken,
                    accessTokenExpiry: $access_token_expiry_seconds,
                    refreshToken: $refreshToken,
                    refreshTokenExpiry: $refresh_token_expiry_seconds
                );
                $mapper
                    ->sessionAdd(session: $session)
                    ->executInsert();
                
                $lastSessionId = $mapper->lastInsertId();
                $mapper->commit();

                $returnData = [];
                $returnData['session_id'] = intval($lastSessionId);
                $returnData['access_token'] = $accessToken;
                $returnData['access_token_expires_in'] = $access_token_expiry_seconds;
                $returnData['refresh_token'] = $refreshToken;
                $returnData['refresh_token_expires_in'] = $refresh_token_expiry_seconds;

                return $response->json(
                    statusCode: 201,
                    success: true,
                    data: $returnData
                );
            
            } catch (\ApiSchool\V1\Exception\UserException $e) {
                if ($mapper->inTransaction()) {
                    $mapper->rollBack();
                }
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: $e->getMessage()
                );
            }
        }

        /**
         * Method deleteAction [POST]
         *
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         *
         * @return void
         */
        #[Route(
            path: '/sessions/([0-9]+)', 
            name:'sessions.deleteOne', 
            method: 'DELETE'
        )]
        public function deleteAction(
            \ApiSchool\V1\Http\Request $request,
            \ApiSchool\V1\Http\Response $response
        ){
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
            $sessionid = (int) $request->getParam('sessionid');
            // Check Parameter School ID
            if ($sessionid === ''  || !is_numeric($sessionid)) {
                ($sessionid !== '') ?: $response->setMessage('Session Id cannot be blank');
                is_numeric($sessionid) ?: $response->setMessage('Session Id must be numeric');
                return $response->json(
                    statusCode:400, success:false
                );
            }
            // Check the Access Token
            if (!$request->getHeaderLine('HTTP_AUTHORIZATION') 
                || @strlen($request->getHeaderLine('HTTP_AUTHORIZATION')) < 1
            ) {
                (
                    $request->getHeaderLine('HTTP_AUTHORIZATION') 
                    ?: $response->setMessage('Access token is missing from the header')
                );
                (
                    @strlen($request->getHeaderLine('HTTP_AUTHORIZATION')) < 1
                    ?: $response->setMessage('Access token cannot be blank')
                );
                return $response->json(
                    statusCode:401, success:false
                );
            }
            
            try {
                $accessToken = $request->getHeaderLine('HTTP_AUTHORIZATION');
                $mapper = new VendorMapper($connexionWrite);

                $mapper
                    ->sessionRemove(id: $sessionid, accessToken: $accessToken)
                    ->executeDelete();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode: 400,
                        success: false,
                        message: 'Failed to log out of this sessions using access token provided'
                    );
                }

                $returnData = [];
                $returnData['session_id'] = intval($sessionid);

                return $response->json(
                    statusCode: 200,
                    success: true,
                    data: $returnData,
                    message: 'Logged out'
                );

            } catch (\ApiSchool\V1\Exception\UserException $e) {
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: $e->getMessage()
                );
            }
        }
         /**
         * Method patchAction [PATCH]
         *
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         *
         * @return void
         */
        #[Route(
            path: '/sessions/([0-9]+)', 
            name:'sessions.patchOne', 
            method: 'PATCH'
        )]
        public function patchAction(
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
            $sessionid = (int) $request->getParam('sessionid');
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

            // Check refresh token
            if (
                !isset($body->refresh_token) 
                || @strlen($body->refresh_token) < 1
            ) {
                (isset($body->refresh_token) 
                    ?: $response->setMessage('Refresh token not supplied')
                );
                (
                    @strlen($body->refreshToken) >= 1
                    ?: $response->setMessage('Refresh token cannot be blank')
                );
                return $response->json(
                    statusCode: 400,
                    success: false
                );
            }
            // Check the Access Token
            if (!$request->getHeaderLine('HTTP_AUTHORIZATION') 
                || @strlen($request->getHeaderLine('HTTP_AUTHORIZATION')) < 1
            ) {
                (
                    $request->getHeaderLine('HTTP_AUTHORIZATION') 
                    ?: $response->setMessage('Access token is missing from the header')
                );
                (
                    @strlen($request->getHeaderLine('HTTP_AUTHORIZATION')) < 1
                    ?: $response->setMessage('Access token cannot be blank')
                );
                return $response->json(
                    statusCode:401, success:false
                );
            }

            try {
                $accessToken = $request->getHeaderLine('HTTP_AUTHORIZATION');
                $refreshToken = $body->refresh_token;

                $mapper = new VendorMapper($connexionWrite);

                $sessionRow = $mapper->sessionRetrieve(
                    id: $sessionid, accessToken: $accessToken, 
                    refreshToken: $refreshToken
                )
                ->executeQuery()
                ->getResults();
                $row = current($sessionRow);
                
                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode: 401,
                        success: false,
                        message: 'Access token or refresh token is invalid'
                    );
                }
              
                $token = Token::fromState($row);
                $userRow = $mapper
                    ->userRetrieve(id: $token->getUserid())
                    ->executeQuery()
                    ->getResults();
                $row = current($userRow);
                $user = User::fromState(data: $row);

                 // Check if user is active
                 if (!$user->isActive()) {
                    return $response->json(
                        statusCode: 401, success: false,
                        message: 'User account is not active'
                    );
                }
                // Check if user has exceeded maximum login attempts
                if ($user->isLocked()) {
                    return $response->json(
                        statusCode: 401, success: false,
                        message: 'User account is currently locked out.'
                    );
                }
                // Check refresh token expiration
                if ($token->isRefreshtokenexpiry()) {
                    return $response->json(
                        statusCode: 401, success: false,
                        message: 'Refresh token has expired - please log in again'
                    );
                }

                $accessToken  = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)) . time());
                $refreshToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)) . time());
                $access_token_expiry_seconds = 1200;
                $refresh_token_expiry_seconds = 1209600;

                $token
                    ->setAccessToken(accessToken: $accessToken)
                    ->setAccessTokenExpiry(accessTokenExpiry: $access_token_expiry_seconds)
                    ->setRefreshToken(refreshToken: $refreshToken)
                    ->setRefreshTokenExpiry(refreshTokenExpiry: $refresh_token_expiry_seconds);

                $mapper
                    ->sessionUpdate(session: $token)
                    ->executeUpdate();

                if ($mapper->rowCount() == 0) {
                    return $response->json(
                        statusCode: 401,
                        success: false,
                        message: 'Access token could not be refresh - please log in again'
                    );
                }

                $returnData = [];
                $returnData['session_id'] = $sessionid;
                $returnData['access_token'] = $accessToken;
                $returnData['access_token_expiry'] = $access_token_expiry_seconds;
                $returnData['refresh_token'] = $refreshToken;
                $returnData['refresh_token_expiry'] = $refresh_token_expiry_seconds;

                return $response->json(
                    statusCode: 200,
                    success: true,
                    data: $returnData,
                    message: 'Access token refreshed'
                );
            } catch (\ApiSchool\V1\Exception\UserException $e) {
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: $e->getMessage()
                );
            }
        }
    }
}