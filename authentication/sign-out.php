<?php 

include('../vendor/autoload.php');

use MatrixAgentsAPI\Security\Authenticator as MatrixAuth;

session_start();
session_regenerate_id();

$authenticator = new MatrixAuth();
echo $authenticator->logoff();
 