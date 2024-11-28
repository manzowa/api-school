<?php 

/**
 *  ImageMapper
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

use App\SchoolManager\Model\Ecole;
use App\SchoolManager\Model\Adresse;
use App\SchoolManager\Model\Image;
use \PDO;

class ImageMapper extends Mapper 
{

    public function __construct(PDO $pdo){
        parent::__construct($pdo);
    }


    public function retrieveEcole(int $ecoleid) {
        try {
            $command = 'SELECT ecoles.* FROM ecoles WHERE ecoles.id = :id ';
            $ecole = $this->prepare($command)
                ->bindParam(':id', $ecoleid, \PDO::PARAM_INT)
                ->execute()->fetch(\PDO::FETCH_ASSOC)
                ->closeCursor()->getResults();
           
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
        return $this->getResults();
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
    public function updateEcole(Ecole $ecole): self 
    {
        try {
            $nom = $ecole->getNom();
            $email = $ecole->getEmail();
            $telephone = $ecole->getTelephone();
            $type= $ecole->getType();
            $site = $ecole->getSite();
            $maximage = $ecole->getMaximage();
            $id = $ecole->getId();

            $command  = 'UPDATE ecoles SET nom= :nom, email= :email, telephone= :telephone, ';
            $command .= 'type= :type, site= :site, maximage = :maximage ';
            $command .= 'WHERE id= :id ';

        
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
            if ($this->inTransaction()) {
                $this->rollBack();
            }
            $this->setResults(false);
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }
 
    public function retrieve(?int $id= null, ?int $ecoleid = null, ?Image $image = null): self 
    {
        if (!is_null($id) && !is_null($ecoleid)) {
            $command  = 'SELECT images.* FROM images ';
            $command .= 'WHERE images.ecoleid = :ecoleid ';
            $command .= 'AND images.id = :id ';
            $this->prepare($command)
            ->bindParam(':ecoleid', $ecoleid, PDO::PARAM_INT)
            ->bindParam(':id', $id, PDO::PARAM_INT);

        } elseif(!is_null($id) && is_null($ecoleid)) {
            $command  = 'SELECT images.* FROM images ';
            $command .= 'WHERE id = :id ';
            $this->prepare($command)
            ->bindParam(':id', $id, PDO::PARAM_INT);

        }  elseif(is_null($id) && !is_null($ecoleid)) {
            $command  = 'SELECT images.* FROM images ';
            $command .= 'WHERE images.ecoleid = :ecoleid ';
            $this->prepare($command)
                ->bindParam(':ecoleid',  $ecoleid, PDO::PARAM_INT);
        } elseif( is_null($id) && is_null($ecoleid) 
            (!is_null($image) && $image instanceof Image)
        ) {
            $filename =  $image->getFilename();
            $ecoleid = $image->getEcoleid();

            $command = 'SELECT images.* FROM images ';
            $command .= 'WHERE ecoleid = :ecoleid ';
            $command .= 'AND filename = :filename ';
            
            $this->prepare($command)
                ->bindParam(':filename',  $filename, \PDO::PARAM_STR)
                ->bindParam(':ecoleid',  $ecoleid, \PDO::PARAM_INT);
        } else {
            $command  = 'SELECT images.* FROM images ';
            $this->prepare($command);
        }
        return $this;
    }
    public function add(Image $image):self 
    {
        $title = $image->getTitle();
        $filename = $image->getFilename();
        $mimetype = $image->getMimetype();
        $ecoleid= $image->getEcoleid();

        $command  = 'INSERT INTO images (title, filename, mimetype, ecoleid)  ';
        $command .= 'VALUES (:title, :filename, :mimetype, :ecoleid) ';

        $this
            ->prepare($command)
            ->bindParam(':title', $title, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':filename', $filename, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':mimetype', $mimetype, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT);

        return $this;
    }
    public function update(Image $image): self 
    {
        $title = $image->getTitle();
        $filename = $image->getFilename();
        $id = $image->getId();

        $command  = 'UPDATE images SET title= :title, filename= :filename ';
        $command .= 'WHERE id= :id ';

        $this
            ->prepare($command)
            ->bindParam(':title', $title, \PDO::PARAM_STR)
            ->bindParam(':filename', $filename, \PDO::PARAM_STR)
            ->bindParam(':id', $id, \PDO::PARAM_INT);  
         
        return $this;
    }
    public function remove(int $id, ?int $ecoleid = null): self
    {
        if (!is_null($id) && !is_null($ecoleid)) {
            $command = 'DELETE FROM images WHERE id = :id ';
            $command .= ' AND ecoleid = :ecoleid';

            $this->prepare($command)
                ->bindParam(':id', $id, PDO::PARAM_INT)
                ->bindParam(':ecoleid',  $ecoleid, \PDO::PARAM_INT);
        } else {
            $command = 'DELETE FROM images WHERE id = :id ';

            $this->prepare($command)
                ->bindParam(':id', $id, PDO::PARAM_INT);
        }

        return $this;
    }
}