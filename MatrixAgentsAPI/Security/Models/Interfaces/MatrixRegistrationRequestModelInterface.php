<?php namespace MatrixAgentsAPI\Security\Models\Interfaces;

interface MatrixRegistrationRequestModelInterface
{
    public function getUsername() : string;
    public function setUsername($username) : MatrixRegistrationRequestModelInterface;
    public function getPassword() : string;
    public function setPassword($password) : MatrixRegistrationRequestModelInterface;
    public function getFirstname() : string;
    public function setFirstname($firstname) : MatrixRegistrationRequestModelInterface;
    public function getLastname() : string;
    public function setLastname($lastname) : MatrixRegistrationRequestModelInterface;
    public function getEmail() : string;
    public function setEmail($email) : MatrixRegistrationRequestModelInterface;
    public function getMobile() : string;
    public function setMobile($mobile) : MatrixRegistrationRequestModelInterface;
}