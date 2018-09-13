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
}