<?php namespace MatrixAgentsAPI\Modules\Login\Model;

use MatrixAgentsAPI\Modules\Login\Interfaces\GenericTableTransactionResponseModelInterface;
use MatrixAgentsAPI\Shared\Models\MatrixGenericResponseModel;

class GenericTableTransactionResponseModel extends MatrixGenericResponseModel implements GenericTableTransactionResponseModelInterface
{

    private $matchingRecords; //: LoginUserRecord

    public function getMatchingRecords()
    {
        return $this->matchingRecords;
    }

    public function setMatchingRecords($matchingRecords): GenericTableTransactionResponseModelInterface
    {
        $this->matchingRecords = $matchingRecords;
        return $this;
    }

    public function getJson()
    {
        //returns the json equivalent of the current class object
        return get_object_vars($this);
    }

    public function getJsonString(): string
    {
        //returns the json string equivalent of the current class object
        return json_encode(get_object_vars($this));
    }
}
