<?php 

/**
 *  EcoleMapper
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
namespace App\SchoolManager\Mapper;

use App\SchoolManager\Exception\EcoleException;
use App\SchoolManager\Model\Adresse;
use App\SchoolManager\Model\Ecole;
use \Countable;
use PDO;

class EcoleMapper extends Mapper implements Countable
{
    public function __construct(\PDO $pdo){
        parent::__construct($pdo);
    }

    public function retrieveEcolesArray() 
    {
        try {
            $ecolesArray = [];
            $ecoles = $this->prepare('SELECT * FROM ecoles')
                ->execute()->fetchAll(\PDO::FETCH_ASSOC)
                ->closeCursor()->getResult();

            if (is_array($ecoles) && count($ecoles) > 0) {
                foreach ($ecoles as $ecole) 
                {
                    $adresses = $this->retrieveEcoleAdressesArray($ecole['id']);
                    $ecoleObject = new Ecole(
                        id: $ecole['id'], nom: $ecole['nom'], 
                        email: $ecole['email'], telephone: $ecole['telephone'],
                        type: $ecole['type'], site: $ecole['site'], 
                        maximage: $ecole['maximage'], adresses: $adresses 
                    );
                    $ecolesArray[] = $ecoleObject->toArray();
                }
                $this->results = $ecolesArray;
            }
           
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this->getResult();
    }
    public function addEcole(Ecole $ecole): self 
    {
        try {
            $command  = 'INSERT INTO ecoles (nom, email, telephone,type, site, maximage)  ';
            $command .= 'VALUES (:nom, :email, :telephone, :type, :site, :maximage) ';
            $nom = $ecole->getNom();
            $email = $ecole->getEmail();
            $telephone = $ecole->getTelephone();
            $type= $ecole->getType();
            $site = $ecole->getSite();
            $maximage = $ecole->getMaximage();

            $this->prepare($command)
              ->bindParam(':nom', $nom, \PDO::PARAM_STR)
              ->bindParam(':email', $email, \PDO::PARAM_STR|\PDO::PARAM_NULL)
              ->bindParam(':telephone', $telephone, \PDO::PARAM_STR|\PDO::PARAM_NULL)
              ->bindParam(':type', $type, \PDO::PARAM_STR|\PDO::PARAM_NULL)
              ->bindParam(':site', $site, \PDO::PARAM_STR|\PDO::PARAM_NULL)
              ->bindParam(':maximage', $maximage, \PDO::PARAM_INT|\PDO::PARAM_NULL)
              ->execute()->closeCursor(); 
            $ecoleid = $this->lastInsertId();
            if ($this->rowCount() !== 0 && is_numeric($ecoleid)) {
                $this->setStockId($ecoleid);
                $this->insertAdresses(adresses: $ecole->getAdresses(), ecoleid: $ecoleid);
            }
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }
    public function removeEcoleById(int $id): self
    {
        try {
            $this->prepare('DELETE FROM ecoles WHERE id = :id')
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->execute();
            $this->results = true;  
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }
    public function removeAdresseById(int $id): self
    {
        try {
            $this->prepare('DELETE FROM adresses WHERE id = :id ')
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->execute();
            $this->results = true;  
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }
    public function removeAdresseByIdAndEcoleId(int $id, int $ecoleid): self
    {
        try {
            $this->prepare('DELETE FROM adresses WHERE id = :id AND ecoleid = :ecoleid')
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT)
                ->execute();
            $this->results = true;  
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }
    public function fetchOne(int $id) {
        try {
            $command = 'SELECT id, nom, adresse, quartier, reference, commune, district, ville, type 
            FROM ecoles WHERE id = :id ';
            $this->prepare($command) ->bindParam(':id', $id, \PDO::PARAM_INT)->execute()
                ->fetchAll(\PDO::FETCH_FUNC, "\App\SchoolManager\Model\Ecole::build")
                ->closeCursor();
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return is_array($this->getResult())? current($this->getResult()):$this->getResult();
    }
    public function retrieveEcole(int $ecoleid) {
        try {
            $command = 'SELECT ecoles.* FROM ecoles WHERE ecoles.id = :id ';
            $ecole = $this->prepare($command)
                ->bindParam(':id', $ecoleid, \PDO::PARAM_INT)
                ->execute()->fetch(\PDO::FETCH_ASSOC)
                ->closeCursor()->getResult();
           
            if (is_array($ecole) && count($ecole) > 0) {
                $adresses = $this->retrieveEcoleAdressesArray($ecole['id']);
                $ecoleObject = new Ecole(
                    id: $ecole['id'], nom: $ecole['nom'], 
                    email: $ecole['email'], telephone: $ecole['telephone'],
                    type: $ecole['type'], site: $ecole['site'], 
                    maximage: $ecole['maximage'], adresses: $adresses 
                );
                $this->results = $ecoleObject;
            }
           
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this->getResult();
    }
    public function retrieveEcoleByName(string $nom) {
        try {
            $command = 'SELECT ecoles.* FROM ecoles WHERE nom = :nom ';
            $ecole = $this->prepare($command)
                ->bindParam(':nom', $nom,  \PDO::PARAM_STR)
                ->execute()->fetch(\PDO::FETCH_ASSOC)
                ->closeCursor()->getResult();
           
            if (is_array($ecole) && count($ecole) > 0) {
                $adresses = $this->retrieveEcoleAdressesArray($ecole['id']);
                $ecoleObject = new Ecole(
                    id: $ecole['id'], nom: $ecole['nom'], 
                    email: $ecole['email'], telephone: $ecole['telephone'],
                    type: $ecole['type'], site: $ecole['site'], 
                    maximage: $ecole['maximage'], adresses: $adresses 
                );
                $this->results = $ecoleObject;
            }
           
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this->getResult();
    }
    public function count(): int 
    {
        try {
            $queryString = 'SELECT count(id) FROM ecoles';
            $this->query(queryString: $queryString)
                ->execute()->fetch()->closeCursor();
        } catch (\PDOException $e) {
            \App\SchoolManager\loggerException($e);
        }
        return is_array($this->getResult())? current($this->getResult()): $this->getResult();
    }
    public function retrieveEcolesByLimitAndOffset(int $limit, int $offset): mixed
    {
        try {
            $queryString = 'SELECT ecoles.* FROM ecoles LIMIT :limit  OFFSET :offset ';
            $ecoles = $this->prepare($queryString)
                ->bindParam(':limit', $limit, \PDO::PARAM_INT)
                ->bindParam(':offset', $offset, \PDO::PARAM_INT)
                ->execute()->fetchAll(\PDO::FETCH_ASSOC)
                ->closeCursor()->getResult(); 

            if (is_array($ecoles) && count($ecoles) > 0) {
                foreach ($ecoles as $ecole) {
                    $adresses = $this->retrieveEcoleAdressesArray($ecole['id']);
                    $ecoleObject = new Ecole(
                        id: $ecole['id'], nom: $ecole['nom'], 
                        email: $ecole['email'], telephone: $ecole['telephone'],
                        type: $ecole['type'], site: $ecole['site'], 
                        maximage: $ecole['maximage'], adresses: $adresses 
                    );
                    $ecolesArray[] = $ecoleObject->toArray();
                }
                $this->results = $ecolesArray;
            }
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this->getResult();
    }
    public function updateEcoleAndAdresses(Ecole $ecole): self 
    {
        try {
            $command  = 'UPDATE ecoles SET nom= :nom, email= :email, telephone= :telephone, ';
            $command .= 'type= :type, site= :site, maximage= :maximage ';
            $command .= 'WHERE id= :id ';
            $nom = $ecole->getNom();
            $email = $ecole->getEmail();
            $telephone = $ecole->getTelephone();
            $type= $ecole->getType();
            $site = $ecole->getSite();
            $maximage = $ecole->getMaximage();
            $id = $ecole->getId();

            $this->prepare($command)
              ->bindParam(':nom', $nom, \PDO::PARAM_STR)
              ->bindParam(':email', $email, \PDO::PARAM_STR|\PDO::PARAM_NULL)
              ->bindParam(':telephone', $telephone, \PDO::PARAM_STR|\PDO::PARAM_NULL)
              ->bindParam(':type', $type, \PDO::PARAM_STR|\PDO::PARAM_NULL)
              ->bindParam(':site', $site, \PDO::PARAM_STR|\PDO::PARAM_NULL)
              ->bindParam(':maximage', $maximage, \PDO::PARAM_INT|\PDO::PARAM_NULL)
              ->bindParam(':id', $id, \PDO::PARAM_INT)
              ->execute(); 

              if ($this->rowCount() !== 0) {
                if (count($ecole->getAdresses()) > 0) {
                    foreach ($ecole->getAdresses() as $adresse) {
                        $adresseObject = new Adresse(
                            id: $adresse['id'], voie: $adresse['voie'],
                            quartier: $adresse['quartier'], commune: $adresse['commune'],
                            district: $adresse['district'], ville: $adresse['ville'],
                            reference: $adresse['reference'], ecoleid: $id
                        );
                        $this->updateAdresse(adresse: $adresseObject);
                        
                        if ($this->rowCount() !== 0) {
                            $this->results = true;
                        }
                    }
                }
            }
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }
    public function updateEcole(Ecole $ecole): self 
    {
        try {
            $command  = 'UPDATE ecoles SET nom= :nom, email= :email, telephone= :telephone, ';
            $command .= 'type= :type, site= :site ';
            $command .= 'WHERE id= :id ';
            $nom = $ecole->getNom();
            $email = $ecole->getEmail();
            $telephone = $ecole->getTelephone();
            $type= $ecole->getType();
            $site = $ecole->getSite();
            $maximage = $ecole->getMaximage();
            $id = $ecole->getId();
            
            $this->prepare($command)
            ->bindParam(':nom', $nom, \PDO::PARAM_STR)
            ->bindParam(':email', $email, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':telephone', $telephone, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':type', $type, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':site', $site, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':maximage', $maximage, \PDO::PARAM_INT|\PDO::PARAM_NULL)
            ->bindParam(':id', $id, \PDO::PARAM_INT)
            ->execute();  
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }
    public function retrieveEcoleAdressesArray(int $ecoleid) {
        $command  = 'SELECT adresses.* FROM adresses, ecoles ';
        $command .= 'WHERE adresses.ecoleid = :ecoleid ';
        $command .= 'AND adresses.ecoleid = ecoles.id';
        $smt =  $this->db->prepare($command);
        $smt->bindParam(':ecoleid', $ecoleid, PDO::PARAM_INT);
        $smt->execute();

        $arrAdresses = [];
        while ($adresseRow = $smt->fetch(\PDO::FETCH_ASSOC)) {
            $adresse = new Adresse(
                id: $adresseRow['id'], voie: $adresseRow['voie'],
                quartier: $adresseRow['quartier'], commune: $adresseRow['commune'],
                district: $adresseRow['district'], ville: $adresseRow['ville'],
                reference: $adresseRow['reference'], ecoleid: $adresseRow['ecoleid']
            );

            $arrAdresses[] = $adresse->toArray();
        }
        return $arrAdresses;
    }
    public function retrieveEcoleAdresse(
        int $ecoleid, ?string $voie, ?string $quartier,
        ?string $reference, ?string $commune
    ) {
        $command  = 'SELECT adresses.* FROM adresses, ecoles ';
        $command .= 'WHERE adresses.ecoleid = :ecoleid ';
        $command .= 'AND adresses.ecoleid = ecoles.id';
        $smt =  $this->db->prepare($command);
        $smt->bindParam(':ecoleid', $ecoleid, PDO::PARAM_INT);
        $smt->execute();
        $adresseRow = $smt->fetch(\PDO::FETCH_ASSOC);

        $adresse = false;
        if (is_array($adresseRow)) {
            $adresse = new Adresse(
                id: $adresseRow['id'], voie: $adresseRow['voie'],
                quartier: $adresseRow['quartier'], commune: $adresseRow['commune'],
                district: $adresseRow['district'], ville: $adresseRow['ville'],
                reference: $adresseRow['reference']
            );
        }
        return $adresse;
    }
    public function retrieveAdresse(Adresse $adresse) {
        try {
            $command = 'SELECT adresses.* FROM adresses ';
            $command .= 'WHERE voie = :voie ';
            $command .= 'AND quartier = :quartier ';
            $command .= 'AND commune = :commune ';
            $command .= 'AND ecoleid = :ecoleid ';

            $voie = $adresse->getVoie();
            $quartier = $adresse->getQuartier();
            $commune = $adresse->getCommune();
            $ecoleid = $adresse->getEcoleid();

            $adresseRow = $this->prepare($command)
                ->bindParam(':voie',  $voie, \PDO::PARAM_STR)
                ->bindParam(':quartier',  $quartier, \PDO::PARAM_STR)
                ->bindParam(':commune',  $commune, \PDO::PARAM_STR)
                ->bindParam(':ecoleid',  $ecoleid, \PDO::PARAM_INT)
                ->execute()->fetch(\PDO::FETCH_ASSOC)
                ->closeCursor()->getResult();
            if (is_array($adresseRow)) {
                $adresse = new Adresse(
                    id: $adresseRow['id'], voie: $adresseRow['voie'],
                    quartier: $adresseRow['quartier'], commune: $adresseRow['commune'],
                    district: $adresseRow['district'], ville: $adresseRow['ville'],
                    reference: $adresseRow['reference'], ecoleid: $adresseRow['ecoleid']
                );
                $this->results = $adresse;
            }

        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this->getResult();

    }
    public function retrieveAdressesByEcoleIdArray(int $ecoleid)
    {
        try {
            $command  = 'SELECT adresses.* FROM adresses ';
            $command .= 'WHERE adresses.ecoleid = :ecoleid ';
            $this->prepare($command)
                ->bindParam(':ecoleid',  $ecoleid, PDO::PARAM_INT)
                ->execute()->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this->getResult();
    }
    public function retrieveAdresseById(int $id)
    {
        try {
            $command  = 'SELECT adresses.* FROM adresses ';
            $command .= 'WHERE adresses.id = :id ';
            $adresseRow= $this->prepare($command)
                ->bindParam(':id',  $id, PDO::PARAM_INT)
                ->execute()->fetch(\PDO::FETCH_ASSOC)
                ->closeCursor()->getResult();
                if (is_array($adresseRow)) {
                    $adresse = new Adresse(
                        id: $adresseRow['id'], voie: $adresseRow['voie'],
                        quartier: $adresseRow['quartier'], commune: $adresseRow['commune'],
                        district: $adresseRow['district'], ville: $adresseRow['ville'],
                        reference: $adresseRow['reference'], ecoleid: $adresseRow['ecoleid']
                    );
                    $this->results = $adresse;
                }
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this->getResult();
    }
    public function retrieveAdresseByEcoleIdAndAdresseId(int $ecoleid, int $adresseid)
    {
        try {
            $command  = 'SELECT adresses.* FROM adresses ';
            $command .= 'WHERE adresses.ecoleid = :ecoleid ';
            $command .= 'AND adresses.id = :adresseid  ';
            $adresseRow= $this->prepare($command)
                ->bindParam(':ecoleid', $ecoleid, PDO::PARAM_INT)
                ->bindParam(':adresseid', $adresseid, PDO::PARAM_INT)
                ->execute()->fetch(\PDO::FETCH_ASSOC)
                ->closeCursor()->getResult();
                if (is_array($adresseRow)) {
                    $adresse = new Adresse(
                        id: $adresseRow['id'], voie: $adresseRow['voie'],
                        quartier: $adresseRow['quartier'], commune: $adresseRow['commune'],
                        district: $adresseRow['district'], ville: $adresseRow['ville'],
                        reference: $adresseRow['reference'], ecoleid: $adresseRow['ecoleid']
                    );
                    $this->results = $adresse;
                }
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this->getResult();
    }
    public function updateAdresse(Adresse $adresse) 
    {
        try {
            $command  = 'UPDATE adresses SET voie= :voie, quartier = :quartier, ';
            $command .= 'reference = :reference, commune = :commune, district = :district, ';
            $command .= 'ville = :ville WHERE id = :id AND ecoleid = :ecoleid';
          
            $voie = $adresse->getVoie();
            $quartier = $adresse->getQuartier();
            $reference = $adresse->getReference();
            $commune = $adresse->getCommune();
            $district = $adresse->getDistrict();
            $ville = $adresse->getVille();
            $id = $adresse->getId();
            $ecoleid = $adresse->getEcoleid();

            $smt =  $this->db->prepare($command);
            $smt->bindParam(':voie', $voie, PDO::PARAM_STR|PDO::PARAM_NULL);
            $smt->bindParam(':quartier', $quartier, PDO::PARAM_STR|PDO::PARAM_NULL);
            $smt->bindParam(':reference', $reference, PDO::PARAM_STR|PDO::PARAM_NULL);
            $smt->bindParam(':commune', $commune, PDO::PARAM_STR);
            $smt->bindParam(':district', $district, PDO::PARAM_STR);
            $smt->bindParam(':ville', $ville, PDO::PARAM_STR);
            $smt->bindParam(':id', $id, PDO::PARAM_INT);
            $smt->bindParam(':ecoleid', $ecoleid, PDO::PARAM_INT);

            $smt->execute();
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }
    public function addAdresse(Adresse $adresse) 
    {
        try {
            $command  = 'INSERT INTO adresses ';
            $command .= '(voie, quartier, reference, commune, district, ville, ecoleid)';
            $command .= 'VALUES (:voie, :quartier, :reference, :commune, :district, :ville, :ecoleid)';

            $voie = $adresse->getVoie();
            $quartier = $adresse->getQuartier();
            $reference = $adresse->getReference();
            $commune = $adresse->getCommune();
            $district = $adresse->getDistrict();
            $ville = $adresse->getVille();
            $ecoleid = $adresse->getEcoleid();

            $smt =  $this->db->prepare($command);
            $smt->bindParam(':voie', $voie, PDO::PARAM_STR|PDO::PARAM_NULL);
            $smt->bindParam(':quartier', $quartier, PDO::PARAM_STR|PDO::PARAM_NULL);
            $smt->bindParam(':reference', $reference, PDO::PARAM_STR|PDO::PARAM_NULL);
            $smt->bindParam(':commune', $commune, PDO::PARAM_STR);
            $smt->bindParam(':district', $district, PDO::PARAM_STR);
            $smt->bindParam(':ville', $ville, PDO::PARAM_STR);
            $smt->bindParam(':ecoleid', $ecoleid, PDO::PARAM_INT);
            $smt->execute();
            $this->results = true;
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }
    public function insertAdresses(array $adresses = [], ?int $ecoleid = null) {
        try {
            if (count($adresses) >0 && is_numeric($ecoleid)) {
                for ($index=0; $index < count($adresses); $index++) { 
                    $rowAdresse = $adresses[$index];
                    $adresse = new Adresse(
                        id: $rowAdresse['id'], voie: $rowAdresse['voie'], 
                        quartier: $rowAdresse['quartier'], commune: $rowAdresse['commune'], 
                        district: $rowAdresse['district'], ville: $rowAdresse['ville'], 
                        reference: $rowAdresse['reference'], ecoleid: $ecoleid
                    );
                    $this->addAdresse(adresse: $adresse);
                }
            }
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
    }
}