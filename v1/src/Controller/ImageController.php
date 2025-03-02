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
    use ApiSchool\V1\Exception\EcoleException;
    use ApiSchool\V1\Mapper\VendorMapper;
    use ApiSchool\V1\Model\Image;
    use ApiSchool\V1\Model\Ecole;
    use \ApiSchool\V1\Exception\ImageException;

    #[Route(path:'/api/v1')]
    class ImageController
    {
        /**
         * Method getImagesAction [GET]
         * 
         * Il permet de recupère les images
         * 
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         *
         * @return mixed
         */
        #[Route(path:'/ecoles/([0-9]+)/images', name:'images.get')]
        public function getImagesAction(
            \ApiSchool\V1\Http\Request $request, 
            \ApiSchool\V1\Http\Response $response
        ) {
            // Establish the connection Database
            $connexionRead = Connexion::read();
            // Check the Connection Database
            if (!Connexion::is($connexionRead)){
               return $response->json(
                   statusCode:500, success:false,
                   message:'Database connection error'
               );
            }
            /*
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
            }*/
            // END Authorisation header

            $ecoleid = $request->getParam('ecoleid');
            // Check Parameter School ID
            if (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid)) {
               return $response->json(
                   statusCode:400, success:false,
                   message:'School id cannot be blank or string. It\'s must be numeric'
               );
            }
           
            try {
                $mapper = new VendorMapper($connexionRead);
                $rows = $mapper
                    ->imageRetrieve(ecoleid: $ecoleid)
                    ->executeQuery()
                    ->getResults();
    
                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:500, success:false, 
                        message:'Images Not Found.'
                    );
                }
    
                $returnData = [];
                $returnData['rows_returned'] = $mapper->rowCount();
                $returnData['images'] = $rows;
    
                return $response->json(
                    statusCode:200, success: true,
                    toCache: true, data: $returnData
                );

            } catch (ImageException $e) {
                return $response->json(
                    statusCode:400, success:false, 
                    message:$e->getMessage()
                );
            }
        }
        /**
         * Method postImagesAction [POST]
         *
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(
            path:'/ecoles/([0-9]+)/images', name:'images.post', 
            method:'POST'
        )]
        public function postImagesAction(
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

            if (!isset($_SERVER['CONTENT_TYPE']) 
                || strpos($_SERVER['CONTENT_TYPE'], "multipart/form-data; boundary=") === false
            ) {
                return $response->json(
                    statusCode:400, success:false,
                    message:'Content type header not set to multipart/form-data with a boundary'
                );
            }
            $ecoleid = $request->getParam('ecoleid');
            // Check Parameter School ID AND Address ID
            if (is_null($ecoleid) 
                || empty($ecoleid) 
                || !is_numeric($ecoleid)
            ) {
                return $response->json(
                    statusCode:400, success:false,
                    message:'School id cannot be blank or string. It\'s must be numeric'
                );
            }

            try 
            {
                $mapper = new VendorMapper($connexionWrite);

                $retrieveRow = $mapper
                 ->ecoleRetrieve(id: $ecoleid)
                 ->executeQuery()
                 ->getResults();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:404, success:false, 
                        message:'School Not Found.'
                    );
                }

                $row = current($retrieveRow);
                $ecole = Ecole::fromState($row);

                if (!is_null($ecole->getMaximage()) 
                    && $ecole->isMaximunImage()
                ) {
                    $msg = "You can't add this image, the maximum ";
                    $msg .= "number of images has been reached.";
                    return $response->json(
                        statusCode:400, success:false, 
                        message: $msg
                    );
                }
                
                if (!$request->getParam('attributes')) {
                    return $response->json(
                        statusCode:400, success:false, 
                        message:'Attributes missing from body of request.'
                    );
                }

                 // Check Attribute Value if it's Json
                 if (!$jsonImagesAttributes = json_decode($request->getParam('attributes'))) {
                    return $response->json(
                        statusCode:400, success:false,
                        message:'Attribute field is not valid JSON.'
                    );
                }
                if (!isset($jsonImagesAttributes->title) 
                    || !isset($jsonImagesAttributes->filename)
                    || empty($jsonImagesAttributes->title) 
                    || empty($jsonImagesAttributes->filename)
                ) {
                    return $response->json(
                        statusCode:400, success:false,
                        message:'Title and Filename fields are mandatory.'
                    );
                }

                if (strpos($jsonImagesAttributes->filename, '.') > 0) {
                    return $response->json(
                        statusCode:400, success:false,
                        message:'Filename must not contain a file extension.'
                    );
                }
                // Retrieve Object UploadedFile 
                $oUFile = current($request->getUploadedFiles());
                
                if ((!is_object($oUFile)) || $oUFile->getError() !== 0 ) {
                    return $response->json(
                        statusCode:500, success:false,
                        message:'Image file upload unsuccessful - make sure you select a file.'
                    );
                }
                
                // Check Size File
                if (!is_null($oUFile->getSize()) 
                    && $oUFile->getSize() > 5242880
                ) {
                    return $response->json(
                        statusCode:400, success:false,
                        message:'File must be under 5MB.'
                    );
                }
            
                // Check MIME FILE
                $typeMimes = ['image/jpeg', 'image/gif', 'image/png'];
                if(!$oUFile->isMime(mimes: $typeMimes)) {
                    return $response->json(
                        statusCode:400, success:false,
                        message:'File type not supported.'
                    );
                }
                // Check File Extension
                if (is_null($oUFile->getImageFileExtension())) {
                    return $response->json(
                        statusCode:400, success:false,
                        message:'No valid file extension found for mimetype.'
                    );
                }


                $image = new Image(
                    id: null, title: $jsonImagesAttributes->title, 
                    filename: $jsonImagesAttributes->filename.$oUFile->getImageFileExtension(),
                    mimetype:  $oUFile->getImageMime(),
                    ecoleid: $ecoleid
                );

                $mapper
                    ->imageRetrieve(image: $image)
                    ->executeQuery();

                if ($mapper->rowCount() !== 0) {
                    return $response->json(
                        statusCode:409, success:false, 
                        message:'A file with that filename already exists - try a different filename'
                    );
                }

                // Start Transaction
                $mapper->beginTransaction();
                $mapper->imageAdd(image: $image)
                    ->executInsert();

                if ($mapper->rowCount() === 0) {
                    if ($mapper->inTransaction()) {
                        $mapper->rollBack();
                    }
                    return $response->json(
                        statusCode:500, success:false, 
                        message:'File to upload image'
                    );
                }

                $lastImageID = (int) $mapper->lastInsertId();
                // Update Number Maximum Image
                $maxima = 1 + intval($ecole->getMaximage());
                $ecole->setMaximage(maximage: $maxima);

                $mapper->ecoleUpdate(ecole: $ecole)
                    ->executeUpdate();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:500, success:false, 
                        message:'Failed to update info ecole'
                    );
                }

                $retrieveRow = $mapper
                    ->imageRetrieve(id: $lastImageID, ecoleid: $ecoleid)
                    ->executeQuery()
                    ->getResults();

                $row = current($retrieveRow);

                if ($mapper->rowCount() === 0) {
                    if ($mapper->inTransaction()) {
                        $mapper->rollBack();
                    }
                    return $response->json(
                        statusCode:500, success:false, 
                        message:'Failed to retrieve image attributes after upload - try uploading image aigin'
                    );
                }
               
                $newImage = new Image(
                    id: $row['id'], 
                    title: $row['title'],
                    filename: $row['filename'], 
                    mimetype: $row['mimetype'],
                    ecoleid: $row['ecoleid']
                );
                
                $newImage->saveImageFile($oUFile->getTmpName());
                $mapper->commit();

                return $response->json(
                    statusCode:200, success: true, 
                    message: 'Image upload successfully',
                    data:  $newImage->toArray()
                );
                
            } catch (ImageException|EcoleException $ex ) {
                if ($mapper->inTransaction()) {
                    $mapper->rollBack();
                }
                return $response->json(
                    statusCode:500, success:false, 
                    message: $ex->getMessage()
                );
            }
        }
        /**
         * Method getOneImageAction [GET]
         * 
         * Il permet de recupère les images
         * 
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         *
         * @return mixed
         */
        #[Route(path:'/ecoles/([0-9]+)/images/([0-9]+)', name:'images.getOne')]
        public function getOneImageAction(
            \ApiSchool\V1\Http\Request $request, 
            \ApiSchool\V1\Http\Response $response
        ) {
            // Establish the connection Database
            $connexionRead = Connexion::read();
            // Check the Connection Database
            if (!Connexion::is($connexionRead)){
               return $response->json(
                   statusCode:500, success:false,
                   message:'Database connection error'
               );
            }
            /*
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
            }*/
            // END Authorisation header

            $ecoleid = $request->getParam('ecoleid');
            $imageid = $request->getParam('imageid');
            // Check Parameter School ID AND Image ID
            if (
                (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid))
                || (is_null($imageid) || empty($imageid) || !is_numeric($imageid))
            ) {
                return $response->json(
                    statusCode:400, success:false, 
                    message: 'Image Id or School ID cannot be blank and must be numeric'
                );
            }
           
            try 
            {
                $mapper = new VendorMapper($connexionRead);
                $resultsRows = $mapper
                    ->imageRetrieve(id: $imageid, ecoleid: $ecoleid)
                    ->executeQuery()
                    ->getResults();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:500, success:false, 
                        message:'Images Not Found.'
                    );
                }
                $row = current($resultsRows);
                $image = new Image(
                    id: $row['id'],
                    title: $row['title'],
                    filename: $row['filename'],
                    mimetype: $row['mimetype'],
                    ecoleid:  $row['ecoleid']
                );
                $image->returnImageFile();
            } catch (ImageException $ex) {
                return $response->json(
                    statusCode:500, success:false, 
                    message: $ex->getMessage()
                );
            }
        }

        /**
         * Method postImagesAction [DELETE]
         *
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(
            path:'/ecoles/([0-9]+)/images/([0-9]+)',
            name:'images.deleteOne', method:'DELETE'
        )]
        public function deleteImagesAction(
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
            /*
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
            */
            // END Authorisation header

            $ecoleid = $request->getParam('ecoleid');
            $imageid = $request->getParam('imageid');
            // Check Parameter School ID AND Image ID
            if (
                (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid))
                || (is_null($imageid) || empty($imageid) || !is_numeric($imageid))
            ) {
                return $response->json(
                    statusCode:400, success:false, 
                    message: 'Image Id or Scholl ID cannot be blank and must be numeric'
                );
            }
            try
            {
                $mapper = new VendorMapper($connexionWrite);
                
                $stateEcoleRow = $mapper
                    ->ecoleRetrieve(id: $ecoleid)
                    ->executeQuery()
                    ->getResults();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:404, success:false, 
                        message:'School with this image not Found.'
                    );
                }
                $row = current($stateEcoleRow);
                $ecole = Ecole::fromState($row);
           
                // Start Transaction
                $mapper->beginTransaction();

                $retrieveRow = $mapper
                    ->imageRetrieve(ecoleid: $ecoleid, id: $imageid)
                    ->executeQuery()
                    ->getResults();


                if ($mapper->rowCount() === 0) {
                    if ($mapper->inTransaction()) {
                        $mapper->rollBack();
                    }
                    return $response->json(
                        statusCode:404, success:false, 
                        message:'Image Not Found'
                    );
                }
                $row = current($retrieveRow);
                $image = new Image(
                    id: $row['id'], 
                    title: $row['title'],
                    filename: $row['filename'], 
                    mimetype: $row['mimetype'],
                    ecoleid: $row['ecoleid']
                );

                $mapper
                    ->imageRemove(id: $image->getId(), ecoleid: $ecoleid)
                    ->executeDelete();

                if ($mapper->rowCount() === 0) {
                    if ($mapper->inTransaction()) {
                        $mapper->rollBack();
                    }
                    return $response->json(
                        statusCode:404, success:false, 
                        message:'Image Not Found'
                    );
                }
                // Update Number Maximum Image
                $valMAx = intval($ecole->getMaximage());
                $maxima = ($valMAx > 0)? $valMAx - 1 : $valMAx;
                $ecole->setMaximage(maximage: $maxima);

                $mapper
                    ->ecoleUpdate(ecole: $ecole)
                    ->executeUpdate();
                    
                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:500, success:false, 
                        message:'Failed to update info ecole'
                    );
                }
                $image->deleteImageFile();

                $mapper->commit();

                return $response->json(
                    statusCode:200, success:false, 
                    message:"Image $imageid Deleted"
                );
            } catch (ImageException|EcoleException $ex) {
                if ($connexionWrite->inTransaction()) {
                    $connexionWrite->rollBack();
                }
                return $response->json(
                    statusCode:500, success:false, 
                    message: $ex->getMessage()
                );
            }
        }

        /**
         * Method postImagesAction [GET]
         *
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(
            path:'/ecoles/([0-9]+)/images/([0-9]+)/attributes',
            name:'images.attributes'
        )]
        public function getImageAttributesAction(
            \ApiSchool\V1\Http\Request $request, 
            \ApiSchool\V1\Http\Response $response
        ) {
            // Establish the connection Database
            $connexionRead = Connexion::read();
            // Check the Connection Database
            if (!Connexion::is($connexionRead)){
                return $response->json(
                    statusCode:500, success:false,
                    message:'Database connection error'
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

            $ecoleid = $request->getParam('ecoleid');
            $imageid = $request->getParam('imageid');
            // Check Parameter School ID AND Image ID
            if (
                (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid))
                || (is_null($imageid) || empty($imageid) || !is_numeric($imageid))
            ) {
                return $response->json(
                    statusCode:400, success:false, 
                    message: 'Image Id or School ID cannot be blank and must be numeric'
                );
            }
           
            try 
            {
                $mapper = new VendorMapper($connexionRead);

                $imageRow = $mapper
                    ->imageRetrieve(id: $imageid , ecoleid: $ecoleid)
                    ->executeQuery()
                    ->getResults();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:500, success:false, 
                        message:'Images Not Found.'
                    );
                }
                $row = current($imageRow);
                $returnData = [];
                $stateImage = Image::fromState($row);
                $returnData['attributes'] =  [
                    "title" =>  $stateImage->getTitle(),
                    "filename" => $stateImage->getFilename(),
                    "mimetype" => $stateImage->getMimetype()
                ];
                return $response->json(
                    statusCode:200, success:true, 
                    data: $returnData
                );
               
            } catch (ImageException $ex) {
                return $response->json(
                    statusCode:500, success:false, 
                    message: $ex->getMessage()
                );
            }

        }
        /**
         * Method patchImageAttributesAction [PATCH]
         *
         * @param \ApiSchool\V1\Http\Request $request
         * @param \ApiSchool\V1\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(
            path:'/ecoles/([0-9]+)/images/([0-9]+)/attributes',
            name:'images.attributes', method: 'PATCH'
        )]
        public function patchImageAttributesAction(
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
            $ecoleid = $request->getParam('ecoleid');
            $imageid = $request->getParam('imageid');

            // Check Parameter School ID AND Image ID
            if (
                (is_null($ecoleid) || empty($ecoleid) 
                || !is_numeric($ecoleid)) || (is_null($imageid) 
                || empty($imageid) || !is_numeric($imageid))
            ) {
                return $response->json(
                    statusCode:400, success:false, 
                    message: 'Image Id or School ID cannot be blank and must be numeric'
                );
            }
            // Check Field to Update
            if (!isset($body->title) && !isset($body->filename)) {
                return $response->json(
                    statusCode:400, success:false,
                    message: "No fields to update are provided."
                );
            }

            try 
            {
                $mapper = new VendorMapper($connexionWrite);
                // Start Transaction
                $mapper->beginTransaction();

                $imageRow = $mapper
                    ->imageRetrieve(ecoleid: $ecoleid, id: $imageid)
                    ->executeQuery()
                    ->getResults();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:500, success:false, 
                        message:'Images Not Found.'
                    );
                }
                $row = current($imageRow);
                $image = Image::fromState($row);
                // Prepare Data to Update
                // Title
                (!is_null($body->title) ? $image->setTitle($body->title): false);

                if (!is_null($body->filename)) {
                    $originalFilename = $image->getFilename();
                    $image->setFilename($body->filename);
                }

                $mapper
                    ->imageUpdate(image: $image)
                    ->executeUpdate();
                
                if ($mapper->rowCount() === 0) {
                    if ($mapper->inTransaction()) {
                        $mapper->rollBack();
                    }
                    $msg  = 'Image attributes not updated - ';
                    $msg .= 'the given values may be the same as  the stored values ';
                    return $response->json(
                        statusCode:400, success:false, 
                        message: $msg
                    );
                }
                $imageRow = $mapper
                    ->imageRetrieve(ecoleid: $ecoleid, id: $imageid)
                    ->executeQuery()
                    ->getResults();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:404, success:false, 
                        message:'No Images found after Update'
                    );
                }
                $row = current($imageRow);
                $image = Image::fromState($row);

                if (isset($body->filename) && !is_null($body->filename)) {
                    $image->renameImageFile(
                        oldFilename: $originalFilename,
                        newFilename: $body->filename
                    );
                }
                $mapper->commit();

                $returnData = [];
                $returnData['image'] =  $image->toArray();
                return $response->json(
                    statusCode:200, success:true, 
                    message: 'Image attributes updated',
                    data: $returnData
                );
            } catch (ImageException $ex) {
                if ($connexionWrite->inTransaction()) {
                    $connexionWrite->rollBack();
                }
                return $response->json(
                    statusCode:500, success:false, 
                    message: $ex->getMessage()
                );
            }
        }
    }
}