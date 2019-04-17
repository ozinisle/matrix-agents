<?php namespace MatrixAgentsAPI\Modules\Login\Model;

use MatrixAgentsAPI\Modules\Login\Interfaces\LoginUserRecordInterface;

class LoginUserRecord implements LoginUserRecordInterface
{

    private $userId;
    private $username;
    private $password;
    private $userRole;

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): LoginUserRecordInterface
    {
        $this->userId = $userId;
        return $this;
    }

    public function getUserName(): string
    {
        return $this->username;
    }

    public function setUserName(string $username): LoginUserRecordInterface
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): LoginUserRecordInterface
    {
        $this->password = $password;
        return $this;
    }

    public function getUserRole(): string
    {
        return $this->userRole;
    }

    public function setUserRole(string $userRole): LoginUserRecordInterface
    {
        $this->userRole = $userRole;
        return $this;
    }
}
