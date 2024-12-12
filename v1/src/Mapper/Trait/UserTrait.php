<?php 

/**
 *  UserTrait
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


trait UserTrait
{
    public function userRetrieve(
        ?int $id= null,
        ?\App\SchoolManager\Model\User $user = null
    ):self {
        return $this;
    }
    public function userAdd(\App\SchoolManager\Model\User $user): self {
        return $this;
    }
    public function userUpdate(\App\SchoolManager\Model\User $user): self{
        return $this;
    }
    public function userRemove(int $id,): self {
        return $this;
    }
}