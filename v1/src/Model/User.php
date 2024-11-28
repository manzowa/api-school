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
        protected ?string $active;
        protected ?int $attempts;

        /**
         * Get the value of id
         */
        public function getId() {
            return $this->id;
        }

        /**
         * Get the value of fullname
         *
         * @return ?string
         */
        public function getFullname(): ?string {
            return $this->fullname;
        }

        /**
         * Get the value of username
         *
         * @return ?string
         */
        public function getUsername(): ?string {
            return $this->username;
        }

        /**
         * Get the value of password
         *
         * @return ?string
         */
        public function getPassword(): ?string {
            return $this->password;
        }

        /**
         * Get the value of active
         *
         * @return ?string
         */
        public function getActive(): ?string {
            return $this->active;
        }

        /**
         * Get the value of attempts
         *
         * @return ?int
         */
        public function getAttempts(): ?int {
            return $this->attempts;
        }
    }
}