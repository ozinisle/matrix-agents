<?php namespace MatrixAgentsAPI\Modules\Login\Model;

use MatrixAgentsAPI\Modules\Login\Interfaces\LoginResponseModelInterface;
use MatrixAgentsAPI\Shared\Models\MatrixGenericResponseModel;
use MatrixAgentsAPI\Modules\Login\Interfaces\LoginUserRecordInterface;

class LoginResponseModel extends MatrixGenericResponseModel implements LoginResponseModelInterface
{

    private $userRecord; //: LoginUserRecord

    public function getUserRecord(): LoginUserRecordInterface
    {
        return $this->userRecord;
    }

    public function setUserRecord(LoginUserRecordInterface $userRecord): LoginResponseModelInterface
    {
        $this->userRecord = $userRecord;
        return $this;
    }
}
