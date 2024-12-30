<?php 

/**
 *  VendorMapper
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
namespace ApiSchool\V1\Mapper;

use ApiSchool\V1\Model\Adresse;


class VendorMapper extends Mapper 
{
    use \ApiSchool\V1\Mapper\Trait\EcoleTrait;
    use \ApiSchool\V1\Mapper\Trait\AdresseTrait;
    use \ApiSchool\V1\Mapper\Trait\ImageTrait;
    use \ApiSchool\V1\Mapper\Trait\SessionTrait;
    use \ApiSchool\V1\Mapper\Trait\UserTrait;

    public function findEcoles(?int $id= null, \ApiSchool\V1\Model\Ecole $ecole= null): self
    {
        $this->ecoleRetrieve(id: $id, ecole: $ecole)
            ->executeQuery();
        return $this;
    }

    public function addMultiAddresse(array $adresses = [], ?int $ecoleid = null): self
    {
        if (count($adresses) >0 && is_numeric($ecoleid)) {
            for ($index=0; $index < count($adresses); $index++) 
            { 
                $row = $adresses[$index];
                $adresse = new Adresse(
                    id: $row['id'], voie: $row['voie'], 
                    quartier: $row['quartier'], commune: $row['commune'], 
                    district: $row['district'], ville: $row['ville'], 
                    reference: $row['reference'], ecoleid: $ecoleid
                );
                $this->adresseAdd(adresse: $adresse)
                    ->executInsert();
            }
        }

        return $this;

    }

    public function findEcolesByLimitAndOffset(int $limit, int $offset): self
    {
        $command = 'SELECT ecoles.* FROM ecoles LIMIT :limit  OFFSET :offset ';
        $this->prepare($command)
            ->bindParam(':limit', $limit, \PDO::PARAM_INT)
            ->bindParam(':offset', $offset, \PDO::PARAM_INT);

        return $this;
    }
}