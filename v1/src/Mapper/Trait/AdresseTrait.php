<?php 

/**
 *  AdresseTrait
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
namespace App\SchoolManager\Mapper\Trait;


trait AdresseTrait
{
    public function adresseRetrieve(
        ?int $id= null, ?int $ecoleid = null, 
        ?\App\SchoolManager\Model\Adresse $adresse = null
    ):self
    {
        if (!is_null($id) && !is_null($ecoleid)) {
            $command  = 'SELECT adresses.* FROM adresses ';
            $command .= 'WHERE adresses.ecoleid = :ecoleid ';
            $command .= 'AND adresses.id = :adresseid ';
            $this->prepare($command)
            ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT)
            ->bindParam(':adresseid', $id, \PDO::PARAM_INT);

        } elseif(!is_null($id) && is_null($ecoleid)) {
            $command = 'SELECT adresses.* FROM adresses WHERE id = :id';
            $this->prepare($command)
             ->bindParam(':id', $id, \PDO::PARAM_INT);
        } elseif(is_null($id) && !is_null($ecoleid)) {
            $command  = 'SELECT adresses.* FROM adresses, ecoles ';
            $command .= 'WHERE adresses.ecoleid = :ecoleid ';
            $command .= 'AND adresses.ecoleid = ecoles.id';
            $this->prepare($command)
            ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT);

        } elseif(
            is_null($id) 
            && is_null($ecoleid)
            && (!is_null($adresse) && $adresse instanceof \App\SchoolManager\Model\Adresse)
        ) {
            $voie = $adresse->getVoie();
            $quartier = $adresse->getQuartier();
            $commune = $adresse->getCommune();
            $ecoleid = $adresse->getEcoleid();

            $command = 'SELECT adresses.* FROM adresses ';
            $command .= 'WHERE voie = :voie ';
            $command .= 'AND quartier = :quartier ';
            $command .= 'AND commune = :commune ';
            $command .= 'AND ecoleid = :ecoleid ';

            $this->prepare($command)
            ->bindParam(':voie',  $voie, \PDO::PARAM_STR)
            ->bindParam(':quartier',  $quartier, \PDO::PARAM_STR)
            ->bindParam(':commune',  $commune, \PDO::PARAM_STR)
            ->bindParam(':ecoleid',  $ecoleid, \PDO::PARAM_INT);

        } else {
            $command = 'SELECT adresses.* FROM adresses ';
            $this->prepare($command);
        }
        return $this;
    }
    public function adresseAdd(\App\SchoolManager\Model\Adresse $adresse): self
    {
        $voie = $adresse->getVoie();
        $quartier = $adresse->getQuartier();
        $reference = $adresse->getReference();
        $commune = $adresse->getCommune();
        $district = $adresse->getDistrict();
        $ville = $adresse->getVille();
        $ecoleid = $adresse->getEcoleid();

        $command  = 'INSERT INTO adresses ';
        $command .= '(voie, quartier, reference, commune, district, ville, ecoleid)';
        $command .= 'VALUES (:voie, :quartier, :reference, :commune, ';
        $command .=' :district, :ville, :ecoleid)';
        
        $this
            ->prepare($command)
            ->bindParam(':voie', $voie, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':quartier', $quartier, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':reference', $reference, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':commune', $commune, \PDO::PARAM_STR)
            ->bindParam(':district', $district, \PDO::PARAM_STR)
            ->bindParam(':ville', $ville, \PDO::PARAM_STR)
            ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT);
        return $this;
    }
    public function adresseUpdate(\App\SchoolManager\Model\Adresse $adresse): self
    {
        $voie = $adresse->getVoie();
        $quartier = $adresse->getQuartier();
        $reference = $adresse->getReference();
        $commune = $adresse->getCommune();
        $district = $adresse->getDistrict();
        $ville = $adresse->getVille();
        $id = $adresse->getId();
        $ecoleid = $adresse->getEcoleid();

        $command  = 'UPDATE adresses SET voie= :voie, quartier = :quartier, ';
        $command .= 'reference = :reference, commune = :commune, district = :district, ';
        $command .= 'ville = :ville WHERE id = :id AND ecoleid = :ecoleid';
          
        $this->prepare($command)
            ->bindParam(':voie', $voie, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':quartier', $quartier, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':reference', $reference, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':commune', $commune, \PDO::PARAM_STR)
            ->bindParam(':district', $district, \PDO::PARAM_STR)
            ->bindParam(':ville', $ville, \PDO::PARAM_STR)
            ->bindParam(':id', $id, \PDO::PARAM_INT)
            ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT);
        return $this;
    }
    public function adresseRemove(int $id, ?int $ecoleid = null): self
    {
        if (!is_null($id) && !is_null($ecoleid)) {
            $command = 'DELETE FROM adresses WHERE id = :id ';
            $command .= ' AND ecoleid = :ecoleid';

            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->bindParam(':ecoleid',  $ecoleid, \PDO::PARAM_INT);
        } else {
            $command = 'DELETE FROM adresses WHERE id = :id ';
            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT);
        }
        return $this;
    }
}