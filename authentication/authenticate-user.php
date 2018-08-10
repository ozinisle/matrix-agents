<?php 

require_once('../lib/securityModule/securityAuthentication.php');

use MatrixSecurityAuthentication\MatrixAuthenticator ;

    echo MatrixSecurityAuthentication\MatrixAuthenticator::login();


?>