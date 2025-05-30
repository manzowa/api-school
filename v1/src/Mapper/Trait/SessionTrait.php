<?php

/**
 *  SessionTrait
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

namespace ApiSchool\V1\Mapper\Trait;

trait SessionTrait
{

    public function sessionRetrieve(
        ?int $id = null, 
        ?int $userId = null,
        ?string $accessToken = null, 
        ?string $accessTokenExpiry = null,
        ?string $refreshToken = null,
        ?string $refreshTokenExpiry = null
    ): self
    {

        if ((!is_null($id) && $id > 0) 
            && (is_null($userId) || $userId <= 0)
            && (is_null($accessToken) || $accessToken === '')
            && (is_null($accessTokenExpiry) || $accessTokenExpiry === '')
            && (is_null($refreshToken) || $refreshToken === '')
            && (is_null($refreshTokenExpiry) || $refreshTokenExpiry === '')
        ) {
            $command = 'SELECT sessions.* FROM sessions WHERE id = :id';
            $this
                ->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT);
        } elseif (
            (!is_null($userId) && $userId > 0) 
            && (is_null($id) || $id <= 0)
            && (is_null($accessToken) || $accessToken === '')
            && (is_null($accessTokenExpiry) || $accessTokenExpiry === '')
            && (is_null($refreshToken) || $refreshToken === '')
            && (is_null($refreshTokenExpiry) || $refreshTokenExpiry === '')
        ) {
            $command = 'SELECT sessions.* FROM sessions ';
            $command .= 'WHERE userid = :userId';
            $this
                ->prepare($command)
                ->bindParam(':userId', $userId, \PDO::PARAM_INT);
        }  elseif(
            (!is_null($accessToken) && $accessToken !== '')
            && (is_null($id) || $id <= 0)
            && (is_null($userId) && $userId <= 0)
            && (is_null($accessTokenExpiry) && $accessTokenExpiry === '')
            && (is_null($refreshToken) && $refreshToken === '')
            && (is_null($refreshTokenExpiry) && $refreshTokenExpiry === '')
        ) {
            
            $command = 'SELECT sessions.* FROM sessions WHERE accessToken = :accessToken';
            $this
                ->prepare($command)
                ->bindParam(':accessToken', $accessToken, \PDO::PARAM_STR);

        } elseif (
            (!is_null($id) && $id > 0) 
            && (!is_null($userId) && $userId > 0)
            && (is_null($accessToken) || $accessToken === '')
            && (is_null($accessTokenExpiry) || $accessTokenExpiry === '')
            && (is_null($refreshToken) || $refreshToken === '')
            && (is_null($refreshTokenExpiry) || $refreshTokenExpiry === '')
        ) {
            $command = 'SELECT sessions.* FROM sessions WHERE id = :id ';
            $command.= ' AND userid = :userId ';
            $this
                ->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->bindParam(':userId', $userId, \PDO::PARAM_INT);
        } elseif(
            (!is_null($accessToken) && $accessToken !== '') 
            && (is_null($id) || $id <= 0)
            && (is_null($userId) && $userId <= 0)
            && (is_null($accessTokenExpiry) && $accessTokenExpiry == '')
            && (is_null($refreshToken) && $refreshToken == '')
            && (is_null($refreshTokenExpiry) && $refreshTokenExpiry == '')
        ) {
            $command = 'SELECT sessions.* FROM sessions WHERE accessToken = :accessToken ';
            $this
                ->prepare($command)
                ->bindParam(':accessToken', $accessToken, \PDO::PARAM_STR);

        } elseif(
            (!is_null($id) && $id > 0) 
            && (!is_null($accessToken) && $accessToken !== '')
            && (!is_null($refreshToken) && $refreshToken !== '')
            && (is_null($userId) || $userId <= 0)
            && (is_null($accessTokenExpiry) && $accessTokenExpiry == '')
            && (is_null($refreshTokenExpiry) && $refreshTokenExpiry == '')
        ) {
            $command = 'SELECT sessions.* FROM sessions WHERE id = :id ';
            $command.= 'AND accessToken = :accessToken ';
            $command.= 'AND refreshtoken = :refreshToken ';
            $this
                ->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->bindParam(':accessToken', $accessToken, \PDO::PARAM_STR)
                ->bindParam(':refreshToken', $refreshToken, \PDO::PARAM_STR);

        } else {
            $command = 'SELECT sessions.* FROM sessions';
            $this
                ->prepare($command);
        }

        return $this;
    }

    public function sessionAdd(\ApiSchool\V1\Model\Token $session): self
    {
        $userId = $session->getUserId();
        $accessToken = $session->getAccessToken();
        $accessTokenExpiry = $session->getAccessTokenExpiry();
        $refreshToken = $session->getRefreshToken();
        $refreshTokenExpiry = $session->getRefreshTokenExpiry();

        $command  = 'INSERT INTO sessions (userid, accesstoken, ';
        $command .= 'accesstokenexpiry, refreshtoken, refreshtokenexpiry) ';
        $command .= 'VALUES ( ';
        $command .= ':userId, ';
        $command .= ':accessToken, date_add(NOW(), INTERVAL :accessTokenExpiry SECOND),';
        $command .= ':refreshToken, date_add(NOW(), INTERVAL :refreshTokenExpiry SECOND)';
        $command .= ')';

        $this
            ->prepare($command)
            ->bindParam(':userId', $userId, \PDO::PARAM_INT)
            ->bindParam(':accessToken', $accessToken, \PDO::PARAM_STR)
            ->bindParam(':accessTokenExpiry', $accessTokenExpiry, \PDO::PARAM_STR)
            ->bindParam(':refreshToken', $refreshToken, \PDO::PARAM_STR)
            ->bindParam(':refreshTokenExpiry', $refreshTokenExpiry, \PDO::PARAM_STR);

        return $this;
    }

    public function sessionUpdate(\ApiSchool\V1\Model\Token $session): self
    {
        $userId = $session->getUserId();
        $accessToken = $session->getAccessToken();
        $accessTokenExpiry = $session->getAccessTokenExpiry();
        $refreshToken = $session->getRefreshToken();
        $refreshTokenExpiry = $session->getRefreshTokenExpiry();
        $id = $session->getId();

        $command  = 'UPDATE sessions SET userid= :userId, accesstoken= :accessToken, ';
        $command .= 'accesstokenexpiry= :accessTokenExpiry, refreshtoken= :refreshToken, ';
        $command .= 'refreshtokenexpiry= :refreshTokenExpiry WHERE id= :id ';

        $this
            ->prepare($command)
            ->bindParam(':userId', $userId, \PDO::PARAM_INT)
            ->bindParam(':accessToken', $accessToken, \PDO::PARAM_STR)
            ->bindParam(':accessTokenExpiry', $accessTokenExpiry, \PDO::PARAM_STR)
            ->bindParam(':refreshToken', $refreshToken, \PDO::PARAM_STR)
            ->bindParam(':refreshTokenExpiry', $refreshTokenExpiry, \PDO::PARAM_STR)
            ->bindParam(':id', $id, \PDO::PARAM_INT);

        return $this;
    }

    public function sessionRemove(int $id, string $accessToken = null): self
    {
        if ((!is_null($id) && $id > 0) && is_null($accessToken)) {
            $command = 'DELETE FROM sessions WHERE id = :id';
            $this
                ->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT);
        } elseif ((!is_null($id) && $id > 0)
            && (!is_null($accessToken) && $accessToken !== "")
        ) {
            $command = 'DELETE FROM sessions WHERE id = :id ';
            $command .= 'AND accessToken = :accessToken';
            $this
                ->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->bindParam(':accessToken', $accessToken, \PDO::PARAM_STR);
        }
        return $this;
    }
}
