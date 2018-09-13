<?php namespace MatrixAgentsAPI\Shared\Models;

use MatrixAgentsAPI\Shared\Models\Interfaces\MatrixGenericResponseModelInterface;


class MatrixGenericResponseModel implements MatrixGenericResponseModelInterface
{
    private $status; // SUCCESS or FAILURE
    private $errorMessage;
    private $displayMessage;

    public function getStatus() : string
    {
        return $this->status;
    }

    public function setStatus($status) : MatrixGenericResponseModel
    {
        $this->status = $status;
        return $this;
    }

    public function getErrorMessage() : string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage($errorMessage) : MatrixGenericResponseModel
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function getDisplayMessage() : string
    {
        return $this->displayMessage;
    }

    public function setDisplayMessage($displayMessage) : MatrixGenericResponseModel
    {
        $this->displayMessage = $displayMessage;
        return $this;
    }

    public function getJson()
    {
        //returns the json equivalent of the current class object
        return get_object_vars($this);
    }

    public function getJsonString() : string
    {
        //returns the json string equivalent of the current class object
        return json_encode(get_object_vars($this));
    }
}