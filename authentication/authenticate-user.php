<?php 

include('../vendor/autoload.php');

use MatrixAgentsAPI\Security\Authenticator as MatrixAuth;

session_start();
$authenticator = new MatrixAuth();
echo $authenticator->login();

?>