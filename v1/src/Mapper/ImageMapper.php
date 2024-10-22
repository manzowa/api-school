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

use App\SchoolManager\Exception\ImageException;
use App\SchoolManager\Model\Ecole;
use App\SchoolManager\Model\Adresse;
use App\SchoolManager\Model\Image;
use \PDO;

class ImageMapper extends Mapper 
{
    public function __construct(PDO $pdo){
        parent::__construct($pdo);
    }

    public function retrieveImagesByEcoleIdArray(int $ecoleid)
    {
        try {
            $command  = 'SELECT images.* FROM images ';
            $command .= 'WHERE images.ecoleid = :ecoleid ';
            $this->prepare($command)
                ->bindParam(':ecoleid',  $ecoleid, PDO::PARAM_INT)
                ->execute()->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this->getResult();
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

    public function retrieveImage(Image $image) {
        try {
            $command = 'SELECT images.* FROM images ';
            $command .= 'WHERE ecoleid = :ecoleid ';
            $command .= 'AND filename = :filename ';

            $filename =  $image->getFilename();
            $ecoleid = $image->getEcoleid();

            $imageRow = $this->prepare($command)
                ->bindParam(':filename',  $filename, \PDO::PARAM_STR)
                ->bindParam(':ecoleid',  $ecoleid, \PDO::PARAM_INT)
                ->execute()->fetch(\PDO::FETCH_ASSOC)
                ->closeCursor()->getResult();
            if (is_array($imageRow)) {
                $newImage = new Image(
                    id: $imageRow['id'], 
                    title: $imageRow['title'],
                    filename: $imageRow['filename'], 
                    mimetype: $imageRow['mimetype'],
                    ecoleid: $imageRow['ecoleid']
                );
                $this->results = $newImage;
            }
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this->getResult();

    }

    public function addImage(Image $image): self 
    {
        try {
            $command  = 'INSERT INTO images (title, filename, mimetype, ecoleid)  ';
            $command .= 'VALUES (:title, :filename, :mimetype, :ecoleid) ';
            $title = $image->getTitle();
            $filename = $image->getFilename();
            $mimetype = $image->getMimetype();
            $ecoleid= $image->getEcoleid();

            $this->prepare($command)
              ->bindParam(':title', $title, \PDO::PARAM_STR|\PDO::PARAM_NULL)
              ->bindParam(':filename', $filename, \PDO::PARAM_STR|\PDO::PARAM_NULL)
              ->bindParam(':mimetype', $mimetype, \PDO::PARAM_STR|\PDO::PARAM_NULL)
              ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT)
              ->execute()->closeCursor(); 
        } catch (\PDOException $e) {
            if ($this->inTransaction()) {
                $this->rollBack();
            }
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }
    public function retrieveImageByEcoleIdAndImageId(int $ecoleid, int $imageid)
    {
        try {
            $command  = 'SELECT images.* FROM images ';
            $command .= 'WHERE images.ecoleid = :ecoleid ';
            $command .= 'AND images.id = :imageid  ';
            $this->prepare($command)
                ->bindParam(':ecoleid',  $ecoleid, \PDO::PARAM_INT)
                ->bindParam(':imageid',  $imageid, \PDO::PARAM_STR)
                ->execute()->fetch(\PDO::FETCH_ASSOC)
                ->closeCursor();
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this->getResult();
    }

    public function removeImageByIdAndEcoleId(int $id, int $ecoleid): self
    {
        try {
            $this->prepare('DELETE FROM images WHERE id = :id AND ecoleid = :ecoleid')
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
    public function retrieveImageById(int $id) {
        try {
            $command = 'SELECT images.* FROM images WHERE images.id = :id ';
            $imageRow = $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->execute()->fetch(\PDO::FETCH_ASSOC)
                ->closeCursor();
        } catch (\PDOException $e) {
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this->getResult();
    }

    public function updateImage(Image $image): self 
    {
        try {
            $command  = 'UPDATE images SET title= :title, filename= :filename ';
            $command .= 'WHERE id= :id ';

            $title = $image->getTitle();
            $filename = $image->getFilename();
            $id = $image->getId();
            
            $this->prepare($command)
            ->bindParam(':title', $title, \PDO::PARAM_STR)
            ->bindParam(':filename', $filename, \PDO::PARAM_STR)
            ->bindParam(':id', $id, \PDO::PARAM_INT)
            ->execute();  
        } catch (\PDOException $e) {
            if ($this->inTransaction()) {
                $this->rollBack();
            }
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }

    public function updateEcole(Ecole $ecole): self 
    {
        try {
            $command  = 'UPDATE ecoles SET nom= :nom, email= :email, telephone= :telephone, ';
            $command .= 'type= :type, site= :site, maximage = :maximage ';
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
            if ($this->inTransaction()) {
                $this->rollBack();
            }
            $this->results = false;
            \App\SchoolManager\loggerException($e);
        }
        return $this;
    }
}