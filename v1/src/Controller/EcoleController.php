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
    use App\SchoolManager\Mapper\EcoleMapper;
    use App\SchoolManager\Exception\EcoleException;
    use App\SchoolManager\Model\Ecole;
    use App\SchoolManager\Attribute\Route;
    use App\SchoolManager\Exception\ImageException;
    use App\SchoolManager\Model\Adresse;
    use App\SchoolManager\Model\Image;

    #[Route(path:'/api/v1')]
    class EcoleController
    {
        /**
         * Method getAction [GET]
         * 
         * Il permet de recupère les écoles
         * 
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         *
         * @return mixed
         */
        #[Route(path:'/ecoles', name:'ecoles.get')]
        public function getAction(
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
            $mapper = new EcoleMapper($connexionRead);
                    
            try {
                $ecoleRows = $mapper->retrieveEcolesArray();
                if (!$ecoleRows || $mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:500, success:false,
                        message:'Failed to get Schools.'
                    );
                }
                $returnData = [];
                $returnData['rows_returned'] = $mapper->rowCount();
                $returnData['schools'] = $ecoleRows;

                return $response->json(
                    statusCode:200, success: true,
                    toCache: true, data:  $returnData
                );
            } catch (EcoleException|ImageException $ex) {
                return $response->json(
                    statusCode:500, success:false, 
                    message: $ex->getMessage()
                );
            }
        }
        
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
        #[Route(path:'/ecoles', name:'ecoles.post', method: 'POST')]
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

            // Check 
            if (!isset($body->nom) || !isset($body->adresses) 
                || (isset($body->adresses) && !is_array($body->adresses))
            ) {
                (!isset($body->nom)? $response->setMessage('Nom field is mandatory and must be provider'): false);
                (!isset($body->adresses)
                    ? $response->setMessage('Adresses field is mandatory and must be provider'): false
                );
                ((isset($body->adresses) && !is_array($body->adresses))
                    ? $response->setMessage('Adresses field should be Array'): false
                );
                return $response->json(statusCode:400, success:false);
            }
            $bodyAdresses = $body->adresses;

            if (count($bodyAdresses) == 0) {
                ((count($bodyAdresses) == 0)? 
                    $response->setMessage('The fields of Adresses is mandatory and must be provider'): false
                );
                return $response->json(statusCode:400, success:false);
            } else {
                for ($index = 0; $index < count($bodyAdresses); $index++) { 
                    $bodyAdresse = $bodyAdresses[$index];
                    if (!is_object($bodyAdresse)
                        || ( is_object($bodyAdresse) && !isset($bodyAdresse->voie))
                        || ( is_object($bodyAdresse) && !isset($bodyAdresse->quartier))
                        || ( is_object($bodyAdresse) && !isset($bodyAdresse->commune))
                        || ( is_object($bodyAdresse) && !isset($bodyAdresse->district))
                        || ( is_object($bodyAdresse) && !isset($bodyAdresse->ville))
                    ) {
                        (!is_object($bodyAdresse) ? 
                            $response->setMessage('The data in the Field Addresses must be objects'): false
                        );
                        ((is_object($bodyAdresse) && !isset($bodyAdresse->voie)) ? 
                            $response->setMessage('Voie Adresses field  is mandatory and must be provider'): false
                        );
                        ((is_object($bodyAdresse) && !isset($bodyAdresse->quartier))? 
                            $response->setMessage('Quartier Adresses field  is mandatory and must be provider'): false
                        );
                        ((is_object($bodyAdresse) && !isset($bodyAdresse->commune))? 
                            $response->setMessage('Commune Adresses field  is mandatory and must be provider'): false
                        );
                        ((is_object($bodyAdresse) && !isset($bodyAdresse->district))? 
                            $response->setMessage('District Adresses  field  is mandatory and must be provider'): false
                        );
                        ((is_object($bodyAdresse) && !isset($bodyAdresse->ville))? 
                            $response->setMessage('Ville Adresses field  is mandatory and must be provider'): false
                        );
                        return $response->json(statusCode:400, success:false);
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
                    ) ? $bodyAdresse->voie: NULL;
                    $quartier = (
                        isset($bodyAdresse->quartier) 
                        && (!is_null($bodyAdresse->quartier) && !empty($bodyAdresse->quartier))
                    ) ? $bodyAdresse->quartier: NULL;
                    $commune = (
                        isset($bodyAdresse->commune) 
                        && (!is_null($bodyAdresse->commune) && !empty($bodyAdresse->commune))
                    ) ? $bodyAdresse->commune: NULL;
                    $district = (
                        isset($bodyAdresse->district) 
                        && (!is_null($bodyAdresse->district) && !empty($bodyAdresse->district))
                    ) ? $bodyAdresse->district: NULL;

                    $ville = (
                        isset($bodyAdresse->ville) 
                        && (!is_null($bodyAdresse->ville) && !empty($bodyAdresse->ville))
                    ) ? $bodyAdresse->ville: NULL;

                    $reference = (
                        isset($bodyAdresse->reference) 
                        && (!is_null($bodyAdresse->reference) && !empty($bodyAdresse->reference))
                    ) ? $bodyAdresse->reference: NULL;

                    $adresse = new Adresse(
                        id: NULL, voie: $voie, quartier: $quartier, 
                        commune: $commune, district: $district, 
                        ville: $ville, reference: $reference
                    );
                    $adressesArray[]= $adresse->toArray();
                }
                
                $email = (
                    isset($body->email) 
                    && (!is_null($body->email) && !empty($body->email))
                ) ? $body->email: NULL;
                $telephone = (
                    isset($body->telephone) 
                    && (!is_null($body->telephone) && !empty($body->telephone))
                ) ? $body->telephone: NULL;
                $type = (
                    isset($body->type) 
                    && (!is_null($body->type) && !empty($body->type))
                ) ? $body->type: NULL;

                $site = (
                    isset($body->site) 
                    && (!is_null($body->site) && !empty($body->site))
                ) ? $body->site: NULL;

                $ecole = new Ecole(
                    id: NULL, nom: $body->nom, email: $email,
                    telephone: $telephone, type: $type,
                    site: $site, adresses: $adressesArray
                );
            } catch (EcoleException $e) {
                return $response->json(statusCode:400, success:false, message:$e->getMessage());
            }
            
           $mapper = new EcoleMapper($connexionWrite);
            // Vérifier l'existance de l'école
           $mapper->retrieveEcoleByName($ecole->getNom());
            if ($mapper->rowCount() !== 0) {
                return $response->json(
                    statusCode: 400, success:false, 
                    message:' School already exists'
                );
            }

            // Add New Ecole
            $mapper->addEcole(ecole: $ecole);
            if ($mapper->rowCount() === 0) {
                return $response->json(
                    statusCode:500, success:false, 
                    message:'Failed to create school'
                );
            }
    
            $ecoleid = $mapper->getStockId();
            if ($mapper->rowCount() === 0 || !is_numeric($ecoleid)) {
                return $response->json(
                    statusCode:500, success:false, 
                    message:'Failed to retrieve school after creation'
                );
            }
            $ecole = $mapper->retrieveEcole(ecoleid: $ecoleid);
            $returnData = [];
            $returnData['rows_inserted'] = $mapper->rowCount();
            $returnData['schools'] = $ecole->toArray();
            return $response->json(
                statusCode:200, success:true, 
                data: $returnData
            );
        }
        /**
         * Method getOneAction [GET]
         *
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return void
         */
        #[Route(path:'/ecoles/([0-9]+)', name:'ecoles.getOne')]
        public function getOneAction(
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
            // Check Parameter School Id
            if (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid)) {
                return $response->json(
                    statusCode:400, success:false,
                    message:'School id cannot be blank or string. It\'s must be numeric'
                );
            }
            $mapper = new EcoleMapper($connexionRead);
            $ecole = $mapper->retrieveEcole(ecoleid: $ecoleid);

            if ($mapper->rowCount() === 0) {
                return $response->json(
                    statusCode:500, success:false, 
                    message:'School Not Found.'
                );
            }

            $returnData = [];
            $returnData['rows_returned'] = $mapper->rowCount();
            $returnData['school'] = $ecole->toArray();

            return $response->json(
                statusCode:200, success: true,
                toCache: true, data: $returnData
            );
        }
        
        /**
         * Method putOneAction [PUT]
         *
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(path:'/ecoles/([0-9]+)', name:'ecoles.putOne', method: 'PUT')]
        public function putOneAction(
            \App\SchoolManager\Http\Request $request, 
            \App\SchoolManager\Http\Response $response
        ) 
        {
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
            // Check 
            if (!isset($body->nom) || !isset($body->adresses) 
                || (isset($body->adresses) && !is_array($body->adresses))
            ) {
                (!isset($body->nom)?
                $response->setMessage('Nom field is mandatory and must be provider'): false
                );
                (!isset($body->adresses)
                    ? $response->setMessage('Adresses field is mandatory and must be provider'): false
                );
                ((isset($body->adresses) 
                    && !is_array($body->adresses)
                    )? $response->setMessage('Adresses field should be Array'): false
                );
                return $response->json(statusCode:400, success:false);
            }
            $bodyAdresses = $body->adresses;

            if (count($bodyAdresses) == 0) {
                ((count($bodyAdresses) == 0)? 
                    $response->setMessage('The fields of Adresses is mandatory and must be provider'): false
                );
                return $response->json(statusCode:400, success:false);
            } else {
                for ($index = 0; $index < count($bodyAdresses); $index++) { 
                    $bodyAdresse = $bodyAdresses[$index];
                    if (!is_object($bodyAdresse)
                        || ( is_object($bodyAdresse) && !isset($bodyAdresse->voie))
                        || ( is_object($bodyAdresse) && !isset($bodyAdresse->quartier))
                        || ( is_object($bodyAdresse) && !isset($bodyAdresse->commune))
                        || ( is_object($bodyAdresse) && !isset($bodyAdresse->district))
                        || ( is_object($bodyAdresse) && !isset($bodyAdresse->ville))
                    ) {
                        (!is_object($bodyAdresse) ? 
                            $response->setMessage('The data in the Field Addresses must be objects'): false
                        );
                        ((is_object($bodyAdresse) && !isset($bodyAdresse->voie)) ? 
                            $response->setMessage('Voie Adresses field  is mandatory and must be provider'): false
                        );
                        ((is_object($bodyAdresse) && !isset($bodyAdresse->quartier))? 
                            $response->setMessage('Quartier Adresses field  is mandatory and must be provider'): false
                        );
                        ((is_object($bodyAdresse) && !isset($bodyAdresse->commune))? 
                            $response->setMessage('Commune Adresses field  is mandatory and must be provider'): false
                        );
                        ((is_object($bodyAdresse) && !isset($bodyAdresse->district))? 
                            $response->setMessage('District Adresses  field  is mandatory and must be provider'): false
                        );
                        ((is_object($bodyAdresse) && !isset($bodyAdresse->ville))? 
                            $response->setMessage('Ville Adresses field  is mandatory and must be provider'): false
                        );
                        return $response->json(statusCode:400, success:false);
                    }
                }
            }

            try {
                $adressesArray = [];
                for ($index = 0; $index < count($bodyAdresses); $index++) { 
                    $bodyAdresse = $bodyAdresses[$index];
                    $adresseId = (
                        isset($bodyAdresse->id) && (!is_null($bodyAdresse->id))
                    ) ? $bodyAdresse->id: NULL;
                    $voie = (
                        isset($bodyAdresse->voie) 
                        && (!is_null($bodyAdresse->voie) && !empty($bodyAdresse->voie))
                    ) ? $bodyAdresse->voie: NULL;
                    $quartier = (
                        isset($bodyAdresse->quartier) 
                        && (!is_null($bodyAdresse->quartier) && !empty($bodyAdresse->quartier))
                    ) ? $bodyAdresse->quartier: NULL;
                    $commune = (
                        isset($bodyAdresse->commune) 
                        && (!is_null($bodyAdresse->commune) && !empty($bodyAdresse->commune))
                    ) ? $bodyAdresse->commune: NULL;
                    $district = (
                        isset($bodyAdresse->district) 
                        && (!is_null($bodyAdresse->district) && !empty($bodyAdresse->district))
                    ) ? $bodyAdresse->district: NULL;

                    $ville = (
                        isset($bodyAdresse->ville) 
                        && (!is_null($bodyAdresse->ville) && !empty($bodyAdresse->ville))
                    ) ? $bodyAdresse->ville: NULL;

                    $reference = (
                        isset($bodyAdresse->reference) 
                        && (!is_null($bodyAdresse->reference) && !empty($bodyAdresse->reference))
                    ) ? $bodyAdresse->reference: NULL;

                    $adresse = new Adresse(
                        id: $adresseId, voie: $voie, quartier: $quartier, 
                        commune: $commune, district: $district, 
                        ville: $ville, reference: $reference
                    );
                    $adressesArray[]= $adresse->toArray();
                }
                
                $email = (
                    isset($body->email) 
                    && (!is_null($body->email) && !empty($body->email))
                ) ? $body->email: NULL;
                $telephone = (
                    isset($body->telephone) 
                    && (!is_null($body->telephone) && !empty($body->telephone))
                ) ? $body->telephone: NULL;
                $type = (
                    isset($body->type) 
                    && (!is_null($body->type) && !empty($body->type))
                ) ? $body->type: NULL;

                $site = (
                    isset($body->site) 
                    && (!is_null($body->site) && !empty($body->site))
                ) ? $body->site: NULL;

                $ecole = new Ecole(
                    id: NULL, nom: $body->nom, email: $email,
                    telephone: $telephone, type: $type,
                    site: $site, adresses: $adressesArray
                );
            } catch (EcoleException $e) {
                return $response->json(statusCode:400, success:false, message:$e->getMessage());
            }

            $ecoleid = (int) $request->getParam('ecoleid');
            // Check Parameter School ID
            if (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid)) {
                return $response->json(
                    statusCode:400, success:false,
                    message:'School id cannot be blank or string. It\'s must be numeric'
                );
            }
            $mapper = new EcoleMapper($connexionWrite);

            // Retrive school by ID
            $ecoleRetrieved = $mapper->retrieveEcole(ecoleid: $ecoleid);

            if ($mapper->rowCount() === 0) 
            {
                // Add New Ecole
                $mapper->addEcole(ecole: $ecole);
                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:500, success:false, 
                        message:'Failed to update or create school'
                    );
                }
                $ecoleStockID = $mapper->getStockId();
                $retrieveNewEcole = $mapper->retrieveEcole(ecoleid: $ecoleStockID);
                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:500, success:false, 
                        message:'Failed to retrieve school after creation'
                    );
                }

                $returnData = [];
                $returnData['rows_inserted'] = $mapper->rowCount();
                $returnData['schools'] = $retrieveNewEcole->toArray();
                return $response->json(
                    statusCode:201, success:true, 
                    data: $returnData
                );
            }
            // Prepare Data to Update
            (!is_null($ecole->getNom())?$ecoleRetrieved->setNom($ecole->getNom()): false );
            (!is_null($ecole->getEmail())?$ecoleRetrieved->setEmail($ecole->getEmail()): false);
            (
                !is_null($ecole->getTelephone())
                ?$ecoleRetrieved->setTelephone($ecole->getTelephone()): false 
            );
            (!is_null($ecole->getType())?$ecoleRetrieved->setType($ecole->getType()): false);
            (!is_null($ecole->getSite())?$ecoleRetrieved->setSite($ecole->getSite()): false);

            if (count($ecole->getAdresses()) > 0) {
                $adressesArray = [];
                $adressesAlterArray =  $ecoleRetrieved->getAdresses();
                $adresseRows = $ecole->getAdresses();
                for ($index=0; $index < count($adresseRows); $index++) { 
                    $adresseRow = $adresseRows[$index];
                    $key = array_search(
                        $adresseRow['id'], 
                        array_column($adressesAlterArray, 'id')
                    );
                    if (isset($adressesAlterArray[$key])) {
                        $adresseAlter = $adressesAlterArray[$key];
                        (isset($adresseRow['voie']) && !is_null($adresseRow['voie']))
                            ? $adresseAlter['voie'] = $adresseRow['voie']  : false;
                        (isset($adresseRow['quartier']) && !is_null($adresseRow['quartier']))
                            ? $adresseAlter['quartier'] = $adresseRow['quartier']  : false;
                        (isset($adresseRow['commune']) && !is_null($adresseRow['commune']))
                            ? $adresseAlter['commune'] = $adresseRow['commune']  : false;
                        (isset($adresseRow['district']) && !is_null($adresseRow['district']))
                            ? $adresseAlter['district'] = $adresseRow['district']  : false;
                        (isset($adresseRow['ville']) && !is_null($adresseRow['ville']))
                            ? $adresseAlter['ville'] = $adresseRow['ville']  : false;
                        (isset($adresseRow['reference']) && !is_null($adresseRow['reference']))
                            ? $adresseAlter['reference'] = $adresseRow['reference']  : false;

                        $adressesArray[] = $adresseAlter;
                    }
                    $ecoleRetrieved->setAdresses($adressesArray);
                }

            }
            // Update Ecole 
            $mapper->updateEcoleAndAdresses(ecole: $ecoleRetrieved);  
            if ($mapper->rowCount() === 0) {
                return $response->json(
                    statusCode:404, success:false, 
                    message:'School not updated.'
                );
            }
            // Fetch after Update
            $ecoleFetched = $mapper->retrieveEcole(ecoleid: $ecoleid);

            if ($mapper->rowCount() === 0) {
                return $response->json(
                    statusCode:404, success:false, 
                    message:'No school found after update.'
                );
            }
            $returnData = [];
            $returnData['rows_counted'] = $mapper->rowCount();
            $returnData['school'] = $ecoleFetched->toArray();
            return $response->json(
                statusCode:200, success:true,
                message: 'School Updated',
                data: $returnData
            );
        }
        /**
         * Method patchOneAction [PATCH]
         *
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(path:'/ecoles/([0-9]+)', name:'ecoles.patchOne', method: 'PATCH')]
        public function patchOneAction(
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

            // Check 
            if (!isset($body->nom)  && !isset($body->email) 
                && !isset($body->telephone) && !isset($body->type) 
                && !isset($body->site) 
            ) {
                return $response->json(
                    statusCode:400, success:false,
                    message: "No fields to update are provided."
                );
            }
            try {
                $nom = (
                    isset($body->nom) 
                    && (!is_null($body->nom) && !empty($body->nom))
                ) ? $body->nom: NULL;
                $email = (
                    isset($body->email) 
                    && (!is_null($body->email) && !empty($body->email))
                ) ? $body->email: NULL;
                $telephone = (
                    isset($body->telephone) 
                    && (!is_null($body->telephone) && !empty($body->telephone))
                ) ? $body->telephone: NULL;
                $type = (
                    isset($body->type) 
                    && (!is_null($body->type) && !empty($body->type))
                ) ? $body->type: NULL;

                $site = (
                    isset($body->site) 
                    && (!is_null($body->site) && !empty($body->site))
                ) ? $body->site: NULL;

                $ecole = new Ecole(
                    id: NULL, nom: $nom, email: $email,
                    telephone: $telephone, type: $type,
                    site: $site
                );

            } catch (EcoleException $e) {
                return $response->json(statusCode:400, success:false, message:$e->getMessage());
            }

        
            $ecoleid = (int) $request->getParam('ecoleid');
            // Check Paramater School ID
            if (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid)) {
                return $response->json(
                    statusCode:400, success:false,
                    message:'School id cannot be blank or string. It\'s must be numeric'
                );
            }
            $mapper = new EcoleMapper($connexionWrite);

            // Retrive school by ID
            $ecoleRetrieved = $mapper->retrieveEcole(ecoleid: $ecoleid);

            if ($mapper->rowCount() === 0) {
                return $response->json(
                    statusCode:404, success:false, 
                    message:'No school Found to update.'
                );
            }

            // Prepare Data to Update
            (!is_null($ecole->getNom())?$ecoleRetrieved->setNom($ecole->getNom()): false );
            (!is_null($ecole->getEmail())?$ecoleRetrieved->setEmail($ecole->getEmail()): false);
            (
                !is_null($ecole->getTelephone())
                ?$ecoleRetrieved->setTelephone($ecole->getTelephone()): false 
            );
            (!is_null($ecole->getType())?$ecoleRetrieved->setType($ecole->getType()): false);
            (!is_null($ecole->getSite())?$ecoleRetrieved->setSite($ecole->getSite()): false);

            $mapper->updateEcoleAndAdresses(ecole: $ecoleRetrieved);  

            if ($mapper->rowCount() === 0) {
                return $response->json(
                    statusCode:404, success:false, 
                    message:'School not updated.'
                );
            }

            // Fetch after Update
            $ecole = $mapper->retrieveEcole(ecoleid: $ecoleid);

            if ($mapper->rowCount() === 0) {
                return $response->json(
                    statusCode:404, success:false, 
                    message:'No school found after update.'
                );
            }
            $returnData = [];
            $returnData['rows_counted'] = $mapper->rowCount();
            $returnData['school'] = $ecole->toArray();
            return $response->json(
                statusCode:200, success:true,
                message: 'School Updated',
                data: $returnData
            );
        }
        /**
         * Method deleteOneEcole  [DELETE]
         *
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(path:'/ecoles/([0-9]+)', name:'ecoles.deleteOne', method: 'DELETE')]
        public function deleteOneAction(
            \App\SchoolManager\Http\Request $request, 
            \App\SchoolManager\Http\Response $response
        ) {
            // Establish the connection Database
            $connexionWrite = Connexion::write();
            // Check the Connection Database
            if (!Connexion::is($connexionWrite)){
                return $response->json(
                    statusCode:500, success:false,
                    message:'Database Connection Error'
                );
            }
            $ecoleid = (int) $request->getParam('ecoleid');
            // Check Parameter School ID
            if (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid)) {
                return $response->json(
                    statusCode:400, success:false,
                    message:'School id cannot be blank or string. It\'s must be numeric'
                );
            }

            $mapper = new EcoleMapper($connexionWrite);
            $mapper->removeEcoleById(id: $ecoleid);

            if ($mapper->rowCount() === 0) {
                return $response->json(
                    statusCode:404, success:false, 
                    message:'School not found for to delete.'
                );
            }
            $returnData = [];
            $returnData['rows_deleted'] = $mapper->rowCount();
            return $response->json(
                statusCode:200, success: true, 
                message: "School $ecoleid deleted"
            );
        }
        /**
         * Method getPageAction [GET]
         *
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(path:'/ecoles/page/([0-9]+)', name:'ecoles.getPerPage')]
        public function getPageAction(
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
 
            $page = $request->getParam('page');
            // Check Parameter Page
            if (is_null($page) || !is_numeric($page)) {
                return $response->json(
                    statusCode:400, success:false,
                    message:'Page number cannot be blank or string. It\'s must be numeric'
                );
            }
            $mapper = new EcoleMapper($connexionRead);
            // Limit par page;
            $limitPerPage = 10;
            $ecolesCount  = intval($mapper->count());
            $numOfPages   = intval(ceil($ecolesCount / $limitPerPage));

            // First Page
            if ($numOfPages == 0)  $numOfPages = 1; 

            if ( $numOfPages < $page || 0 == $page ) {
                return $response->json(
                    statusCode:404, success:false,
                    message:'Page not found.'
                );
            }
            // Offset Page
            $offset = (($page == 1) ? 0 : ($limitPerPage * ($page - 1)));

            $ecoleArray = $mapper->retrieveEcolesByLimitAndOffset($limitPerPage, $offset);
            $returnData = [];
            $returnData['rows_returned'] = $mapper->rowCount();
            $returnData['total_rows'] = $ecolesCount;
            $returnData['total_pages'] = $numOfPages;
            $returnData['has_next_page'] =  ($page < $numOfPages) ? true : false;
            $returnData['has_privious_page'] =  ($page > 1) ? true : false;
            $returnData['schools'] = $ecoleArray;

            return $response->json(
                statusCode:200, success:true, toCache: true,
                data: $returnData
            );
        }
    }
}