<?php 

/**
 *  ImageTrait
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
namespace App\SchoolManager\Mapper\Trait
{
    trait ImageTrait 
    {
        public function imageRetrieve(
            ?int $id= null, ?int $ecoleid = null, 
            ?\App\SchoolManager\Model\Image $image = null
        ): self 
        {
            if (!is_null($id) && !is_null($ecoleid)) {
                $command  = 'SELECT images.* FROM images ';
                $command .= 'WHERE images.ecoleid = :ecoleid ';
                $command .= 'AND images.id = :id ';
                $this->prepare($command)
                ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT)
                ->bindParam(':id', $id, \PDO::PARAM_INT);
    
            } elseif(!is_null($id) && is_null($ecoleid)) {
                $command  = 'SELECT images.* FROM images ';
                $command .= 'WHERE id = :id ';
                $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT);
    
            }  elseif(is_null($id) && !is_null($ecoleid)) {
                $command  = 'SELECT images.* FROM images ';
                $command .= 'WHERE images.ecoleid = :ecoleid ';
                $this->prepare($command)
                    ->bindParam(':ecoleid',  $ecoleid, \PDO::PARAM_INT);
            } elseif( 
                is_null($id) 
                && is_null($ecoleid) 
                && (!is_null($image) && $image instanceof \App\SchoolManager\Model\Image)
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
        public function imageAdd(\App\SchoolManager\Model\Image $image):self 
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
        public function imageUpdate(\App\SchoolManager\Model\Image $image): self 
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
        public function imageRemove(int $id, ?int $ecoleid = null): self
        {
            if (!is_null($id) && !is_null($ecoleid)) {
                $command = 'DELETE FROM images WHERE id = :id ';
                $command .= ' AND ecoleid = :ecoleid';
    
                $this->prepare($command)
                    ->bindParam(':id', $id, \PDO::PARAM_INT)
                    ->bindParam(':ecoleid',  $ecoleid, \PDO::PARAM_INT);
            } else {
                $command = 'DELETE FROM images WHERE id = :id ';
    
                $this->prepare($command)
                    ->bindParam(':id', $id, \PDO::PARAM_INT);
            }
    
            return $this;
        }
    }
}