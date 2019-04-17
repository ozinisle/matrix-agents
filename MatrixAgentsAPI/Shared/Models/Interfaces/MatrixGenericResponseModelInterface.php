<?php namespace MatrixAgentsAPI\Shared\Models\Interfaces;

interface MatrixGenericResponseModelInterface
{
    public function getStatus(): string;
    public function setStatus(string $status): MatrixGenericResponseModelInterface;
    public function getErrorMessage(): string;
    public function setErrorMessage(string $errorMessage): MatrixGenericResponseModelInterface;
    public function getDisplayMessage(): string;
    public function setDisplayMessage(string $displayMessage): MatrixGenericResponseModelInterface;
    public function getResponseCode(): string;
    public function setResponseCode(string $responseCode): MatrixGenericResponseModelInterface;

    public function getJson();
    public function getJsonString(): string;
}
