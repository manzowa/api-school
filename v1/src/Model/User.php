<?php

/**
 * File User
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

namespace ApiSchool\V1\Model {

    use \ApiSchool\V1\Exception\UserException;

    final class User
    {
        protected readonly ?int $id;
        protected ?string $fullname;
        protected ?string $username;
        protected ?string $email;
        protected ?string $password;
        protected ?string $active;
        protected ?int $attempts;

        public function __construct(
            ?int $id,
            ?string $fullname,
            ?string $username,
            ?string $email,
            ?string $password,
            ?string $active = 'Y',
            ?int $attempts = 0
        ) {
            $this
                ->setId($id)
                ->setFullname($fullname)
                ->setUsername($username)
                ->setEmail($email)
                ->setPassword($password)
                ->setActive($active)
                ->setAttempts($attempts);
        }
        /**
         * Get the value of id
         */
        public function getId(): ?int
        {
            return $this->id;
        }
        /**
         * Get the value of fullname
         *
         * @return ?string
         */
        public function getFullname(): ?string
        {
            return $this->fullname;
        }
        /**
         * Get the value of username
         *
         * @return ?string
         */
        public function getUsername(): ?string
        {
            return $this->username;
        }
        /**
         * Get the value of email
         *
         * @return ?string
         */
        public function getEmail(): ?string
        {
            return $this->email;
        }
        /**
         * Get the value of password
         *
         * @return ?string
         */
        public function getPassword(): ?string
        {
            return $this->password;
        }
        /**
         * Get the value of active
         *
         * @return ?string
         */
        public function getActive(): ?string
        {
            return $this->active;
        }
        /**
         * Get the value of attempts
         *
         * @return ?int
         */
        public function getAttempts(): ?int
        {
            return $this->attempts;
        }
        /**
         * Set the value of id
         */
        public function setId(?int $id): self
        {
            if ((!is_null($id))
                && (!is_numeric($id) || $id <= 0 || $id >   9223372036854775807)
            ) {
                throw new UserException("User ID error");
            }
            $this->id = $id;
            return $this;
        }
        /**
         * Set the value of fullname
         *
         * @param ?string $fullname
         *
         * @return self
         */
        public function setFullname(?string $fullname): self
        {
            if (
                is_null($fullname)
                || (@strlen($fullname) < 0)
                || (@strlen($fullname) > 255)
            ) {
                throw new UserException("School fullname error.");
            }
            $this->fullname = $fullname;
            return $this;
        }
        /**
         * Set the value of username
         *
         * @param ?string $username
         *
         * @return self
         */
        public function setUsername(?string $username): self
        {
            if (
                is_null($username)
                || mb_strlen($username) < 0
                || mb_strlen($username) > 255
            ) { 
                throw new UserException("School Username error.");
            }
            $this->username = $username;
            return $this;
        }
        /**
         * Set the value of email
         *
         * @param ?string $email
         *
         * @return self
         */
        public function setEmail(?string $email): self
        {
            if (
                is_null($email)
                || mb_strlen($email) < 0
                || mb_strlen($email) > 255
                ||!filter_var($email, FILTER_VALIDATE_EMAIL)
            ) {
                throw new UserException("School Email is not valid.");
            }
            $this->email = $email;
            return $this;
        }
        /**
         * Set the value of password
         *
         * @param ?string $password
         *
         * @return self
         */
        public function setPassword(?string $password): self
        {
            if (
                is_null($password)
                || mb_strlen($password) < 1
                || mb_strlen($password) > 255
            ) {
                throw new UserException("School Password error.");
            }
            $this->password = $password;
            return $this;
        }
        /**
         * Set the value of active
         *
         * @param ?string $active
         *
         * @return self
         */
        public function setActive(?string $active): self
        {
            $active = empty($active)? $active : mb_strtoupper($active, 'UTF-8');
            $this->active = $active;
            return $this;
        }
        /**
         * Set the value of attempts
         *
         * @param ?int $attempts
         *
         * @return self
         */
        public function setAttempts(?int $attempts): self
        {
            if (!is_null($attempts) && !is_numeric($attempts)) {
                throw new UserException("School attempts error.");
            }
            $this->attempts = $attempts;
            return $this;
        }
        public function toArray(): array
        {
            return [
                'id'        => $this->getId(),
                'fullname'  => $this->getFullname(),
                'username'  => $this->getUsername(),
                'email'     => $this->getEmail(),
                'password'  => $this->getPassword(),
                'active'    => $this->getActive(),
                'attempts'  => $this->getAttempts()
            ];
        }
        public static function fromState(array $data = [])
        {
            return new static(
                id: $data['id'] ?? null,
                fullname: $data['fullname'] ?? null,
                username: $data['username'] ?? null,
                email: $data['email']?? null,
                password: $data['password'] ?? null,
                active: $data['active'] ?? null,
                attempts: $data['attempts'] ?? null
            );
        }
        public function isActive(): bool {
            return $this->getActive() === 'Y';
        }
        public function resetAttempts(): self {
            return $this->setAttempts(0);
        }
        public function incrementAttempts(): self {
            return $this->setAttempts($this->getAttempts() + 1);
        }
        public function isLocked(): bool {
            return $this->getAttempts() >= 3;
        }
    }
}
