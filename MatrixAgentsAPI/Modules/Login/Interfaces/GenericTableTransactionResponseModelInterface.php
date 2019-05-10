<?php namespace MatrixAgentsAPI\Modules\Login\Interfaces;

use MatrixAgentsAPI\Shared\Models\Interfaces\MatrixGenericResponseModelInterface;

interface GenericTableTransactionResponseModelInterface extends MatrixGenericResponseModelInterface
{
    public function getMatchingRecords();
    public function setMatchingRecords($matchingRecords): GenericTableTransactionResponseModelInterface;

    public function getJson();
    public function getJsonString(): string;
}
