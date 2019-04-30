<?php namespace MatrixAgentsAPI\Modules\IRemNotes;

use MatrixAgentsAPI\Utilities\EventLogger;
use MatrixAgentsAPI\Security\Models\MatrixRegistrationResponseModel;
use MatrixAgentsAPI\DatabaseModel\DBConstants;
use MatrixAgentsAPI\Modules\Login\Model\LoginResponseModel;
use MatrixAgentsAPI\Modules\Login\Model\LoginUserRecord;
use MatrixAgentsAPI\Security\JWT\Token;
use MatrixAgentsAPI\Shared\Models\MatrixGenericResponseModel;

class IRemNotesTableTransactions
{

    private $logger;
    private $dbConnection;
    private $iRememberProperties = null;
    private $userName;
    private $password;
    private $serverName;
    private $dbName;

    private $constStatusFlags = DBConstants::StatusFlags;
    private $constResponseCode = DBConstants::ResponseCode;
    private $constDisplayMessages = DBConstants::DisplayMessages;

    const MASK_LOG_TRUE = false;

    public function __construct()
    {
        $this->logger = new EventLogger();
    }

    public function getIRememberProperties()
    {
        if ($this->iRememberProperties) {
            //get properties as a section segrated array
            $PROCESS_SECTIONS = true;
            $this->iRememberProperties = parse_ini_file(realpath('../../i-remember-properties.ini'), $PROCESS_SECTIONS);
        }
        return $this->iRememberProperties;
    }

    public function setIRememberProperties($_iRememberProperties)
    {
        $this->iRememberProperties = $_iRememberProperties;
        return $this;
    }

    private function getIRememberDBProperties()
    {
        $dbConfig = null;
        try {
            $this->logger->debug('UserTableTransactions >>> into getIRememberDBProperties method ', self::MASK_LOG_TRUE);
            $iremProps = $this->getIRememberProperties();
            $dbConfig = $iremProps['database-configuration'];
            $this->serverName = $dbConfig['iRemember_servername'];
            $this->dbName = $dbConfig['iRemember_db'];
            $this->userName = $dbConfig['iRemember_nrml_user'];
            $this->password = $dbConfig['iRemember_nrml_user_password'];
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . var_export($e, true) . "\n");
        } finally {
            $this->logger->debug('UserTableTransactions >>> out of getIRememberDBProperties method ', self::MASK_LOG_TRUE);
            return $dbConfig;
        }
    }

    public function updateNote($request, $headers)
    {
        $this->logger->debug('IRemNotesTableTransactions >>> into method updateNote ',  self::MASK_LOG_TRUE);
        $updateNoteResponse = new MatrixGenericResponseModel();
        try {
            $updateNoteResponse->setStatus($this->constStatusFlags['Failure'])
                ->setDisplayMessage($this->constDisplayMessages['TemporaryServiceDownMessage'])
                ->setErrorMessage($this->constDisplayMessages['TemporaryServiceDownMessage'])
                ->setResponseCode($this->constResponseCode["RandomError"]);

            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 1 ',  self::MASK_LOG_TRUE);

            $bearerToken = $this->getBearerToken();
            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> bearer token >>> ' . $bearerToken,  self::MASK_LOG_TRUE);

            $token = new Token();
            $payloadInToken = $token->getPayload($bearerToken);
            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> payloadInToken >>> ' . $payloadInToken,  self::MASK_LOG_TRUE);

            $payloadJSON = json_decode($payloadInToken, true);
            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> payloadJSON >>> ' . var_export($payloadJSON, true),  self::MASK_LOG_TRUE);

            $userId = $payloadJSON['user_id'];
            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> userId >>> ' . $userId,  self::MASK_LOG_TRUE);

            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 3 ',  self::MASK_LOG_TRUE);
            // $transactionToken = new Token($request)
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('IRemNotesTableTransactions >>> updateNote >>> Caught exception: ' . var_export($e, true) . "\n");
            $updateNoteResponse->setStatus($this->constStatusFlags['Failure'])
                ->setDisplayMessage($this->constDisplayMessages['TemporaryServiceDownMessage'])
                ->setErrorMessage(var_export($e, true))
                ->setResponseCode($this->constResponseCode["CodeError"]);
        } finally {
            $this->logger->debug('IRemNotesTableTransactions >>> out of updateNote method ', self::MASK_LOG_TRUE);
            return $updateNoteResponse;
        }
    }

    /** 
     * Get header Authorization
     * */
    function getAuthorizationHeader()
    {
        $headers = null;
        try {
            $this->logger->debug('IRemNotesTableTransactions >>> into method getAuthorizationHeader ',  self::MASK_LOG_TRUE);
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
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('IRemNotesTableTransactions >>> getAuthorizationHeader >>> Caught exception: ' . var_export($e, true) . "\n");
        } finally {
            $this->logger->debug('IRemNotesTableTransactions >>> out of getAuthorizationHeader method ', self::MASK_LOG_TRUE);
            return $headers;
        }
    }

    /**
     * get access token from header
     * */
    function getBearerToken()
    {
        $bearerToken = null;
        try {
            $this->logger->debug('IRemNotesTableTransactions >>> into method getBearerToken ',  self::MASK_LOG_TRUE);
            $headers = $this->getAuthorizationHeader();
            $this->logger->debug('IRemNotesTableTransactions >>> getBearerToken >>> authorizationHeader is >>> ' . var_export($headers, true),  self::MASK_LOG_TRUE);
            // HEADER: Get the access token from the header
            if (!empty($headers)) {
                if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                    $bearerToken = $matches[1];
                }
            }
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('IRemNotesTableTransactions >>> getBearerToken >>> Caught exception: ' . var_export($e, true) . "\n");
        } finally {
            $this->logger->debug('IRemNotesTableTransactions >>> out of getBearerToken method ', self::MASK_LOG_TRUE);
            return $bearerToken;
        }
    }
}
