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
namespace ApiSchool\V1\Mapper\Trait;


trait UserTrait
{
    public function userRetrieve(
        ?int $id= null,
        ?\ApiSchool\V1\Model\User $user = null
    ):self {
        if (!is_null($id)) {
            $command = 'SELECT users.* FROM users WHERE id = :id';
            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT);
        } elseif(is_null($id) &&!is_null($user) && $user instanceof \ApiSchool\V1\Model\User) {
            $id = $user->getId();
            $username = $user->getUsername();
            $email = $user->getEmail();

            $command  = 'SELECT users.* FROM users WHERE id = :id ';
            $command .= 'AND email = :email AND username = :username';
            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->bindParam(':email', $email, \PDO::PARAM_STR)
                ->bindParam(':username', $username, \PDO::PARAM_STR);
        } else {
            $command  = 'SELECT users.* FROM users';
            $this->prepare($command);
        }
        return $this;
    }
    public function userAdd(\ApiSchool\V1\Model\User $user): self {
    
        $fullname = $user->getFullName();
        $username = $user->getUsername();
        $email = $user->getEmail();
        $password = $user->getPassword();
        $active = $user->getActive();
        $attempts = $user->getAttempts();
       
        $command  = 'INSERT INTO users (fullname, username, email, password, active, attempts) ';
        $command .= 'VALUES (:fullname, :username, :email, :password, :active, :attempts)';

        $this
            ->prepare($command)
            ->bindParam(':fullname', $fullname, \PDO::PARAM_STR)
            ->bindParam(':username', $username, \PDO::PARAM_STR)
            ->bindParam(':email', $email, \PDO::PARAM_STR)
            ->bindParam(':password', $password, \PDO::PARAM_STR)
            ->bindParam(':active', $active, \PDO::PARAM_STR)
            ->bindParam(':attempts', $attempts, \PDO::PARAM_INT);
        return $this;
    }
    public function userUpdate(\ApiSchool\V1\Model\User $user): self{
        $id = $user->getId();
        $fullname = $user->getFullName();
        $username = $user->getUsername();
        $email = $user->getEmail();
        $password = $user->getPassword();
        $active = $user->getActive();
        $attempts = $user->getAttempts();

        $command  = 'UPDATE users SET fullname= :fullname, username= :username, email= :email, ';
        $command .= 'password= :password, active= :active, attempts= :attempts '; 
        $command .= 'WHERE id= :id ';


        $this 
            ->prepare($command)
            ->bindParam(':fullname', $fullname, \PDO::PARAM_STR)
            ->bindParam(':username', $username, \PDO::PARAM_STR)
            ->bindParam(':email', $email, \PDO::PARAM_STR)
            ->bindParam(':password', $password, \PDO::PARAM_STR)
            ->bindParam(':active', $active, \PDO::PARAM_STR)
            ->bindParam(':attempts', $attempts, \PDO::PARAM_INT)
            ->bindParam(':id', $id, \PDO::PARAM_INT);
        return $this;
    }
    public function userRemove(int $id): self {
        if (!is_null($id)) {
            $command = 'DELETE FROM users WHERE id = :id';
            $this
                ->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT);
        }
        return $this;
    }

    public function userCounter() {
        $this
            ->prepare('SELECT COUNT(*) as total FROM users');
        
        return $this;
    }

    public function userLogin(string $username, string $password):? self {
        $command = 'SELECT users.* FROM users WHERE username = :username AND password = :password';
        $this
            ->prepare($command)
            ->bindParam(':username', $username, \PDO::PARAM_STR)
            ->bindParam(':password', $password, \PDO::PARAM_STR);

        return $this;
    }

    public function findUserByUsername(string $username):? self {
        $command = 'SELECT users.* FROM users WHERE username = :username';
        $this->prepare($command)
            ->bindParam(':username', $username, \PDO::PARAM_STR);

        return $this;
    }
    public function findUserByUsernameAndPassword(string $username, string $password):? self {
        $command  = 'SELECT users.* FROM users WHERE username = :username ';
        $command .= 'AND users.password = :password';

        $this
            ->prepare($command)
            ->bindParam(':username', $username, \PDO::PARAM_STR)
            ->bindParam(':password', $password, \PDO::PARAM_STR);
            
        return $this;
    }
 
}