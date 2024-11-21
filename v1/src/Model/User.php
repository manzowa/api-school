<?php 

/**
 * File User
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
namespace App\SchoolManager\Model
{
    final class User
    {
        protected readonly ?int $id;
        protected ?string $fullname;
        protected ?string $username;
        protected ?string $password;
    }
}