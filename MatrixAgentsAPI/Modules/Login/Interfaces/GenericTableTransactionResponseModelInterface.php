<?php namespace MatrixAgentsAPI\Modules\Login\Interfaces;

use MatrixAgentsAPI\Shared\Models\Interfaces\MatrixGenericResponseModelInterface;

interface GenericTableTransactionResponseModelInterface extends MatrixGenericResponseModelInterface
{
    public function getMatchingRecords(): GenericTableTransactionResponseModelInterface;
    public function setMatchingRecords($matchingRecords): GenericTableTransactionResponseModelInterface;
}
