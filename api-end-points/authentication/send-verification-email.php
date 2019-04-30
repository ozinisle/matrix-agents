<?php
//DEV TEMP
//NOTE - change the header to production url in production
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type: application/json");

include('../../vendor/autoload.php');

use MatrixAgentsAPI\Security\Authenticator as MatrixAuth;

session_start();
session_regenerate_id();

$authenticator = new MatrixAuth();
echo $authenticator->sendVerificationEmail();
