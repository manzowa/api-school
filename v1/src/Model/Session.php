<?php

/**
 * File Session
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

    use \DateTime;

    final class Session
    {
        public function __construct
        (
            protected readonly ?int $id,
            protected ?int $userid,
            protected ?string $accessToken,
            protected ?DateTime $accessTokenExpiry,
            protected ?string $refreshToken,
            protected ?DateTime $refreshTokenExpiry
        )
        {}

        /**
         * Get the value of id
         */
        public function getId(): ?int
        {
            return $this->id;
        }

        /**
         * Get the value of userid
         *
         * @return ?int
         */
        public function getUserid(): ?int
        {
            return $this->userid;
        }

        /**
         * Get the value of accessToken
         *
         * @return ?string
         */
        public function getAccessToken(): ?string
        {
            return $this->accessToken;
        }

        /**
         * Get the value of accessTokenExpiry
         *
         * @return ?DateTime
         */
        public function getAccessTokenExpiry(): ?DateTime
        {
            return $this->accessTokenExpiry;
        }

        /**
         * Get the value of refreshToken
         *
         * @return ?string
         */
        public function getRefreshToken(): ?string
        {
            return $this->refreshToken;
        }

        /**
         * Get the value of refreshTokenExpiry
         *
         * @return ?DateTime
         */
        public function getRefreshTokenExpiry(): ?DateTime
        {
            return $this->refreshTokenExpiry;
        }

        /**
         * Set the value of id
         */
        public function setId(?int $id): self
        {
            $this->id = $id;
            return $this;
        }

        /**
         * Set the value of userid
         *
         * @param ?int $userid
         *
         * @return self
         */
        public function setUserid(?int $userid): self
        {
            $this->userid = $userid;
            return $this;
        }

        /**
         * Set the value of accessToken
         *
         * @param ?string $accessToken
         *
         * @return self
         */
        public function setAccessToken(?string $accessToken): self
        {
            $this->accessToken = $accessToken;
            return $this;
        }

        /**
         * Set the value of accessTokenExpiry
         *
         * @param ?DateTime $accessTokenExpiry
         *
         * @return self
         */
        public function setAccessTokenExpiry(?DateTime $accessTokenExpiry): self
        {
            $this->accessTokenExpiry = $accessTokenExpiry;
            return $this;
        }

        /**
         * Set the value of refreshToken
         *
         * @param ?string $refreshToken
         *
         * @return self
         */
        public function setRefreshToken(?string $refreshToken): self
        {
            $this->refreshToken = $refreshToken;
            return $this;
        }

        /**
         * Set the value of refreshTokenExpiry
         *
         * @param ?DateTime $refreshTokenExpiry
         *
         * @return self
         */
        public function setRefreshTokenExpiry(?DateTime $refreshTokenExpiry): self
        {
            $this->refreshTokenExpiry = $refreshTokenExpiry;
            return $this;
        }
    }
}
