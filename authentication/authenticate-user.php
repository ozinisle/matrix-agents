<?php 
//DEV TEMP
//NOTE - change the header to production url in production
header('Access-Control-Allow-Origin: *');

include('../vendor/autoload.php');

use MatrixAgentsAPI\Security\Authenticator as MatrixAuth;

session_start();
$authenticator = new MatrixAuth();
echo $authenticator->login();
 