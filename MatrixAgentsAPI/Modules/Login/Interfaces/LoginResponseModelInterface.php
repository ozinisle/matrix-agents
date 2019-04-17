<?php namespace MatrixAgentsAPI\Modules\Login\Interfaces;

use MatrixAgentsAPI\Shared\Models\Interfaces\MatrixGenericResponseModelInterface;
use MatrixAgentsAPI\Modules\Login\Interfaces\LoginUserRecordInterface;

interface LoginResponseModelInterface extends MatrixGenericResponseModelInterface
{
    public function getUserRecord(): LoginUserRecordInterface;
    public function setUserRecord(LoginUserRecordInterface $userRecord): LoginResponseModelInterface;
}
