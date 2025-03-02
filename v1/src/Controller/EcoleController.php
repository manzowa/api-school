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
    use ApiSchool\V1\Exception\EcoleException;
    use ApiSchool\V1\Model\Ecole;
    use ApiSchool\V1\Attribute\Route;
    use ApiSchool\V1\Exception\AdresseException;
    use ApiSchool\V1\Model\Adresse;
    use ApiSchool\V1\Mapper\VendorMapper;

    #[Route(path: '/api/v1')]
    class EcoleController
    {
        /**
         * Method getAction [GET]
         * 
         * Il permet de recupère les écoles
         * 
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         *
         * @return mixed
         */
        #[Route(path: '/ecoles', name: 'ecoles.get')]
        public function getAction(
            \ApiSchool\V1\Http\Request $request,
            \ApiSchool\V1\Http\Response $response
        ) {
            // Establish the connection Database
            $connexionRead = Connexion::read();
            // Check the Connection Database
            if (!Connexion::is($connexionRead)) {
                return $response->json(
                    statusCode: 500,
                    success: false,
                    message: 'Database connection error'
                );
            }

            try {
                $mapper = new VendorMapper($connexionRead);

                $retrievedRows = $mapper
                    ->findEcoles()
                    ->getResults();

                $rowCounted = $mapper->rowCount();

                $ecoles = [];
                for ($index = 0; $index < $rowCounted; $index++) {
                    $row = $retrievedRows[$index];
                    $adresses = $mapper
                        ->adresseRetrieve(ecoleid: $row['id'])
                        ->executeQuery()
                        ->getResults();

                    $images = $mapper
                        ->imageRetrieve(ecoleid: $row['id'])
                        ->executeQuery()
                        ->getResults();

                    $ecole = new Ecole(
                        id: $row['id'],
                        nom: $row['nom'],
                        email: $row['email'],
                        telephone: $row['telephone'],
                        type: $row['type'],
                        site: $row['site'],
                        maximage: $row['maximage'],
                        adresses: $adresses ?? [],
                        images: $images ?? []
                    );
                    $ecoles[] = $ecole->toArray();
                }

                $returnData = [];
                $returnData['rows_returned'] = $rowCounted;
                $returnData['schools'] = $ecoles;

                return $response->json(
                    statusCode: 200,
                    success: true,
                    toCache: true,
                    data: $returnData
                );
            } catch (EcoleException | AdresseException $ex) {
                return $response->json(
                    statusCode: 500,
                    success: false,
                    message: $ex->getMessage()
                );
            }
        }

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
        #[Route(path: '/ecoles', name: 'ecoles.post', method: 'POST')]
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
            // Check Authorisation header
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
            
            $accessToken = $request->getHeaderLine('HTTP_AUTHORIZATION');
            $auth = new \ApiSchool\V1\Auth\Auth(token: $accessToken);

            // Check Access Token
            if (!$auth->isValid()) {
                return $response->json(
                    statusCode: 401,
                    success: false,
                    message: 'Invalid access token'
                );
            }
            // Check if user has exceeded maximum login attempts
            if ($auth->getUser()->isLocked()) {
                return $response->json(
                    statusCode: 401, success: false,
                    message: 'User account is currently locked out.'
                );
            }
              // Check refresh token expiration
              if ($auth->getToken()->accessTokenExpired()) {
                return $response->json(
                    statusCode: 401, success: false,
                    message: 'Access token expired.'
                );
            }
            // END Authorisation header

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

            // Check 
            if (
                !isset($body->nom) || !isset($body->adresses)
                || (isset($body->adresses) && !is_array($body->adresses))
            ) {
                (!isset($body->nom) ? $response->setMessage('Nom field is mandatory and must be provider') : false);
                (!isset($body->adresses)
                    ? $response->setMessage('Adresses field is mandatory and must be provider') : false
                );
                ((isset($body->adresses) && !is_array($body->adresses))
                    ? $response->setMessage('Adresses field should be Array') : false
                );
                return $response->json(statusCode: 400, success: false);
            }
            $bodyAdresses = $body->adresses;

            if (count($bodyAdresses) == 0) {
                ((count($bodyAdresses) == 0) ?
                    $response->setMessage('The fields of Adresses is mandatory and must be provider') : false
                );
                return $response->json(statusCode: 400, success: false);
            } else {
                for ($index = 0; $index < count($bodyAdresses); $index++) {
                    $bodyAdresse = $bodyAdresses[$index];
                    if (
                        !is_object($bodyAdresse)
                        || (is_object($bodyAdresse) && !isset($bodyAdresse->voie))
                        || (is_object($bodyAdresse) && !isset($bodyAdresse->quartier))
                        || (is_object($bodyAdresse) && !isset($bodyAdresse->commune))
                        || (is_object($bodyAdresse) && !isset($bodyAdresse->district))
                        || (is_object($bodyAdresse) && !isset($bodyAdresse->ville))
                    ) {
                        (!is_object($bodyAdresse) ?
                            $response->setMessage('The data in the Field Addresses must be objects') : false
                        );
                        ((is_object($bodyAdresse) && !isset($bodyAdresse->voie)) ?
                            $response->setMessage('Voie Adresses field  is mandatory and must be provider') : false
                        );
                        ((is_object($bodyAdresse) && !isset($bodyAdresse->quartier)) ?
                            $response->setMessage('Quartier Adresses field  is mandatory and must be provider') : false
                        );
                        ((is_object($bodyAdresse) && !isset($bodyAdresse->commune)) ?
                            $response->setMessage('Commune Adresses field  is mandatory and must be provider') : false
                        );
                        ((is_object($bodyAdresse) && !isset($bodyAdresse->district)) ?
                            $response->setMessage('District Adresses  field  is mandatory and must be provider') : false
                        );
                        ((is_object($bodyAdresse) && !isset($bodyAdresse->ville)) ?
                            $response->setMessage('Ville Adresses field  is mandatory and must be provider') : false
                        );
                        return $response->json(statusCode: 400, success: false);
                    }
                }
            }

            try {
                $adressesArray = [];
                for ($index = 0; $index < count($bodyAdresses); $index++) {
                    $bodyAdresse = $bodyAdresses[$index];
                    $voie = (
                        isset($bodyAdresse->voie)
                        && (!is_null($bodyAdresse->voie) && !empty($bodyAdresse->voie))
                    ) ? $bodyAdresse->voie : NULL;
                    $quartier = (
                        isset($bodyAdresse->quartier)
                        && (!is_null($bodyAdresse->quartier) && !empty($bodyAdresse->quartier))
                    ) ? $bodyAdresse->quartier : NULL;
                    $commune = (
                        isset($bodyAdresse->commune)
                        && (!is_null($bodyAdresse->commune) && !empty($bodyAdresse->commune))
                    ) ? $bodyAdresse->commune : NULL;
                    $district = (
                        isset($bodyAdresse->district)
                        && (!is_null($bodyAdresse->district) && !empty($bodyAdresse->district))
                    ) ? $bodyAdresse->district : NULL;

                    $ville = (
                        isset($bodyAdresse->ville)
                        && (!is_null($bodyAdresse->ville) && !empty($bodyAdresse->ville))
                    ) ? $bodyAdresse->ville : NULL;

                    $reference = (
                        isset($bodyAdresse->reference)
                        && (!is_null($bodyAdresse->reference) && !empty($bodyAdresse->reference))
                    ) ? $bodyAdresse->reference : NULL;

                    $adresse = new Adresse(
                        id: NULL,
                        voie: $voie,
                        quartier: $quartier,
                        commune: $commune,
                        district: $district,
                        ville: $ville,
                        reference: $reference
                    );
                    $adressesArray[] = $adresse->toArray();
                }

                $email = (
                    isset($body->email)
                    && (!is_null($body->email) && !empty($body->email))
                ) ? $body->email : NULL;
                $telephone = (
                    isset($body->telephone)
                    && (!is_null($body->telephone) && !empty($body->telephone))
                ) ? $body->telephone : NULL;
                $type = (
                    isset($body->type)
                    && (!is_null($body->type) && !empty($body->type))
                ) ? $body->type : NULL;

                $site = (
                    isset($body->site)
                    && (!is_null($body->site) && !empty($body->site))
                ) ? $body->site : NULL;

                $ecole = new Ecole(
                    id: NULL,
                    nom: $body->nom,
                    email: $email,
                    telephone: $telephone,
                    type: $type,
                    site: $site,
                    adresses: $adressesArray
                );

                $mapper = new VendorMapper($connexionWrite);
                // Check school exist
                $mapper->findEcoles(ecole: $ecole);

                if ($mapper->rowCount() !== 0) {
                    return $response->json(
                        statusCode: 400,
                        success: false,
                        message: ' School already exists'
                    );
                }

                // Add new Ecole
                $mapper
                    ->ecoleAdd($ecole)
                    ->executInsert();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode: 500,
                        success: false,
                        message: 'Failed to create school'
                    );
                }
                $ecoleid = (int) $mapper->lastInsertId();
                // Add Address
                $mapper->addMultiAddresse(
                    adresses: $ecole->getAdresses(),
                    ecoleid: $ecoleid
                );

                $retrieveRow = $mapper
                    ->findEcoles(id: $ecoleid)
                    ->getResults();
                $rowCounted = $mapper->rowCount();
                $row = current($retrieveRow);

                if ($rowCounted === 0) {
                    return $response->json(
                        statusCode: 500,
                        success: false,
                        message: 'Failed to retrieve school after creation'
                    );
                }
                $adresses = $mapper
                    ->adresseRetrieve(ecoleid: $ecoleid)
                    ->executeQuery()
                    ->getResults();

                $images = $mapper
                    ->imageRetrieve(ecoleid: $ecoleid)
                    ->executeQuery()
                    ->getResults();

                $news = new Ecole(
                    id: $row['id'],
                    nom: $row['nom'],
                    email: $row['email'],
                    telephone: $row['telephone'],
                    type: $row['type'],
                    site: $row['site'],
                    maximage: $row['maximage'],
                    adresses: $adresses ?? [],
                    images: $images ?? []
                );

                $returnData = [];
                $returnData['rows_inserted'] =  $rowCounted;
                $returnData['schools'] = $news->toArray();

                return $response->json(
                    statusCode: 200,
                    success: true,
                    data: $returnData
                );
            } catch (EcoleException | AdresseException $e) {
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: $e->getMessage()
                );
            }
        }
        /**
         * Method getOneAction [GET]
         *
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         * 
         * @return void
         */
        #[Route(path: '/ecoles/([0-9]+)', name: 'ecoles.getOne')]
        public function getOneAction(
            \ApiSchool\V1\Http\Request $request,
            \ApiSchool\V1\Http\Response $response
        ) {
            // Establish the connection Database
            $connexionRead = Connexion::read();
            // Check the Connection Database
            if (!Connexion::is($connexionRead)) {
                return $response->json(
                    statusCode: 500,
                    success: false,
                    message: 'Database connection error'
                );
            }

            $ecoleid = $request->getParam('ecoleid');
            // Check Parameter School Id
            if (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid)) {
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: 'School id cannot be blank or string. It\'s must be numeric'
                );
            }

            try {
                $mapper = new VendorMapper($connexionRead);
                $row = $mapper
                    ->findEcoles(id: $ecoleid)
                    ->getResults();

                $row = current($row);
                $rowCounted = $mapper->rowCount();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode: 500,
                        success: false,
                        message: 'School Not Found.'
                    );
                }

                $adresses = $mapper
                    ->adresseRetrieve(ecoleid: $row['id'])
                    ->executeQuery()
                    ->getResults();

                $images = $mapper
                    ->imageRetrieve(ecoleid: $row['id'])
                    ->executeQuery()
                    ->getResults();

                $ecole = new Ecole(
                    id: $row['id'],
                    nom: $row['nom'],
                    email: $row['email'],
                    telephone: $row['telephone'],
                    type: $row['type'],
                    site: $row['site'],
                    maximage: $row['maximage'],
                    adresses: $adresses ?? [],
                    images: $images ?? []
                );

                $returnData = [];
                $returnData['rows_returned'] = $rowCounted;
                $returnData['school'] = $ecole->toArray();

                return $response->json(
                    statusCode: 200,
                    success: true,
                    toCache: true,
                    data: $returnData
                );
            } catch (EcoleException | AdresseException $ex) {
                return $response->json(
                    statusCode: 500,
                    success: false,
                    message: $ex->getMessage()
                );
            }
        }

        /**
         * Method putOneAction [PUT]
         *
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(path: '/ecoles/([0-9]+)', name: 'ecoles.putOne', method: 'PUT')]
        public function putOneAction(
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
            // Check Authorisation header
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
            
            $accessToken = $request->getHeaderLine('HTTP_AUTHORIZATION');
            $auth = new \ApiSchool\V1\Auth\Auth(token: $accessToken);

            // Check Access Token
            if (!$auth->isValid()) {
                return $response->json(
                    statusCode: 401,
                    success: false,
                    message: 'Invalid access token'
                );
            }
            // Check if user has exceeded maximum login attempts
            if ($auth->getUser()->isLocked()) {
                return $response->json(
                    statusCode: 401, success: false,
                    message: 'User account is currently locked out.'
                );
            }
              // Check refresh token expiration
              if ($auth->getToken()->accessTokenExpired()) {
                return $response->json(
                    statusCode: 401, success: false,
                    message: 'Access token expired.'
                );
            }
            // END Authorisation header

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
            // Check 
            if (!isset($body->nom)) {
                (
                    !isset($body->nom) ?
                    $response->setMessage('Nom field is mandatory and must be provider') : false
                );
                return $response->json(
                    statusCode: 400,
                    success: false
                );
            }

            // Prepare Data
            try {
                $email = (
                    isset($body->email)
                    && (!is_null($body->email) && !empty($body->email))
                ) ? $body->email : NULL;
                $telephone = (
                    isset($body->telephone)
                    && (!is_null($body->telephone) && !empty($body->telephone))
                ) ? $body->telephone : NULL;
                $type = (
                    isset($body->type)
                    && (!is_null($body->type) && !empty($body->type))
                ) ? $body->type : NULL;

                $site = (
                    isset($body->site)
                    && (!is_null($body->site) && !empty($body->site))
                ) ? $body->site : NULL;

                $ecoleid = (int) $request->getParam('ecoleid');
                // Check Parameter School ID
                if (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid)) {
                    return $response->json(
                        statusCode: 400,
                        success: false,
                        message: 'School id cannot be blank or string. It\'s must be numeric'
                    );
                }

                $ecole = new Ecole(
                    id: $ecoleid,
                    nom: $body->nom,
                    email: $email,
                    telephone: $telephone,
                    type: $type,
                    site: $site
                );

                $mapper = new VendorMapper($connexionWrite);

                $row = $mapper
                    ->findEcoles(id: $ecole->getId())
                    ->getResults();
                $row = current($row);
                $rowCounted = $mapper->rowCount();

                if ($rowCounted === 0) {
                    // Add new Ecole
                    $mapper
                        ->ecoleAdd(ecole: $ecole)
                        ->executInsert();

                    if ($mapper->rowCount() === 0) {
                        return $response->json(
                            statusCode: 500,
                            success: false,
                            message: 'Failed to update, school not exist or create new  school'
                        );
                    }
                    $ecoleid = (int) $mapper->lastInsertId();
                    $row = $mapper
                        ->findEcoles(id: $ecoleid)
                        ->getResults();
                    $row = current($row);

                    if ($mapper->rowCount() === 0) {
                        return $response->json(
                            statusCode: 500,
                            success: false,
                            message: 'Failed to retrieve school after creation'
                        );
                    }
                    $adresses = $mapper
                        ->adresseRetrieve(ecoleid: $row['id'])
                        ->executeQuery()
                        ->getResults();

                    $images = $mapper
                        ->imageRetrieve(ecoleid: $row['id'])
                        ->executeQuery()
                        ->getResults();

                    $ecole = new Ecole(
                        id: $row['id'],
                        nom: $row['nom'],
                        email: $row['email'],
                        telephone: $row['telephone'],
                        type: $row['type'],
                        site: $row['site'],
                        maximage: $row['maximage'],
                        adresses: $adresses ?? [],
                        images: $images ?? []
                    );

                    $returnData = [];
                    $returnData['rows_returned'] = $rowCounted;
                    $returnData['school'] = $ecole->toArray();

                    return $response->json(
                        statusCode: 200,
                        success: true,
                        toCache: true,
                        data: $returnData
                    );
                }
                // Retrive school by ID ecoleState
                $ecoleState = Ecole::fromState(data: $row);

                // Prepare Data to Update
                (!is_null($ecole->getNom()) ? $ecoleState->setNom($ecole->getNom()) : false);
                (!is_null($ecole->getEmail()) ? $ecoleState->setEmail($ecole->getEmail()) : false);
                (
                    !is_null($ecole->getTelephone())
                    ? $ecoleState->setTelephone($ecole->getTelephone()) : false
                );
                (!is_null($ecole->getType()) ? $ecoleState->setType($ecole->getType()) : false);
                (!is_null($ecole->getSite()) ? $ecoleState->setSite($ecole->getSite()) : false);

                // Prepare Data to Update
                $mapper
                    ->ecoleUpdate(ecole: $ecoleState)
                    ->executeUpdate();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode: 404,
                        success: false,
                        message: 'School not updated.'
                    );
                }
                // Fetch after Update
                $row = $mapper
                    ->findEcoles(id: $ecoleid)
                    ->getResults();
                $rowCounted = $mapper->rowCount();
                $row = current($row);

                if ($rowCounted === 0) {
                    return $response->json(
                        statusCode: 404,
                        success: false,
                        message: 'No school found after update.'
                    );
                }
                $stateFetched = Ecole::fromState($row);
                $adresses = $mapper
                    ->adresseRetrieve(ecoleid: $row['id'])
                    ->executeQuery()
                    ->getResults();

                $images = $mapper
                    ->imageRetrieve(ecoleid: $row['id'])
                    ->executeQuery()
                    ->getResults();
                $stateFetched->setAdresses(adresses: $adresses);
                $stateFetched->setImages(images: $images);

                $returnData = [];
                $returnData['rows_counted'] =  $rowCounted;
                $returnData['school'] = $stateFetched->toArray();

                return $response->json(
                    statusCode: 200,
                    success: true,
                    message: 'School Updated',
                    data: $returnData
                );
            } catch (EcoleException | AdresseException $e) {
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: $e->getMessage()
                );
            }
        }
        /**
         * Method patchOneAction [PATCH]
         *
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(path: '/ecoles/([0-9]+)', name: 'ecoles.patchOne', method: 'PATCH')]
        public function patchOneAction(
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
            // Check Authorisation header
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
            
            $accessToken = $request->getHeaderLine('HTTP_AUTHORIZATION');
            $auth = new \ApiSchool\V1\Auth\Auth(token: $accessToken);

            // Check Access Token
            if (!$auth->isValid()) {
                return $response->json(
                    statusCode: 401,
                    success: false,
                    message: 'Invalid access token'
                );
            }
            // Check if user has exceeded maximum login attempts
            if ($auth->getUser()->isLocked()) {
                return $response->json(
                    statusCode: 401, success: false,
                    message: 'User account is currently locked out.'
                );
            }
              // Check refresh token expiration
              if ($auth->getToken()->accessTokenExpired()) {
                return $response->json(
                    statusCode: 401, success: false,
                    message: 'Access token expired.'
                );
            }
            // END Authorisation header

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

            // Check 
            if (
                !isset($body->nom)  && !isset($body->email)
                && !isset($body->telephone) && !isset($body->type)
                && !isset($body->site)
            ) {
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: "No fields to update are provided."
                );
            }
            try {
                $nom = (
                    isset($body->nom)
                    && (!is_null($body->nom) && !empty($body->nom))
                ) ? $body->nom : NULL;
                $email = (
                    isset($body->email)
                    && (!is_null($body->email) && !empty($body->email))
                ) ? $body->email : NULL;
                $telephone = (
                    isset($body->telephone)
                    && (!is_null($body->telephone) && !empty($body->telephone))
                ) ? $body->telephone : NULL;
                $type = (
                    isset($body->type)
                    && (!is_null($body->type) && !empty($body->type))
                ) ? $body->type : NULL;

                $site = (
                    isset($body->site)
                    && (!is_null($body->site) && !empty($body->site))
                ) ? $body->site : NULL;

                $ecoleid = (int) $request->getParam('ecoleid');
                // Check Paramater School ID
                if (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid)) {
                    return $response->json(
                        statusCode: 400,
                        success: false,
                        message: 'School id cannot be blank or string. It\'s must be numeric'
                    );
                }

                $ecole = new Ecole(
                    id: $ecoleid,
                    nom: $nom,
                    email: $email,
                    telephone: $telephone,
                    type: $type,
                    site: $site
                );
                $mapper = new VendorMapper($connexionWrite);

                $row = $mapper
                    ->findEcoles(id: $ecole->getId())
                    ->getResults();
                $row = current($row);
                $rowCounted = $mapper->rowCount();

                if ($rowCounted === 0) {
                    return $response->json(
                        statusCode: 404,
                        success: false,
                        message: 'No school Found to update.'
                    );
                }
                $stateFetched = Ecole::fromState($row);
                $adresses = $mapper
                    ->adresseRetrieve(ecoleid: $row['id'])
                    ->executeQuery()
                    ->getResults();

                $images = $mapper
                    ->imageRetrieve(ecoleid: $row['id'])
                    ->executeQuery()
                    ->getResults();
                $stateFetched->setAdresses(adresses: $adresses);
                $stateFetched->setImages(images: $images);

                // Prepare Data to Update
                (!is_null($ecole->getNom()) ? $stateFetched->setNom($ecole->getNom()) : false);
                (!is_null($ecole->getEmail()) ? $stateFetched->setEmail($ecole->getEmail()) : false);
                (
                    !is_null($ecole->getTelephone())
                    ? $stateFetched->setTelephone($ecole->getTelephone()) : false
                );
                (!is_null($ecole->getType()) ? $stateFetched->setType($ecole->getType()) : false);
                (!is_null($ecole->getSite()) ? $stateFetched->setSite($ecole->getSite()) : false);

                $mapper->ecoleUpdate(ecole: $stateFetched)
                    ->executeUpdate();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode: 404,
                        success: false,
                        message: 'School not updated.'
                    );
                }

                $row = $mapper
                    ->findEcoles(id: $ecoleid)
                    ->getResults();
                $rowCounted = $mapper->rowCount();
                $row = current($row);

                // Fetch after Update
                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode: 404,
                        success: false,
                        message: 'No school found after update.'
                    );
                }
                $stateFetched = Ecole::fromState($row);
                $adresses = $mapper
                    ->adresseRetrieve(ecoleid: $row['id'])
                    ->executeQuery()
                    ->getResults();

                $images = $mapper
                    ->imageRetrieve(ecoleid: $row['id'])
                    ->executeQuery()
                    ->getResults();
                $stateFetched->setAdresses(adresses: $adresses);
                $stateFetched->setImages(images: $images);

                $returnData = [];
                $returnData['rows_counted'] = $rowCounted;
                $returnData['school'] =  $stateFetched->toArray();

                return $response->json(
                    statusCode: 200,
                    success: true,
                    message: 'School Updated',
                    data: $returnData
                );
            } catch (EcoleException | AdresseException $e) {
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: $e->getMessage()
                );
            }
        }
        /**
         * Method deleteOneEcole  [DELETE]
         *
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(path: '/ecoles/([0-9]+)', name: 'ecoles.deleteOne', method: 'DELETE')]
        public function deleteOneAction(
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
                    message: 'Database Connection Error'
                );
            }
            // Check Authorisation header
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
            
            $accessToken = $request->getHeaderLine('HTTP_AUTHORIZATION');
            $auth = new \ApiSchool\V1\Auth\Auth(token: $accessToken);

            // Check Access Token
            if (!$auth->isValid()) {
                return $response->json(
                    statusCode: 401,
                    success: false,
                    message: 'Invalid access token'
                );
            }
            // Check if user has exceeded maximum login attempts
            if ($auth->getUser()->isLocked()) {
                return $response->json(
                    statusCode: 401, success: false,
                    message: 'User account is currently locked out.'
                );
            }
              // Check refresh token expiration
              if ($auth->getToken()->accessTokenExpired()) {
                return $response->json(
                    statusCode: 401, success: false,
                    message: 'Access token expired.'
                );
            }
            // END Authorisation header

            $ecoleid = (int) $request->getParam('ecoleid');
            // Check Parameter School ID
            if (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid)) {
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: 'School id cannot be blank or string. It\'s must be numeric'
                );
            }

            try {
                $mapper = new VendorMapper($connexionWrite);
                $mapper->ecoleRemove(id: $ecoleid)
                    ->executeDelete();

                $rowCounted = $mapper->rowCount();

                if ($rowCounted === 0) {
                    return $response->json(
                        statusCode: 404,
                        success: false,
                        message: 'School not found for to delete.'
                    );
                }
                $returnData = [];
                $returnData['rows_deleted'] = $rowCounted;

                return $response->json(
                    statusCode: 200,
                    success: true,
                    message: "School $ecoleid deleted"
                );
            } catch (EcoleException | AdresseException $e) {
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: $e->getMessage()
                );
            }
        }
        /**
         * Method getPageAction [GET]
         *
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(path: '/ecoles/page/([0-9]+)', name: 'ecoles.getPerPage')]
        public function getPageAction(
            \ApiSchool\V1\Http\Request $request,
            \ApiSchool\V1\Http\Response $response
        ) {
            // Establish the connection Database
            $connexionRead = Connexion::read();
            // Check the Connection Database
            if (!Connexion::is($connexionRead)) {
                return $response->json(
                    statusCode: 500,
                    success: false,
                    message: 'Database connection error'
                );
            }
            $page = $request->getParam('page');
            // Check Parameter Page
            if (is_null($page) || !is_numeric($page)) {
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: 'Page number cannot be blank or string. It\'s must be numeric'
                );
            }

            try {
                $mapper = new VendorMapper($connexionRead);

                $counter = $mapper->ecoleCounter();
                // Limit par page;
                $limitPerPage = 3;
                $ecolesCount  = intval($counter);
                $numOfPages   = intval(ceil($ecolesCount / $limitPerPage));
                // First Page
                if ($numOfPages == 0)  $numOfPages = 1;
                if ($numOfPages < $page || 0 == $page) {
                    return $response->json(
                        statusCode: 404,
                        success: false,
                        message: 'Page not found.'
                    );
                }
                // Offset Page
                $offset = (($page == 1) ? 0 : ($limitPerPage * ($page - 1)));

                $rows = $mapper
                    ->findEcolesByLimitAndOffset(limit: $limitPerPage, offset: $offset)
                    ->executeQuery()
                    ->getResults();

                $rowCounted = $mapper->rowCount();
                $stateRows = [];

                if (is_array($rows) && count($rows) > 0) {
                    for ($index = 0; $index < count($rows); $index++) {
                        $row = $rows[$index];

                        $stateFetched = Ecole::fromState($row);
                        $adresses = $mapper
                            ->adresseRetrieve(ecoleid: $row['id'])
                            ->executeQuery()
                            ->getResults();

                        $images = $mapper
                            ->imageRetrieve(ecoleid: $row['id'])
                            ->executeQuery()
                            ->getResults();
                        $stateFetched->setAdresses(adresses: $adresses);
                        $stateFetched->setImages(images: $images);

                        $stateRows[] = $stateFetched->toArray();
                    }
                }

                $returnData = [];
                $returnData['rows_returned'] = $rowCounted;
                $returnData['total_rows'] = $ecolesCount;
                $returnData['total_pages'] = $numOfPages;
                $returnData['has_next_page'] =  ($page < $numOfPages) ? true : false;
                $returnData['has_privious_page'] =  ($page > 1) ? true : false;
                $returnData['schools'] = $stateRows;

                return $response->json(
                    statusCode: 200,
                    success: true,
                    toCache: true,
                    data: $returnData
                );
            } catch (EcoleException | AdresseException $e) {
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: $e->getMessage()
                );
            }
        }
    }
}
