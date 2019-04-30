<?php namespace MatrixAgentsAPI\Security;

use MatrixAgentsAPI\Security\JWT\Token;
use MatrixAgentsAPI\Utilities\EventLogger;
use MatrixAgentsAPI\Security\Encryption\OpenSSLEncryption;
use MatrixAgentsAPI\Security\Models\MatrixRegistrationResponseModel;
use MatrixAgentsAPI\DatabaseModel\UserTableTransactions;
use MatrixAgentsAPI\DatabaseModel\DBConstants;
use MatrixAgentsAPI\Modules\Login\Model\LoginResponseModel;
use MatrixAgentsAPI\DatabaseModel\ForgotPasswordTableTransactions;

class Authenticator
{
    private $logger;
    private $authenticatedUserName = '';
    private $opensslEncryption;

    private $iRememberProperties;
    private $login_pay_load = null;

    private $constStatusFlags = DBConstants::StatusFlags;
    private $constResponseCode = DBConstants::ResponseCode;
    private $constDisplayMessages = DBConstants::DisplayMessages;

    const MASK_LOG_TRUE = false;

    public function __construct()
    {
        $this->logger = new EventLogger();
        $this->opensslEncryption = new OpenSSLEncryption();
    }

    public function register(): string
    {

        //create the response object
        $registrationResponse = new MatrixRegistrationResponseModel();
        $displayMessage = 'The service is temporarily unavailable. Please contact the support team to seek help in this regard';
        $responseCode = $this->constResponseCode['RegistrationFailure'];

        try {
            $this->logger->debug('into method register >>> ', self::MASK_LOG_TRUE);

            //Start :: gather data relevant to the login attempt
            //initialize session
            $this->initializeSession();

            //compiler gets here only if the  request is from a valid origin
            //get the request body to extract the parameters posted to the request
            $request_body = $this->getRequestBody();

            $decryptedRegistrationRequest = $this->opensslEncryption->CryptoJSAesDecrypt($_SESSION['request_decryption_pass_phrase'], $request_body);

            if (empty($decryptedRegistrationRequest)) {
                $this->logger
                    ->errorEvent()
                    ->log('Invalid request. No request body information found');
                return $decryptedRegistrationRequest;
            }

            if ($decryptedRegistrationRequest->appName === "iRemember") {
                $this->logger->debug(' ****** Register with iRemember app ****** user name>>> ***' .
                    $decryptedRegistrationRequest->username
                    . '**** password **** ' . $decryptedRegistrationRequest->password, self::MASK_LOG_TRUE);

                $usrTabl = new UserTableTransactions();
                $usrTabl->setIRememberProperties($this->iRememberProperties);

                $registrationResponse = $usrTabl->addUser($decryptedRegistrationRequest->username, $decryptedRegistrationRequest->password, 'NRML');
            }
        } catch (Exception $e) {

            $registrationResponse->setStatus($this->constStatusFlags['Failure'])
                ->setDisplayMessage($displayMessage)
                ->setErrorMessage(var_export($e, true))
                ->setResponseCode($this->constResponseCode['RegistrationFailure']);

            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . var_export($e, true) . "\n");
        } finally {
            $this->logger->debug(' executing finally block in register() method', self::MASK_LOG_TRUE);

            return $this->getEncryptedResponse($registrationResponse->getJsonString());
        }
    }

    private function getEncryptedResponse($response): string
    {
        try {
            return $this->opensslEncryption->CryptoJSAesEncrypt($_SESSION['response_encryption_pass_phrase'], $response);
        } catch (Exception $e) {

            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . var_export($e, true) . "\n");
        }

        return null;
    }

    public function login(): string
    {
        try {
            //initializing default login response to aunthentication failure scenario
            $loginResponseToBeSent = "{\"isAuthenticate\":\"false\"}";

            //Start :: gather data relevant to the login attempt
            //get the site from which the login request has been triggered
            $referer = $_SERVER['HTTP_REFERER'];

            //print current working directory -- debug purpose
            //echo getcwd() . PHP_EOL;

            //initialize session
            $this->initializeSession();

            $request_body = $this->getRequestBody();

            $this->logger->debug(PHP_EOL . 'app debug mode set to >>>' . $_SESSION['debug_mode'], self::MASK_LOG_TRUE);
            $this->logger->debug('into login method ', self::MASK_LOG_TRUE);
            $this->logger->debug('login payload >>> ' . $request_body, self::MASK_LOG_TRUE);

            //Following decryption procedure shall be used for openssl mechanism for php7.2 or higher        
            //$reqData = $this->getRequest();
            $this->logger->debug('>>> $_SESSION[\'request_decryption_pass_phrase\']>>> ' . $_SESSION['request_decryption_pass_phrase'], self::MASK_LOG_TRUE);
            $this->logger->debug('>>> $request_body >>> ' . $request_body, self::MASK_LOG_TRUE);

            $decryptedRequest = $this->opensslEncryption->CryptoJSAesDecrypt($_SESSION['request_decryption_pass_phrase'], $request_body);
            // $this->logger->debug('decryptedRequest is >>> ' . var_dump($decryptedRequest), self::MASK_LOG_TRUE);
            if (empty($decryptedRequest)) {

                $this->logger
                    ->errorEvent()
                    ->log('Invalid request. No request body information found');
                return $loginResponseToBeSent;
            }

            $username = $decryptedRequest->username;
            $password = $decryptedRequest->password;
            //END :: gather data relevant to the login attempt         

            $this->logger->debug('>>> checking for allowed origins ', self::MASK_LOG_TRUE);

            //validate the data against the information stored in the data base 
            $usrTabl = new UserTableTransactions();
            $usrTabl->setIRememberProperties($this->iRememberProperties);

            $loginResponse = new LoginResponseModel();
            $loginResponse = $usrTabl->getUser($username, $password);

            $this->logger->debug('Authenticator >>> login >>> loginResponse is' . var_export($loginResponse, true), self::MASK_LOG_TRUE);


            if ($loginResponse->getStatus() == $this->constStatusFlags['Success']) {
                //if validation is succeeds authenticate the user and send an JWT token for future communication authentication

                $userId = $loginResponse->getUserRecord()->getUserId();
                $secretKey = $this->getJwtSecretKey();
                $expiration = date("Y-m-d H:i:s", strtotime('+2 hours'));
                $issuer = "http://www.techdotmasterpiece.com";
                $audience = "http://www.techdotmasterpiece.com/products/iRemember/login";
                $subject = 'user-session-authorization';

                $this->authenticatedUserName = $username;

                $token = Token::getToken($userId, $secretKey, $expiration, $issuer, $audience, $subject);

                $_SESSION['secretKey'] = $secretKey;

                $loginResponseToBeSent = "{\"isAuthenticated\":\"true\",\"token\":\"" . $token .
                    "\",\"authenticatedUserName\":\"" . $this->authenticatedUserName . "\"}"; //,\"referer\":\"" . $referer . "\"}";

                $this->logger->debug('>>> valid login detected >>> ' . $loginResponseToBeSent, self::MASK_LOG_TRUE);
            } else {
                $this->logger
                    ->securityEvent()
                    ->warningEvent()
                    ->log("Login attempt failed for the following credentials, " . PHP_EOL .
                        " username :" . $username . "," . PHP_EOL .
                        " password :" . $password . PHP_EOL .
                        " The request that was submitted is as follows >>> " . PHP_EOL .
                        $request_body);
                //if validation fails dont let the user to authenticate
                $loginResponseToBeSent = "{\"isAuthenticated\":\"false\",\"displayMessage\":\""
                    . $this->constDisplayMessages['LoginIncorrectUserNamePassword'] . "\"}";
            }
        } catch (Exception $e) {
            $loginResponseToBeSent = "{\"isAuthenticated\":\"false\"}";
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ');
            $this->logger
                ->errorEvent()
                ->log(var_export($e, true));
            $this->logger
                ->errorEvent()
                ->log(PHP_EOL);
        } finally {
            return $this->getEncryptedResponse($loginResponseToBeSent);
            //return $this->opensslEncryption->CryptoJSAesEncrypt($_SESSION['response_encryption_pass_phrase'], $loginResponseToBeSent);
        }
    }

    public function logoff(): string
    {
        if (isset($_SESSION['secretKey'])) {

            $secretKey = $_SESSION['secretKey'];

            $bearerToken = $this->getBearerToken();

            if ($bearerToken) {
                if (Token::validate($bearerToken, $secretKey)) {
                    unset($_SESSION['secretKey']);
                    return $this->constStatusFlags['Success'];
                } else {
                    //get the request body to extract the parameters posted to the request
                    $request_body = file_get_contents('php://input');
                    $this->logger
                        ->securityEvent()
                        ->log("Alert : Possible Fradulant attempt :: Log Out attempt failed for the request, " . PHP_EOL . $request_body);

                    return $this->constStatusFlags['Failure'];
                }
            }
        } else {
            //get the request body to extract the parameters posted to the request
            $request_body = file_get_contents('php://input');
            $this->logger
                ->securityEvent()
                ->errorEvent()
                ->log("Alert : Invalid Scenario :: Session values namely 'secretKey' is not set" . PHP_EOL .
                    " This is not supposed to happen :: Log Out attempt failed for the request, " . PHP_EOL . $request_body);

            return $this->constStatusFlags['Failure'];
        }
        exit;
    }

    public function sendVerificationEmail(): string
    {
        $sendVerificationEmailResponseToBeSent = new MatrixRegistrationResponseModel();
        try {
            $this->logger->debug('into sendVerificationEmail ', self::MASK_LOG_TRUE);

            //initializing default  response to aunthentication failure scenario
            $sendVerificationEmailResponseToBeSent->setStatus($this->constStatusFlags['Failure'])
                ->setDisplayMessage($this->constDisplayMessages['TemporaryServiceDownMessage'])
                ->setResponseCode($this->constResponseCode['RegistrationFailure']);


            //initialize session
            $this->initializeSession();

            $request_body = $this->getRequestBody();

            $this->logger->debug(PHP_EOL . 'app debug mode set to >>>' . $_SESSION['debug_mode'], self::MASK_LOG_TRUE);
            $this->logger->debug('into sendVerificationEmail method ', self::MASK_LOG_TRUE);
            $this->logger->debug('sendVerificationEmail payload >>> ' . $request_body, self::MASK_LOG_TRUE);

            //Following decryption procedure shall be used for openssl mechanism for php7.2 or higher        
            //$reqData = $this->getRequest();
            $this->logger->debug('>>> $_SESSION[\'request_decryption_pass_phrase\']>>> ' . $_SESSION['request_decryption_pass_phrase'], self::MASK_LOG_TRUE);
            $this->logger->debug('>>> $request_body >>> ' . $request_body, self::MASK_LOG_TRUE);

            $decryptedRequest = $this->opensslEncryption->CryptoJSAesDecrypt($_SESSION['request_decryption_pass_phrase'], $request_body);
            // $this->logger->debug('decryptedRequest is >>> ' . var_dump($decryptedRequest), self::MASK_LOG_TRUE);
            if (empty($decryptedRequest)) {

                $this->logger
                    ->errorEvent()
                    ->log('Invalid request. No request body information found');
                return $sendVerificationEmailResponseToBeSent;
            }

            $username = $decryptedRequest->username;
            //END :: gather data relevant to the login attempt         

            $this->logger->debug('>>> checking for allowed origins ', self::MASK_LOG_TRUE);

            //validate the data against the information stored in the data base 
            $usrTabl = new UserTableTransactions();
            $usrTabl->setIRememberProperties($this->iRememberProperties);
            $sendVerificationEmailResponseToBeSent = $usrTabl->isUser($username);

            $this->logger->debug('Authenticator >>> sendVerificationEmail >>> $sendVerificationEmailResponseToBeSent is >>>' . var_export($sendVerificationEmailResponseToBeSent, true), self::MASK_LOG_TRUE);

            if ($sendVerificationEmailResponseToBeSent->getStatus() == $this->constStatusFlags['Success']) {
                $this->logger->debug('Authenticator >>> sendVerificationEmail >>> $sendVerificationEmailResponseToBeSent status is success', self::MASK_LOG_TRUE);
                $forgotPasswordTbl = new ForgotPasswordTableTransactions();
                $forgotPasswordTbl->setIRememberProperties($this->iRememberProperties);

                $addForgotPasswordEntryResponse =  $forgotPasswordTbl->addForgotPasswordEntry($username);
                $this->logger
                    ->debug('Authenticator >>> sendVerificationEmail >>> $addForgotPasswordEntryResponse is >>> ' . var_export($addForgotPasswordEntryResponse, true), self::MASK_LOG_TRUE);

                return $sendVerificationEmailResponseToBeSent;
            } else {
                $this->logger->debug('Authenticator >>> sendVerificationEmail >>> $sendVerificationEmailResponseToBeSent status is failure', self::MASK_LOG_TRUE);
                $sendVerificationEmailResponseToBeSent->setDisplayMessage('Mail Will be sent if the email has already been registered')->setErrorMessage('')->setResponseCode('');
                return $sendVerificationEmailResponseToBeSent;
            }
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ');
            $this->logger
                ->errorEvent()
                ->log(var_export($e, true));
            $this->logger
                ->errorEvent()
                ->log(PHP_EOL);
        } finally {
            $this->logger->debug('out of sendVerificationEmail ', self::MASK_LOG_TRUE);
            return $this->getEncryptedResponse($sendVerificationEmailResponseToBeSent->getJsonString());
        }
    }

    private function initializeSession()
    {

        try {
            $this->logger->debug(' into method Authenticator::initializeSession()', self::MASK_LOG_TRUE);

            //get properties as a section segrated array
            $PROCESS_SECTIONS = true;

            //get app properties
            $this->iRememberProperties = parse_ini_file(realpath('../../i-remember-properties.ini'), $PROCESS_SECTIONS);

            $matrixAppFlags = $this->iRememberProperties['matrix-app-flags'];
            $_SESSION['debug_mode'] = $matrixAppFlags['debug_mode'];

            $matrixCommChannelPassPhrase = $this->iRememberProperties['matrix-comm-channel-pass-phrase'];
            $_SESSION['request_decryption_pass_phrase'] = $matrixCommChannelPassPhrase['request_decryption_pass_phrase'];
            $_SESSION['response_encryption_pass_phrase'] = $matrixCommChannelPassPhrase['response_encryption_pass_phrase'];

            // $matrixDatabaseConfiguration = $this->iRememberProperties['database-configuration'];
            // $_SESSION['matrix_database_name'] = $matrixDatabaseConfiguration['matrix_database_name'];

            $this->logger->debug(' completed method Authenticator::initializeSession()', self::MASK_LOG_TRUE);
        } catch (Exception $e) {

            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . var_export($e, true) . "\n");
        }
    }

    private function getJWT($payload)
    {
        try {
            $this->logger->debug(' into method Authenticator::getJWT()', self::MASK_LOG_TRUE);
            // ***** described in https://dev.to/robdwaller/how-to-create-a-json-web-token-using-php-3gml

            // Generate a token
            //$token = Token::getToken('userIdentifier', 'secret', 'tokenExpiryDateTimeString', 'issuerIdentifier');

            // Validate the token
            //$result = Token::validate($token, 'secret');

            // Create token header as a JSON string
            $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

            // Create token payload as a JSON string
            //$payload = json_encode(['user_id' => 123,'sample_resp_attr' => sampleValue]);
            $payload = json_encode($payload);

            // Encode Header to Base64Url String
            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

            // Encode Payload to Base64Url String
            $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

            // Create Signature Hash
            $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'my-256-bit-secret-abC123!', true);

            // Encode Signature to Base64Url String
            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

            // Create JWT
            $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

            $this->logger->debug(' completed method Authenticator::getJWT()', self::MASK_LOG_TRUE);

            return $jwt;
        } catch (Exception $e) {

            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . var_export($e, true) . "\n");
        }
    }

    private function getJwtSecretKey()
    {
        try {
            $this->logger->debug(' into method Authenticator::getJwtSecretKey()', self::MASK_LOG_TRUE);
            //defining a list of prime numbers for encryption
            $primes = array(
                "1009", "1013",
                "1019", "1021", "1031", "1033", "1039", "1049", "1051", "1061", "1063", "1069",
                "1087", "1091", "1093", "1097", "1103", "1109", "1117", "1123", "1129", "1151",
                "1153", "1163", "1171", "1181", "1187", "1193", "1201", "1213", "1217", "1223",
                "1229", "1231", "1237", "1249", "1259", "1277", "1279", "1283", "1289", "1291",
                "1297", "1301", "1303", "1307", "1319", "1321", "1327", "1361", "1367", "1373",
                "1381", "1399", "1409", "1423", "1427", "1429", "1433", "1439", "1447", "1451",
                "1453", "1459", "1471", "1481", "1483", "1487", "1489", "1493", "1499", "1511",
                "1523", "1531", "1543", "1549", "1553", "1559", "1567", "1571", "1579", "1583",
                "1597", "1601", "1607", "1609", "1613", "1619", "1621", "1627", "1637", "1657",
                "1663", "1667", "1669", "1693", "1697", "1699", "1709", "1721", "1723", "1733",
                "1741", "1747", "1753", "1759", "1777", "1783", "1787", "1789", "1801", "1811",
                "1823", "1831", "1847", "1861", "1867", "1871", "1873", "1877", "1879", "1889",
                "1901", "1907", "1913", "1931", "1933", "1949", "1951", "1973", "1979", "1987",
                "1993", "1997", "1999", "2003", "2011", "2017", "2027", "2029", "2039", "2053",
                "2063", "2069", "2081", "2083", "2087", "2089", "2099", "2111", "2113", "2129",
                "2131", "2137", "2141", "2143", "2153", "2161", "2179", "2203", "2207", "2213",
                "2221", "2237", "2239", "2243", "2251", "2267", "2269", "2273", "2281", "2287",
                "2293", "2297", "2309", "2311", "2333", "2339", "2341", "2347", "2351", "2357",
                "2371", "2377", "2381", "2383", "2389", "2393", "2399", "2411", "2417", "2423",
                "2437", "2441", "2447", "2459", "2467", "2473", "2477", "2503", "2521", "2531",
                "2539", "2543", "2549", "2551", "2557", "2579", "2591", "2593", "2609", "2617",
                "2621", "2633", "2647", "2657", "2659", "2663", "2671", "2677", "2683", "2687",
                "2689", "2693", "2699", "2707", "2711", "2713", "2719", "2729", "2731", "2741",
                "2749", "2753", "2767", "2777", "2789", "2791", "2797", "2801", "2803", "2819",
                "2833", "2837", "2843", "2851", "2857", "2861", "2879", "2887", "2897", "2903",
                "2909", "2917", "2927", "2939", "2953", "2957", "2963", "2969", "2971", "2999",
                "3001", "3011", "3019", "3023", "3037", "3041", "3049", "3061", "3067", "3079",
                "3083", "3089", "3109", "3119", "3121", "3137", "3163", "3167", "3169", "3181",
                "3187", "3191", "3203", "3209", "3217", "3221", "3229", "3251", "3253", "3257",
                "3259", "3271", "3299", "3301", "3307", "3313", "3319", "3323", "3329", "3331",
                "3343", "3347", "3359", "3361", "3371", "3373", "3389", "3391", "3407", "3413",
                "3433", "3449", "3457", "3461", "3463", "3467", "3469", "3491", "3499", "3511",
                "3517", "3527", "3529", "3533", "3539", "3541", "3547", "3557", "3559", "3571",
                "3581", "3583", "3593", "3607", "3613", "3617", "3623", "3631", "3637", "3643",
                "3659", "3671", "3673", "3677", "3691", "3697", "3701", "3709", "3719", "3727",
                "3733", "3739", "3761", "3767", "3769", "3779", "3793", "3797", "3803", "3821",
                "3823", "3833", "3847", "3851", "3853", "3863", "3877", "3881", "3889", "3907",
                "3911", "3917", "3919", "3923", "3929", "3931", "3943", "3947", "3967", "3989",
                "4001", "4003", "4007", "4013", "4019", "4021", "4027", "4049", "4051", "4057",
                "4073", "4079", "4091", "4093", "4099", "4111", "4127", "4129", "4133", "4139",
                "4153", "4157", "4159", "4177", "4201", "4211", "4217", "4219", "4229", "4231",
                "4241", "4243", "4253", "4259", "4261", "4271", "4273", "4283", "4289", "4297",
                "4327", "4337", "4339", "4349", "4357", "4363", "4373", "4391", "4397", "4409",
                "4421", "4423", "4441", "4447", "4451", "4457", "4463", "4481", "4483", "4493",
                "4507", "4513", "4517", "4519", "4523", "4547", "4549", "4561", "4567", "4583",
                "4591", "4597", "4603", "4621", "4637", "4639", "4643", "4649", "4651", "4657",
                "4663", "4673", "4679", "4691", "4703", "4721", "4723", "4729", "4733", "4751",
                "4759", "4783", "4787", "4789", "4793", "4799", "4801", "4813", "4817", "4831",
                "4861", "4871", "4877", "4889", "4903", "4909", "4919", "4931", "4933", "4937",
                "4943", "4951", "4957", "4967", "4969", "4973", "4987", "4993", "4999", "5003",
                "5009", "5011", "5021", "5023", "5039", "5051", "5059", "5077", "5081", "5087",
                "5099", "5101", "5107", "5113", "5119", "5147", "5153", "5167", "5171", "5179",
                "5189", "5197", "5209", "5227", "5231", "5233", "5237", "5261", "5273", "5279",
                "5281", "5297", "5303", "5309", "5323", "5333", "5347", "5351", "5381", "5387",
                "5393", "5399", "5407", "5413", "5417", "5419", "5431", "5437", "5441", "5443",
                "5449", "5471", "5477", "5479", "5483", "5501", "5503", "5507", "5519", "5521",
                "5527", "5531", "5557", "5563", "5569", "5573", "5581", "5591", "5623", "5639",
                "5641", "5647", "5651", "5653", "5657", "5659", "5669", "5683", "5689", "5693",
                "5701", "5711", "5717", "5737", "5741", "5743", "5749", "5779", "5783", "5791",
                "5801", "5807", "5813", "5821", "5827", "5839", "5843", "5849", "5851", "5857",
                "5861", "5867", "5869", "5879", "5881", "5897", "5903", "5923", "5927", "5939",
                "5953", "5981", "5987", "6007", "6011", "6029", "6037", "6043", "6047", "6053",
                "6067", "6073", "6079", "6089", "6091", "6101", "6113", "6121", "6131", "6133",
                "6143", "6151", "6163", "6173", "6197", "6199", "6203", "6211", "6217", "6221",
                "6229", "6247", "6257", "6263", "6269", "6271", "6277", "6287", "6299", "6301",
                "6311", "6317", "6323", "6329", "6337", "6343", "6353", "6359", "6361", "6367",
                "6373", "6379", "6389", "6397", "6421", "6427", "6449", "6451", "6469", "6473",
                "6481", "6491", "6521", "6529", "6547", "6551", "6553", "6563", "6569", "6571",
                "6577", "6581", "6599", "6607", "6619", "6637", "6653", "6659", "6661", "6673",
                "6679", "6689", "6691", "6701", "6703", "6709", "6719", "6733", "6737", "6761",
                "6763", "6779", "6781", "6791", "6793", "6803", "6823", "6827", "6829", "6833",
                "6841", "6857", "6863", "6869", "6871", "6883", "6899", "6907", "6911", "6917",
                "6947", "6949", "6959", "6961", "6967", "6971", "6977", "6983", "6991", "6997",
                "7001", "7013", "7019", "7027", "7039", "7043", "7057", "7069", "7079", "7103",
                "7109", "7121", "7127", "7129", "7151", "7159", "7177", "7187", "7193", "7207",
                "7211", "7213", "7219", "7229", "7237", "7243", "7247", "7253", "7283", "7297",
                "7307", "7309", "7321", "7331", "7333", "7349", "7351", "7369", "7393", "7411",
                "7417", "7433", "7451", "7457", "7459", "7477", "7481", "7487", "7489", "7499",
                "7507", "7517", "7523", "7529", "7537", "7541", "7547", "7549", "7559", "7561",
                "7573", "7577", "7583", "7589", "7591", "7603", "7607", "7621", "7639", "7643",
                "7649", "7669", "7673", "7681", "7687", "7691", "7699", "7703", "7717", "7723",
                "7727", "7741", "7753", "7757", "7759", "7789", "7793", "7817", "7823", "7829",
                "7841", "7853", "7867", "7873", "7877", "7879", "7883", "7901", "7907", "7919",
            );

            //randomize the array order
            shuffle($primes);

            //get current day count of the year
            $currentDayCount = date('z') + 1;

            //get a random number
            $randomNumber = rand(1, 365);

            //get integer fragment of the random number
            $randomInteger = intVal($randomNumber);

            //create a secret number with the obtained random values
            $secretNumber = $primes[$currentDayCount] * $primes[$randomInteger] * $primes[$randomInteger + 365];
            $secretHex = dechex($secretNumber * intVal(rand(999, 9999)));
            $secrectRandomString = substr(md5(rand()), 0, 17);
            $secretText = str_shuffle((string)$secretHex . strtoupper($secrectRandomString) . (string)$secretNumber . "!@#$%^&*()");

            $this->logger->debug(' completed method Authenticator::getJwtSecretKey()', self::MASK_LOG_TRUE);

            return $secretText;
        } catch (Exception $e) {

            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . var_export($e, true) . "\n");
        }
    }

    /** 
     * Get hearder Authorization
     * */
    public function getAuthorizationHeader()
    {
        try {
            $this->logger->debug(' into method Authenticator::getAuthorizationHeader()', self::MASK_LOG_TRUE);
            $headers = null;
            if (isset($_SERVER['Authorization'])) {
                $headers = trim($_SERVER["Authorization"]);
            } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
                $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
            } elseif (function_exists('apache_request_headers')) {
                $requestHeaders = apache_request_headers();
                // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
                $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
                //print_r($requestHeaders);
                if (isset($requestHeaders['Authorization'])) {
                    $headers = trim($requestHeaders['Authorization']);
                }
            }
            $this->logger->debug(' completed method Authenticator::getAuthorizationHeader()', self::MASK_LOG_TRUE);
            return $headers;
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . var_export($e, true) . "\n");
        }
    }

    /**
     * get access token from header
     * */
    public function getBearerToken()
    {
        try {
            $this->logger->debug(' completed method Authenticator::getBearerToken()', self::MASK_LOG_TRUE);
            $headers = $this->getAuthorizationHeader();
            // HEADER: Get the access token from the header
            if (!empty($headers)) {
                if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                    return $matches[1];
                }
            }
            $this->logger->debug(' completed method Authenticator::getBearerToken()', self::MASK_LOG_TRUE);
            return null;
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . var_export($e, true) . "\n");
        }
    }

    private function getRequestBody()
    {
        try {
            //compiler gets here only if the  request is from a valid origin
            //get the request body to extract the parameters posted to the request
            if ($this->login_pay_load === null) {
                $this->login_pay_load = file_get_contents('php://input');
            }
            $this->logger->debug(' $json>>>' . $this->login_pay_load, self::MASK_LOG_TRUE);
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . var_export($e, true) . "\n");
        } finally {
            return $this->login_pay_load;
        }
    }
}
