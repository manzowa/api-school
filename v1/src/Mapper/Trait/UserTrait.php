<?php 

/**
 *  UserTrait
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
namespace App\SchoolManager\Mapper\Trait;


trait UserTrait
{
    public function userRetrieve(
        ?int $id= null,
        ?\ApiSchool\V1\Model\User $user = null
    ):self {
        return $this;
    }
    public function userAdd(\ApiSchool\V1\Model\User $user): self {
        return $this;
    }
    public function userUpdate(\ApiSchool\V1\Model\User $user): self{
        return $this;
    }
    public function userRemove(int $id,): self {
        return $this;
    }
}