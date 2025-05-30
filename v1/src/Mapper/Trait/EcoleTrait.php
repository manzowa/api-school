<?php 

/**
 *  AdresseTrait
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
namespace ApiSchool\V1\Mapper\Trait
{
    trait ecoleTrait
    {
        public function ecoleRetrieve(
            ?int $id= null, \ApiSchool\V1\Model\Ecole $ecole= null
        ): self 
        {
           if(!is_null($id) ) {
                $command = 'SELECT ecoles.* FROM ecoles WHERE id = :id';
                $this->prepare($command)
                 ->bindParam(':id', $id, \PDO::PARAM_INT);
            } elseif(
                is_null($id) 
                && (!is_null($ecole) && $ecole instanceof \ApiSchool\V1\Model\Ecole)
            ) {
                $nom = $ecole->getNom();
                $command = 'SELECT ecoles.* FROM ecoles WHERE nom = :nom ';
                $ecole = $this
                    ->prepare($command)
                    ->bindParam(':nom', $nom, \PDO::PARAM_STR);
            }else {
                $command = 'SELECT ecoles.* FROM ecoles ';
                $this->prepare($command);
            }
            return $this;
        }
    
        public function ecoleAdd(\ApiSchool\V1\Model\Ecole $ecole): self 
        {
            $nom = $ecole->getNom();
            $email = $ecole->getEmail();
            $telephone = $ecole->getTelephone();
            $type= $ecole->getType();
            $site = $ecole->getSite();
            $maximage = $ecole->getMaximage();
    
            $command  = 'INSERT INTO ecoles (nom, email, telephone,type, site, maximage)  ';
            $command .= 'VALUES (:nom, :email, :telephone, :type, :site, :maximage) ';
    
            $this
                ->prepare($command)
                ->bindParam(':nom', $nom, \PDO::PARAM_STR)
                ->bindParam(':email', $email, \PDO::PARAM_STR|\PDO::PARAM_NULL)
                ->bindParam(':telephone', $telephone, \PDO::PARAM_STR|\PDO::PARAM_NULL)
                ->bindParam(':type', $type, \PDO::PARAM_STR|\PDO::PARAM_NULL)
                ->bindParam(':site', $site, \PDO::PARAM_STR|\PDO::PARAM_NULL)
                ->bindParam(':maximage', $maximage, \PDO::PARAM_INT|\PDO::PARAM_NULL); 
    
            return $this;
        }
    
        public function ecoleUpdate(\ApiSchool\V1\Model\Ecole $ecole): self 
        {
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
                ->bindParam(':id', $id, \PDO::PARAM_INT);
            return $this;
        }
    
        public function ecoleRemove(?int $id= null) 
        {
            $this
                ->prepare( 'DELETE FROM ecoles WHERE id = :id ')
                ->bindParam(':id', $id, \PDO::PARAM_INT);
            return $this;
        }

        public function ecoleCounter()
        {
            $command = 'SELECT count(id) as totalCount FROM ecoles';
            $data = $this->prepare($command)
                ->executeQuery()
                ->getResults();
            $result = current($data);
            return intval($result['totalCount']);
        }
    }
}