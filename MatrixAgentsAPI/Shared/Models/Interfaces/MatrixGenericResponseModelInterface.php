<?php namespace MatrixAgentsAPI\Shared\Models\Interfaces;

interface MatrixGenericResponseModelInterface
{
    public function getStatus() : string;
    public function setStatus($status);
    public function getErrorMessage() : string;
    public function setErrorMessage($errorMessage);
    public function getDisplayMessage() : string;
    public function setDisplayMessage($displayMessage);

    public function getJson();
    public function getJsonString() : string;
}