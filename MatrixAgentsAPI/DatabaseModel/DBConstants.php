<?php namespace MatrixAgentsAPI\DatabaseModel;

class DBConstants
{
    const MatrixUsersTable = array(
        "name" => "matrix_users",
        "model" => array(
            "userid",
            "username",
            "password",
            "email",
            "mobile",
            "is_admin",
            "user_role",
            "failed_login_attempts",
            "user_active_status"
        )
    );

    // error code format => yymmdd- Segment Runnin Number(001, 002 soon)-Running Code (0001,0002...so on) :
    //  error code example :: "LoginUserNameNotExist" => "1904170020002", ---- 190417->yymmdd -- 002 -> segment 2 (Login) --- 0002  second response code in the module
    const ResponseCode = array(
        "RegistrationSuccess" => "1904170010001",
        "RegistrationFailure" => "-1904170010002",
        "RegistrationNameAlreadyExists" => "-1904170010003",
        "RegistrationPasswordComplexityNotMet" => "-1904170010004",
        "LoginSuccess" => "1904170020001",
        "LoginIncorrectUserNamePassword" => "-1904170020002",
        "LoginFailure" => "-1904170020003",
        "ForgotPassword" => "-1904170020005",

        "SendVerificationEmailSuccess" => "1904250030001",
        "SendVerificationEmailFailure" => "-1904250030002",

        "NewNoteCreatedSuccess" => "1905020040001",
        "InvalidInputData" => "-1905020040002",
        "SilentFailure" => "-1905030040003",

        "InvalidRequest" => "-1904290990001",
        "RandomError" => "-1904290990002",
        "CodeError" => "-1904290990003",
    );

    const DisplayMessages = array(
        "TemporaryServiceDownMessage" => "The service is temporarily unavailable. Please contact the support team to seek help in this regard",
        "RegistrationUserNameExists" => "User name already exists. Try forgot password link to recover your password",
        "LoginIncorrectUserNamePassword" => "Incorrect Username or Password",
        "InvalidRequest" => "Invalid request. No request body information found",
        "InvalidInputData" => "Invalid Input Data",
        "SilentFailure" => "Code Execution did not complete. Possibly due to syntax or structural errors"
    );

    const StatusFlags = array(
        "Success" => "SUCCESS",
        "Failure" => "FAILURE"
    );
}
