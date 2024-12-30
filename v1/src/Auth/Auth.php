<?php

/**
 * File Auth
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

namespace ApiSchool\V1\Auth;

use \ApiSchool\V1\Database\Connexion;
use \ApiSchool\V1\Model\Token;
use \ApiSchool\V1\Model\User;

class Auth
{
    private string $token;
    private \ApiSchool\V1\Mapper\VendorMapper $mapper;

    public function __construct(string $token)
    {
        $connexionRead = Connexion::read();
        $this->token = $token;
        $this->mapper = new \ApiSchool\V1\Mapper\VendorMapper($connexionRead);
    }

    public function isValid(): bool{
        return is_object($this->getToken()) ?: false;
    }

    public function getToken(): ?Token 
    { 
       if (!is_null($this->token) && @strlen($this->token) > 1) {
            $rowToken = $this->mapper
                ->sessionRetrieve(accessToken: $this->token)
                ->executeQuery()
                ->getResults();

            if ($this->mapper->rowCount() === 0) return null;
       
            $row = current($rowToken);
            $token = Token::fromState(data: $row);

            return $token;
       }
       return null;
    }

    public function getUser(): ?User 
    {
        if ($this->getToken()) {
            $rowUser = $this->mapper
                ->userRetrieve(id: $this->getToken()->getUserId())
                ->executeQuery()
                ->getResults();
            
            if ($this->mapper->rowCount() === 0) return null;
            $row = current($rowUser);
            $user = User::fromState(data: $row);

            return $user;
        }
        return null;
    }
}
