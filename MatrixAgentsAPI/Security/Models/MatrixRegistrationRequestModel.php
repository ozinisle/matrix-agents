<?php namespace MatrixAgentsAPI\Security\Models;

use MatrixAgentsAPI\Security\Models\Interfaces\MatrixRegistrationRequestModelInterface;

class MatrixRegistrationRequestModel implements MatrixRegistrationRequestModelInterface
{
    private $username;
    private $password;
    private $firstname;
    private $lastname;
    private $email;
    private $mobile;

    public function getUsername() : string
    {
        $this->username;
    }

    public function setUsername($username) : MatrixRegistrationRequestModel
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword() : string
    {
        return $this->password;
    }

    public function setPassword($password) : MatrixRegistrationRequestModel
    {
        $this->password = $password;
        return $this;
    }

    public function getFirstname() : string
    {
        return $this->firstname;
    }

    public function setFirstname($firstname) : MatrixRegistrationRequestModel
    {
        $this->firstname = $firstname;
        return $this;
    }
    public function getLastname() : string
    {
        return $this->lastname;
    }

    public function setLastname($lastname) : MatrixRegistrationRequestModel
    {
        $this->$lastname = $lastname;
        return $this;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function setEmail($email) : MatrixRegistrationRequestModel
    {
        $this->email = $email;
        return $this;
    }

    public function getMobile() : string
    {
        return $this->mobile;
    }

    public function setMobile($mobile) : MatrixRegistrationRequestModel
    {
        $this->mobile = $mobile;
        return $this;
    }
}