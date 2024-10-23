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
    use App\SchoolManager\Exception\EcoleException;
    use App\SchoolManager\Mapper\ImageMapper;
    use App\SchoolManager\Model\Image;
    use \App\SchoolManager\Exception\ImageException;

    #[Route(path:'/api/v1')]
    class ImageController
    {
        /**
         * Method getImagesAction [GET]
         * 
         * Il permet de recupère les images
         * 
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         *
         * @return mixed
         */
        #[Route(path:'/ecoles/([0-9]+)/images', name:'images.get')]
        public function getImagesAction(
            \App\SchoolManager\Http\Request $request, 
            \App\SchoolManager\Http\Response $response
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

            $ecoleid = $request->getParam('ecoleid');
            // Check Parameter School ID
            if (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid)) {
               return $response->json(
                   statusCode:400, success:false,
                   message:'School id cannot be blank or string. It\'s must be numeric'
               );
            }
            $mapper = new ImageMapper($connexionRead);
            $arrayImages = $mapper->retrieveImagesByEcoleIdArray(ecoleid: $ecoleid);
            if ($mapper->rowCount() === 0) {
                return $response->json(
                    statusCode:500, success:false, 
                    message:'Images Not Found.'
                );
            }

            $returnData = [];
            $returnData['rows_returned'] = $mapper->rowCount();
            $returnData['images'] = $arrayImages;

            return $response->json(
                statusCode:200, success: true,
                toCache: true, data: $returnData
            );
        }
        /**
         * Method postImagesAction [POST]
         *
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(
            path:'/ecoles/([0-9]+)/images', name:'images.post', 
            method:'POST'
        )]
        public function postImagesAction(
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
            if (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid)) {
                return $response->json(
                    statusCode:400, success:false,
                    message:'School id cannot be blank or string. It\'s must be numeric'
                );
            }
            $mapper = new ImageMapper($connexionWrite);
            $ecole = $mapper->retrieveEcole(ecoleid: $ecoleid);

            if ($mapper->rowCount() === 0) {
                return $response->json(
                    statusCode:404, success:false, 
                    message:'School Not Found.'
                );
            }

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
            if (!isset($jsonImagesAttributes->title) || !isset($jsonImagesAttributes->filename)
                || empty($jsonImagesAttributes->title) || empty($jsonImagesAttributes->filename)
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
            if(!$oUFile->isMime(mimes: ['image/jpeg', 'image/gif', 'image/png'])) {
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

            try {
                
                $image = new Image(
                    id: null, title: $jsonImagesAttributes->title, 
                    filename: $jsonImagesAttributes->filename.$oUFile->getImageFileExtension(),
                    mimetype:  $oUFile->getImageMime(),
                    ecoleid: $ecoleid
                );
                $mapper->retrieveImage($image);
                if ($mapper->rowCount() !== 0) {
                    if ($mapper->inTransaction()) {
                        $mapper->rollBack();
                    }
                    return $response->json(
                        statusCode:409, success:false, 
                        message:'A file with that filename already exists - try a different filename'
                    );
                }
                // Start Transaction
                $mapper->beginTransaction();
                $mapper->addImage(image: $image);
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

                $mapper->updateEcole(ecole: $ecole);
                if ($mapper->rowCount() === 0) {
                    if ($mapper->inTransaction()) {
                        $mapper->rollBack();
                    }
                    return $response->json(
                        statusCode:500, success:false, 
                        message:'Failed to update info ecole'
                    );
                }

                $imageRow = $mapper->retrieveImageByEcoleIdAndImageId(
                    ecoleid: $ecoleid, imageid: $lastImageID
                );
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
                    id: $imageRow['id'], 
                    title: $imageRow['title'],
                    filename: $imageRow['filename'], 
                    mimetype: $imageRow['mimetype'],
                    ecoleid: $imageRow['ecoleid']
                );
                
                $newImage->saveImageFile($oUFile->getTmpName());
                $mapper->commit();
                return $response->json(
                    statusCode:200, success: true, 
                    message: 'Image upload successfully',
                    data:  $newImage->toArray()
                );

            } catch (ImageException|EcoleException $ex) {
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
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         *
         * @return mixed
         */
        #[Route(path:'/ecoles/([0-9]+)/images/([0-9]+)', name:'images.getOne')]
        public function getOneImageAction(
            \App\SchoolManager\Http\Request $request, 
            \App\SchoolManager\Http\Response $response
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
            $mapper = new ImageMapper($connexionRead);
            $imageRow = $mapper->retrieveImageByEcoleIdAndImageId(
                ecoleid: $ecoleid, imageid: $imageid
            );

            if ($mapper->rowCount() === 0) {
                return $response->json(
                    statusCode:500, success:false, 
                    message:'Images Not Found.'
                );
            }
            try {
                $image = new Image(
                    id: $imageRow['id'],
                    title: $imageRow['title'],
                    filename: $imageRow['filename'],
                    mimetype: $imageRow['mimetype'],
                    ecoleid:  $imageRow['ecoleid']
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
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(
            path:'/ecoles/([0-9]+)/images/([0-9]+)',
            name:'images.deleteOne', method:'DELETE'
        )]
        public function deleteImagesAction(
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
            $mapper = new ImageMapper($connexionWrite);
            $ecole = $mapper->retrieveEcole(ecoleid: $ecoleid);
            if ($mapper->rowCount() === 0) {
                return $response->json(
                    statusCode:404, success:false, 
                    message:'School with this image not Found.'
                );
            }
            try {
                // Start Transaction
                $mapper->beginTransaction();
                $imageRow = $mapper->retrieveImageByEcoleIdAndImageId(
                    ecoleid: $ecoleid, imageid: $imageid
                );
                if ($mapper->rowCount() === 0) {
                    if ($mapper->inTransaction()) {
                        $mapper->rollBack();
                    }
                    return $response->json(
                        statusCode:404, success:false, 
                        message:'Image Not Found'
                    );
                }
                $image = new Image(
                    id: $imageRow['id'], 
                    title: $imageRow['title'],
                    filename: $imageRow['filename'], 
                    mimetype: $imageRow['mimetype'],
                    ecoleid: $imageRow['ecoleid']
                );

                $mapper->removeImageByIdAndEcoleId(
                    id: $image->getId(),
                    ecoleid: $image->getEcoleid()
                );
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

                $mapper->updateEcole(ecole: $ecole);
                if ($mapper->rowCount() === 0) {
                    if ($mapper->inTransaction()) {
                        $mapper->rollBack();
                    }
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
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(
            path:'/ecoles/([0-9]+)/images/([0-9]+)/attributes',
            name:'images.attributes'
        )]
        public function getImageAttributesAction(
            \App\SchoolManager\Http\Request $request, 
            \App\SchoolManager\Http\Response $response
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
            $mapper = new ImageMapper($connexionRead);
            $imageRow = $mapper->retrieveImageByEcoleIdAndImageId(
                ecoleid: $ecoleid, imageid: $imageid
            );
            if ($mapper->rowCount() === 0) {
                return $response->json(
                    statusCode:500, success:false, 
                    message:'Images Not Found.'
                );
            }
            $returnData = [];
            $returnData['attributes'] =  [
                "title" => $imageRow["title"],
                "filename" => $imageRow["filename"],
                "mimetype" => $imageRow["mimetype"]
            ];
            return $response->json(
                statusCode:200, success:true, 
                data: $returnData
            );

        }
        /**
         * Method patchImageAttributesAction [PATCH]
         *
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(
            path:'/ecoles/([0-9]+)/images/([0-9]+)/attributes',
            name:'images.attributes', method: 'PATCH'
        )]
        public function patchImageAttributesAction(
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
            // Check Field to Update
            if (!isset($body->title) && !isset($body->filename)) {
                return $response->json(
                    statusCode:400, success:false,
                    message: "No fields to update are provided."
                );
            }
            $mapper = new ImageMapper($connexionWrite);
            try {
                // Start Transaction
                $connexionWrite->beginTransaction();

                $imageRow = $mapper->retrieveImageByEcoleIdAndImageId(
                    ecoleid: $ecoleid, imageid: $imageid
                );
                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:500, success:false, 
                        message:'Images Not Found.'
                    );
                }
                $image = new Image(
                    id: $imageRow['id'],
                    title: $imageRow['title'],
                    filename: $imageRow['filename'],
                    mimetype: $imageRow['mimetype'],
                    ecoleid:  $imageRow['ecoleid']
                );
                // Prepare Data to Update
                // Title
                (!is_null($body->title) ? $image->setTitle($body->title): false);

                if (!is_null($body->filename)) {
                    $originalFilename = $image->getFilename();
                    $image->setFilename($body->filename);
                }

                $mapper->updateImage(image: $image);
                if ($mapper->rowCount() === 0) {
                    if ($connexionWrite->inTransaction()) {
                        $connexionWrite->rollBack();
                    }
                    $msg  = 'Image attributes not updated - ';
                    $msg .= 'the given values may be the same as  the stored values ';
                    return $response->json(
                        statusCode:400, success:false, 
                        message: $msg
                    );
                }
                $imageRow = $mapper->retrieveImageByEcoleIdAndImageId(
                    ecoleid: $ecoleid, imageid: $imageid
                );
                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:404, success:false, 
                        message:'No Images found after Update'
                    );
                }
                $image = new Image(
                    id: $imageRow['id'],
                    title: $imageRow['title'],
                    filename: $imageRow['filename'],
                    mimetype: $imageRow['mimetype'],
                    ecoleid:  $imageRow['ecoleid']
                );

                if (isset($body->filename) && !is_null($body->filename)) {
                    $image->renameImageFile(
                        oldFilename: $originalFilename,
                        newFilename: $body->filename
                    );
                }
                $connexionWrite->commit();
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