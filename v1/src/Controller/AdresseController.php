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
    use App\SchoolManager\Exception\AdresseException;
    use App\SchoolManager\Attribute\Route;
    use App\SchoolManager\Mapper\VendorMapper;
    use App\SchoolManager\Model\Adresse;

    #[Route(path:'/api/v1')]
    class AdresseController
    {
        /**
         * Method getAdressesAction [GET]
         *
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(path:'/ecoles/([0-9]+)/adresses', name:'adresses.get')]
        public function getAdressesAction(
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

            $mapper = new VendorMapper($connexionRead);
            $rows = $mapper
                ->adresseRetrieve(ecoleid: $ecoleid)
                ->executeQuery()
                ->getResults();

            if ($mapper->rowCount() === 0) {
                return $response->json(
                    statusCode:500, success:false, 
                    message:'Adresse Not Found.'
                );
            }
            $returnData = [];
            $returnData['rows_returned'] = $mapper->rowCount();
            $returnData['adressses'] = $rows;

            return $response->json(
                statusCode:200, success: true,
                toCache: true, data: $returnData
            );
        }
        /**
         * Method postAdressesAction [POST]
         *
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(
            path:'/ecoles/([0-9]+)/adresses', name:'adresse.post', 
            method:'POST'
        )]
        public function postAdressesAction(
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
            // Check Parameter School ID AND Address ID
            if (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid)) {
                return $response->json(
                    statusCode:400, success:false,
                    message:'School id cannot be blank or string. It\'s must be numeric'
                );
            }
            // Check 
            if (!isset($body->voie) || !isset($body->quartier)
                || !isset($body->commune) || !isset($body->district)
                || !isset($body->ville)
            ) {
                // Voie
                (  
                    !isset($body->voie) ? 
                    $response->setMessage(
                        'Voie field is mandatory and must be provider'
                    ) : false
                );
                // Quartier
                (
                    !isset($body->quartier)? 
                    $response->setMessage(
                        'Quartier field is mandatory and must be provider'
                    ) : false
                );
                // Commune
                (
                    !isset($body->commune)? 
                    $response->setMessage(
                        'Commune field is mandatory and must be provider'
                    ): false
                );
                // District
                (
                    !isset($body->district)? 
                    $response->setMessage(
                        'District field is mandatory and must be provider'
                    ): false
                );
                // Ville
                (
                    !isset($body->ville)? 
                    $response->setMessage(
                        'Ville field is mandatory and must be provider'
                    ): false
                );
                return $response->json(statusCode:400, success:false);
            }
            // Prepare Data
            try {

                $voie = (
                    isset($body->voie) 
                    && (!is_null($body->voie) && !empty($body->voie))
                ) ? $body->voie: NULL;
                $quartier = (
                    isset($body->quartier) 
                    && (!is_null($body->quartier) && !empty($body->quartier))
                ) ? $body->quartier: NULL;
                $commune = (
                    isset($body->commune) 
                    && (!is_null($body->commune) && !empty($body->commune))
                ) ? $body->commune: NULL;
                $district = (
                    isset($body->district) 
                    && (!is_null($body->district) && !empty($body->district))
                ) ? $body->district: NULL;

                $ville = (
                    isset($body->ville) 
                    && (!is_null($body->ville) && !empty($body->ville))
                ) ? $body->ville: NULL;

                $reference = (
                    isset($body->reference) 
                    && (!is_null($body->reference) && !empty($body->reference))
                ) ? $body->reference: NULL;

                $adresse = new Adresse(
                    id: NULL, voie: $voie, 
                    quartier: $quartier, 
                    commune: $commune, 
                    district: $district, 
                    ville: $ville, 
                    reference: $reference, 
                    ecoleid: $ecoleid
                );

                $mapper = new VendorMapper($connexionWrite);
                // (adresse: $adresse
                $mapper
                    ->adresseRetrieve(adresse: $adresse)
                    ->executeQuery();

                if ($mapper->rowCount() !== 0) {    
                    return $response->json(
                        statusCode: 400, success:false, 
                        message:' Address already exists'
                    );
                }
                // Start Transaction
                $mapper->beginTransaction();

                $mapper->adresseAdd(adresse: $adresse)
                    ->executInsert();

                if ($mapper->rowCount() === 0) {   
                    if ($mapper->inTransaction()) {
                        $mapper->rollBack();
                    } 
                    return $response->json(
                        statusCode: 400, success:false, 
                        message:'Failed to add address.'
                    );
                }
                $adresseId = (int) $mapper->lastInsertId();

                $row = $mapper
                    ->adresseRetrieve(id: $adresseId)
                    ->executeQuery()
                    ->getResults();
    
                if ($mapper->rowCount() === 0) {   
                    if ($mapper->inTransaction()) {
                        $mapper->rollBack();
                    }  
                    return $response->json(
                        statusCode: 400, success:false, 
                        message:'Failed to retrieve address after to create.'
                    );
                }
                $row = current($row);
                $stateAdresse= Adresse::fromState(data: $row);

                $returnData = [];
                $returnData['rows_returned'] = $mapper->rowCount();
                $returnData['adressses'] = $stateAdresse->toArray();

                $mapper->commit();

                return $response->json(
                    statusCode:200, success: true,
                    toCache: true, data: $returnData
                );
               
            } catch (AdresseException $e) {
                return $response->json(
                    statusCode:400, success:false, 
                    message:$e->getMessage()
                );
            }
        }
        /**
         * Method getOneAdresseAction [GET]
         *
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(path:'/ecoles/([0-9]+)/adresses/([0-9]+)', name:'adresses.getOne')]
        public function getOneAdresseAction(
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
            $adresseid = $request->getParam('adresseid');
            // Check Parameter School ID
            if (
                (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid))
                || (is_null($adresseid) || empty($adresseid) || !is_numeric($adresseid))
            ) {
                (
                    is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid)
                ) ? $response->setMessage(
                    'School ID cannot be blank or string. It\'s must be numeric'
                ): false;
                (
                    is_null($adresseid) || empty($adresseid) || !is_numeric($adresseid)
                ) ? $response->setMessage(
                    'Address school ID cannot be blank or string. It\'s must be numeric'
                ): false;

                return $response->json(statusCode:400, success:false);
            }

            $mapper = new VendorMapper($connexionRead);

            $retrievedRow= $mapper
                ->adresseRetrieve(id: $adresseid, ecoleid: $ecoleid)
                ->executeQuery()
                ->getResults();

            if ($mapper->rowCount() === 0) {
                return $response->json(
                    statusCode:404, success:false, 
                    message:'Adresse Not Found.'
                );
            }
            $returnData = [];
            $returnData['rows_returned'] = $mapper->rowCount();
            $returnData['adressses'] = $retrievedRow;

            return $response->json(
                statusCode:200, success: true,
                toCache: true, data: $returnData
            );
        }
        /**
         * Method putOneAdresseAction [PUT]
         *
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(
            path:'/ecoles/([0-9]+)/adresses/([0-9]+)', 
            name:'adresses.putOne', method:"PUT"
        )]
        public function putOneAdresseAction(
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
            $adresseid = $request->getParam('adresseid');
            // Check Parameter School ID AND Address ID
            if (
                (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid))
                || (is_null($adresseid) || empty($adresseid) || !is_numeric($adresseid))
            ) {
                return $response->json(
                    statusCode:400, success:false, 
                    message: 'Adresse Id or School ID cannot be blank and must be numeric'
                );
            }

            // Check 
            if (!isset($body->voie) || !isset($body->quartier)
                || !isset($body->commune) || !isset($body->district)
                || !isset($body->ville)
                ) 
            {
                // Voie
                (  
                    !isset($body->voie) ? 
                    $response->setMessage(
                        'Voie field is mandatory and must be provider'
                    ) : false
                );
                // Quartier
                (
                    !isset($body->quartier)? 
                    $response->setMessage(
                        'Quartier field is mandatory and must be provider'
                    ) : false
                );
                // Commune
                (
                    !isset($body->commune)? 
                    $response->setMessage(
                        'Commune field is mandatory and must be provider'
                    ): false
                );
                // District
                (
                    !isset($body->district)? 
                    $response->setMessage(
                        'District field is mandatory and must be provider'
                    ): false
                );
                // Ville
                (
                    !isset($body->ville)? 
                    $response->setMessage(
                        'Ville field is mandatory and must be provider'
                    ): false
                );
                return $response->json(statusCode:400, success:false);
            }

            // Prepare Data
            try 
            {
                $voie = (
                    isset($body->voie) 
                    && (!is_null($body->voie) && !empty($body->voie))
                ) ? $body->voie: NULL;
                $quartier = (
                    isset($body->quartier) 
                    && (!is_null($body->quartier) && !empty($body->quartier))
                ) ? $body->quartier: NULL;
                $commune = (
                    isset($body->commune) 
                    && (!is_null($body->commune) && !empty($body->commune))
                ) ? $body->commune: NULL;
                $district = (
                    isset($body->district) 
                    && (!is_null($body->district) && !empty($body->district))
                ) ? $body->district: NULL;

                $ville = (
                    isset($body->ville) 
                    && (!is_null($body->ville) && !empty($body->ville))
                ) ? $body->ville: NULL;

                $reference = (
                    isset($body->reference) 
                    && (!is_null($body->reference) && !empty($body->reference))
                ) ? $body->reference: NULL;

                $adresse = new Adresse(
                    id: NULL, voie: $voie, quartier: $quartier, 
                    commune: $commune, district: $district, 
                    ville: $ville, reference: $reference, ecoleid: $ecoleid
                );
                //$mapper = new AdresseMapper($connexionWrite);
                $mapper = new VendorMapper($connexionWrite);

                $retrievedRow = $mapper
                    ->adresseRetrieve(id: $adresseid, ecoleid: $ecoleid)
                    ->executeQuery()
                    ->getResults();
                
                if ($mapper->rowCount() === 0) {
                    // Add New Adresse
                    $mapper->beginTransaction();
                    $mapper
                        ->adresseAdd(adresse: $adresse)
                         ->executInsert();

                    if ($mapper->rowCount() === 0) {
                        if ($mapper->inTransaction()) {
                            $mapper->rollBack();
                        }
                        return $response->json(
                            statusCode:500, success:false, 
                            message:'Failed to add new address.'
                        );
                    }
                    $adresseLastID = (int) $mapper->lastInsertId();
                    $retrievedNewRow = $mapper
                        ->adresseRetrieve(id: $adresseLastID, ecoleid: $ecoleid)
                        ->executeQuery()
                        ->getResults();

                    if ($mapper->rowCount() === 0) {
                        if ($mapper->inTransaction()) {
                            $mapper->rollBack();
                        }
                        return $response->json(
                            statusCode:500, success:false, 
                            message:'Failed to retrieve school after creation'
                        );
                    }
                    $returnData = [];
                    $returnData['rows_inserted'] = $mapper->rowCount();
                    $returnData['adresse'] = $retrievedNewRow;
                    $mapper->commit();

                    return $response->json(
                        statusCode:201, success:true, 
                        data: $returnData
                    );
                }

                $row = current($retrievedRow);
                $adresseRetrieved = new Adresse(
                    id: $row['id'], voie: $row['voie'],
                    quartier: $row['quartier'], commune: $row['commune'],
                    district: $row['district'], ville: $row['ville'],
                    reference: $row['reference'], ecoleid: $row['ecoleid']
                );

                // Prepare Data to Update
                // Voie
                (
                    !is_null($adresse->getVoie())
                    ? $adresseRetrieved->setVoie($adresse->getVoie()): false
                );
                // 	quartier
                (
                    !is_null($adresse->getQuartier())
                    ? $adresseRetrieved->setQuartier($adresse->getQuartier()): false
                );
                // reference
                (
                    !is_null($adresse->getReference())
                    ? $adresseRetrieved->setReference($adresse->getReference()): false
                );
                // commune
                (
                    !is_null($adresse->getCommune())
                    ? $adresseRetrieved->setCommune($adresse->getCommune()): false
                );
                // District
                (
                    !is_null($adresse->getDistrict())
                    ? $adresseRetrieved->setDistrict($adresse->getDistrict()): false
                );
                // ville
                (
                    !is_null($adresse->getVille())
                    ? $adresseRetrieved->setVille($adresse->getVille()): false
                );
                // Update Adresse
                $mapper->adresseUpdate($adresseRetrieved)
                    ->executeUpdate();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:404, success:false, 
                        message:'Address not updated.'
                    );
                }
                // Fetch after Update
                $stateAdresse = $mapper
                    ->adresseRetrieve(id: $adresseid)
                    ->executeQuery()
                    ->getResults();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:404, success:false, 
                        message:'No Address found after update.'
                    );
                }

                $returnData = [];
                $returnData['rows_returned'] = $mapper->rowCount();
                $returnData['adressse'] = $stateAdresse;
    
                return $response->json(
                    statusCode:200, success: true,
                    toCache: true, data: $returnData
                );
            } catch (AdresseException $e) {
                return $response->json(
                    statusCode:400, success:false, 
                    message:$e->getMessage()
                );
            }
        }
        /**
         * Method patchOneAdresseAction [PATCH]
         *
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(
            path:'/ecoles/([0-9]+)/adresses/([0-9]+)',
            name:'adresses.patchOne', method:"PATCH"
        )]
        public function patchOneAdresseAction(
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
            $adresseid = $request->getParam('adresseid');
            // Check Parameter School ID AND Address ID
            if (
                (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid))
                || (is_null($adresseid) || empty($adresseid) || !is_numeric($adresseid))
            ) {
                return $response->json(
                    statusCode:400, success:false, 
                    message: 'Adresse Id or School ID cannot be blank and must be numeric'
                );
            }

             // Check 
            if (!isset($body->voie) && !isset($body->quartier)
                && !isset($body->commune) && !isset($body->district)
                && !isset($body->ville)
                ) 
            {
                return $response->json(
                    statusCode:400, success:false,
                    message: "No fields to update are provided."
                );
            }
     
            // Prepare Data
            try {
                $voie = (
                    isset($body->voie) 
                    && (!is_null($body->voie) && !empty($body->voie))
                ) ? $body->voie: NULL;
                $quartier = (
                    isset($body->quartier) 
                    && (!is_null($body->quartier) && !empty($body->quartier))
                ) ? $body->quartier: NULL;
                $commune = (
                    isset($body->commune) 
                    && (!is_null($body->commune) && !empty($body->commune))
                ) ? $body->commune: NULL;
                $district = (
                    isset($body->district) 
                    && (!is_null($body->district) && !empty($body->district))
                ) ? $body->district: NULL;

                $ville = (
                    isset($body->ville) 
                    && (!is_null($body->ville) && !empty($body->ville))
                ) ? $body->ville: NULL;

                $reference = (
                    isset($body->reference) 
                    && (!is_null($body->reference) && !empty($body->reference))
                ) ? $body->reference: NULL;

                $adresse = new Adresse(
                    id: NULL, voie: $voie, quartier: $quartier, 
                    commune: $commune, district: $district, 
                    ville: $ville, reference: $reference, ecoleid: $ecoleid
                );

                //$mapper = new AdresseMapper($connexionWrite);
                $mapper = new VendorMapper($connexionWrite);
                

                $retrievedRow = $mapper
                    ->adresseRetrieve(id: $adresseid, ecoleid: $ecoleid)
                    ->executeQuery()
                    ->getResults();


                if ($mapper->rowCount() === 0) {
                    // Add New Adresse
                    return $response->json(
                        statusCode:404, success:true,
                        message:'No Address found to update.'
                    
                    );
                }
                $row = current($retrievedRow);
                $adresseRetrieved = new Adresse(
                    id: $row['id'], voie: $row['voie'],
                    quartier: $row['quartier'], commune: $row['commune'],
                    district: $row['district'], ville: $row['ville'],
                    reference: $row['reference'], ecoleid: $row['ecoleid']
                );
                // Prepare Data to Update
                // Voie
                (
                    !is_null($adresse->getVoie())
                    ? $adresseRetrieved->setVoie($adresse->getVoie()): false
                );
                // 	quartier
                (
                    !is_null($adresse->getQuartier())
                    ? $adresseRetrieved->setQuartier($adresse->getQuartier()): false
                );
                // reference
                (
                    !is_null($adresse->getReference())
                    ? $adresseRetrieved->setReference($adresse->getReference()): false
                );
                // commune
                (
                    !is_null($adresse->getCommune())
                    ? $adresseRetrieved->setCommune($adresse->getCommune()): false
                );
                // District
                (
                    !is_null($adresse->getDistrict())
                    ? $adresseRetrieved->setDistrict($adresse->getDistrict()): false
                );
                // ville
                (
                    !is_null($adresse->getVille())
                    ? $adresseRetrieved->setVille($adresse->getVille()): false
                );
                // Update Adresse
                $mapper
                    ->adresseUpdate($adresseRetrieved)
                    ->executeUpdate();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:404, success:false, 
                        message:'Address not updated.'
                    );
                }
                // Fetch after Update
                $stateAdresseRow = $mapper
                    ->adresseRetrieve(id: $adresseid)
                    ->executeQuery()
                    ->getResults();

                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode:404, success:false, 
                        message:'No Address found after update.'
                    );
                }

                $returnData = [];
                $returnData['rows_returned'] = $mapper->rowCount();
                $returnData['adressse'] = $stateAdresseRow;

                return $response->json(
                    statusCode:200, success: true,
                    toCache: true, data: $returnData
                );
            } catch (AdresseException $e) {
                return $response->json(
                    statusCode:400, success:false, 
                    message:$e->getMessage()
                );
            }
        }
        /**
         * Method deleteOneAdresseAction [DELETE]
         *
         * @param \App\SchoolManager\Http\Request $request
         * @param \App\SchoolManager\Http\Response $response
         * 
         * @return mixed
         */
        #[Route(
            path:'/ecoles/([0-9]+)/adresses/([0-9]+)',
            name:'adresses.deleteOne', method:"DELETE"
        )]
        public function deleteOneAdresseAction(
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
            $adresseid = $request->getParam('adresseid');
            // Check Parameter School ID AND Address ID
            if (
                (is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid))
                || (is_null($adresseid) || empty($adresseid) || !is_numeric($adresseid))
            ) {
                (
                    is_null($ecoleid) || empty($ecoleid) || !is_numeric($ecoleid)
                ) ? $response->setMessage(
                    'School ID cannot be blank or string. It\'s must be numeric'
                ): false;
                (
                    is_null($adresseid) || empty($adresseid) || !is_numeric($adresseid)
                ) ? $response->setMessage(
                    'Address school ID cannot be blank or string. It\'s must be numeric'
                ): false;
                return $response->json(statusCode:400, success:false);
            }
        
            $mapper = new VendorMapper($connexionWrite);

            // Start transaction
            $mapper->beginTransaction();
            $mapper
                ->adresseRemove(id: $adresseid)
                ->executeDelete();

            if ($mapper->rowCount() === 0) {
                if ($mapper->inTransaction()) {
                    $mapper->rollBack();
                }
                // Add New Adresse
                return $response->json(
                    statusCode:404, success:true,
                    message:'No Address found to delete.'
                   
                );
            }
            $mapper->commit();
            
            return $response->json(
                statusCode:200, success: true,
                message: "Address $adresseid deleted"
            );
        }
    }
}