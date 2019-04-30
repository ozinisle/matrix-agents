<?php namespace MatrixAgentsAPI\Modules\Login\Model;

use MatrixAgentsAPI\Modules\Login\Interfaces\GenericTableTransactionResponseModelInterface;
use MatrixAgentsAPI\Shared\Models\MatrixGenericResponseModel;

class GenericTableTransactionResponseModel extends MatrixGenericResponseModel implements GenericTableTransactionResponseModelInterface
{

    private $matchingRecords; //: LoginUserRecord

    public function getMatchingRecords(): GenericTableTransactionResponseModelInterface
    {
        return $this->matchingRecords;
    }

    public function setMatchingRecords($matchingRecords): GenericTableTransactionResponseModelInterface
    {
        $this->matchingRecords = $matchingRecords;
        return $this;
    }
}
