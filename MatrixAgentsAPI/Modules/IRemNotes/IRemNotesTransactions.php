<?php namespace MatrixAgentsAPI\Modules\IRemNotes;

use MatrixAgentsAPI\DatabaseModel\DBConstants;
use MatrixAgentsAPI\Utilities\EventLogger;
use MatrixAgentsAPI\Security\Encryption\OpenSSLEncryption;
use MatrixAgentsAPI\Shared\Models\MatrixGenericResponseModel;

class IRemNotesTransactions
{
    private $logger;
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

    public function updateNote(): string
    {

        //create the response object
        $updateResponse = new MatrixGenericResponseModel();

        $updateResponse->setStatus($this->constStatusFlags['Failure'])
            ->setDisplayMessage($this->constDisplayMessages['TemporaryServiceDownMessage'])
            ->setErrorMessage($this->constDisplayMessages['TemporaryServiceDownMessage'])
            ->setResponseCode($this->constResponseCode["RandomError"]);

        try {
            $this->logger->debug('IRemNotesTransactions >>> into method updateNote >>> ', self::MASK_LOG_TRUE);

            //Start :: gather data relevant to the login attempt
            //initialize session
            $this->initializeSession();

            //compiler gets here only if the  request is from a valid origin
            //get the request body to extract the parameters posted to the request
            $request_body = $this->getRequestBody();
            $request_headers = $this->getRequestHeaders();

            $decryptedUpdateNoteRequest = $this->opensslEncryption->CryptoJSAesDecrypt($_SESSION['request_decryption_pass_phrase'], $request_body);

            // $this->logger->debug('IRemNotesTransactions >>> decryptedUpdateNoteRequest >>> ' . var_export($decryptedUpdateNoteRequest, true), self::MASK_LOG_TRUE);
            // $this->logger->debug('IRemNotesTransactions >>> request_headers >>> ' . var_export($request_headers, true), self::MASK_LOG_TRUE);


            if (empty($decryptedUpdateNoteRequest)) {
                $this->logger
                    ->errorEvent()
                    ->log($this->constDisplayMessages['InvalidRequest']);
                return $updateResponse->setStatus($this->constStatusFlags['Failure'])
                    ->setDisplayMessage($this->constDisplayMessages['InvalidRequest'])
                    ->setErrorMessage($this->constDisplayMessages['InvalidRequest'])
                    ->setResponseCode($this->constResponseCode['InvalidRequest']);
            }

            $this->logger->debug('IRemNotesTransactions >>> about to create IRemNotesTableTransactions', self::MASK_LOG_TRUE);
            $notesTbl = new IRemNotesTableTransactions();

            $this->logger->debug('IRemNotesTransactions >>> about to call setIRememberProperties', self::MASK_LOG_TRUE);
            $notesTbl->setIRememberProperties($this->iRememberProperties);

            $this->logger->debug('IRemNotesTransactions >>> about to call $notesTbl->updateNote', self::MASK_LOG_TRUE);
            $updateResponse = $notesTbl->updateNote($decryptedUpdateNoteRequest, $request_headers);
        } catch (Exception $e) {

            $updateResponse->setStatus($this->constStatusFlags['Failure'])
                ->setDisplayMessage($this->constDisplayMessages['TemporaryServiceDownMessage'])
                ->setErrorMessage(var_export($e, true))
                ->setResponseCode($this->constResponseCode['RegistrationFailure']);

            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . var_export($e, true) . "\n");
        } finally {
            $this->logger->debug(' executing finally block in IRemNotesTransactions >>> updateNote() method', self::MASK_LOG_TRUE);

            return $this->getEncryptedResponse($updateResponse->getJsonString());
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

    private function initializeSession()
    {

        try {
            $this->logger->debug(' into method IRemNotesTransactions::initializeSession()', self::MASK_LOG_TRUE);

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

            $this->logger->debug(' completed method IRemNotesTransactions::initializeSession()', self::MASK_LOG_TRUE);
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
                // $this->logger->debug('IRemNotesTransactions >>>  getRequestBody >>> check 1 >>> ', self::MASK_LOG_TRUE);
                // $this->logger->debug('IRemNotesTransactions >>>  request_method >>> ' . $_SERVER["REQUEST_METHOD"] . ' >>> CONTENT_TYPE >>> ' . $_SERVER["CONTENT_TYPE"], self::MASK_LOG_TRUE);
                // $this->logger->debug('IRemNotesTransactions >>>  getRequestBody >>> CONTENT_LENGTH >>> ' . $_SERVER["CONTENT_LENGTH"], self::MASK_LOG_TRUE);
                // if (
                //     $_SERVER["REQUEST_METHOD"] == "POST" && ($_SERVER["CONTENT_TYPE"] == "application/json" || $_SERVER["CONTENT_TYPE"] == "application/json; charset=UTF-8")
                // ) {
                //     $this->logger->debug('IRemNotesTransactions >>>  getRequestBody >>> check 2 >>> ', self::MASK_LOG_TRUE);
                //     $this->login_pay_load = file_get_contents("php://input", false, stream_context_get_default(), 0, $_SERVER["CONTENT_LENGTH"]);

                //     // $this->login_pay_load = json_decode($_REQUEST["JSON_RAW"], true);

                //     // // merge JSON-Content to $_REQUEST 
                //     // if (is_array($this->login_pay_load)) $_REQUEST   =  $this->login_pay_load + $_REQUEST;
                //     $this->logger->debug('IRemNotesTransactions >>>  getRequestBody >>> check 3 >>> ', self::MASK_LOG_TRUE);
                // }
                $this->login_pay_load = file_get_contents('php://input');
            }
            $this->logger->debug('IRemNotesTransactions >>>  getRequestBody >>> login_pay_load >>> ' . $this->login_pay_load, self::MASK_LOG_TRUE);
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . var_export($e, true) . "\n");
        } finally {
            return $this->login_pay_load;
        }
    }

    private function  getRequestHeaders()
    {
        $headers = array();
        try {

            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) <> 'HTTP_') {
                    continue;
                }
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }

            $this->logger->debug(' $headers >>>' . var_export($headers, true), self::MASK_LOG_TRUE);
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . var_export($e, true) . "\n");
        } finally {
            return $headers;
        }
    }
}
