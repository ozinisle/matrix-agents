<?php namespace MatrixAgentsAPI\Modules\Login\Interfaces;

interface LoginUserRecordInterface
{
    public function getUserId(): string;
    public function setUserId(string $userId): LoginUserRecordInterface;
    public function getUserName(): string;
    public function setUserName(string $userName): LoginUserRecordInterface;
    public function getPassword(): string;
    public function setPassword(string $password): LoginUserRecordInterface;
    public function getUserRole(): string;
    public function setUserRole(string $userRole): LoginUserRecordInterface;
}
