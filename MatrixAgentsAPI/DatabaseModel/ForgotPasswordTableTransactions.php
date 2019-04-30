<?php namespace MatrixAgentsAPI\DatabaseModel;

use MatrixAgentsAPI\Utilities\EventLogger;
use MatrixAgentsAPI\Security\Models\MatrixRegistrationResponseModel;
use MatrixAgentsAPI\DatabaseModel\DBConstants;
use MatrixAgentsAPI\Modules\Login\Model\GenericTableTransactionResponseModel;


class ForgotPasswordTableTransactions
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
            $this->logger->debug('ForgotPasswordTableTransactions >>> into getIRememberDBProperties method ', self::MASK_LOG_TRUE);
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
            $this->logger->debug('ForgotPasswordTableTransactions >>> out of getIRememberDBProperties method ', self::MASK_LOG_TRUE);
            return $dbConfig;
        }
    }



    private function disConnect()
    {
        $this->logger->debug('ForgotPasswordTableTransactions >>> into  disConnect method ', self::MASK_LOG_TRUE);

        $this->dbConnection = null;

        $this->logger->debug('ForgotPasswordTableTransactions >>> out of  disConnect method', self::MASK_LOG_TRUE);
    }

    public function checkUserEntry($username, $password)
    {
        $queryResponse = new GenericTableTransactionResponseModel();
        try {
            $this->logger->debug('ForgotPasswordTableTransactions >>> into of getUser method', self::MASK_LOG_TRUE);

            $dbConfig = $this->getIRememberDBProperties();

            $serverName = $dbConfig["iRemember_servername"];
            $dbName = "techdotm_iremakoz";
            $dbUser = "techdotm_iRemNRMLSub";
            $dbPassword = "whsGiF04brLNV10f";

            $conn = mysqli_connect($serverName, $dbUser, $dbPassword, $dbName);
            // Check connection
            if (mysqli_connect_errno()) {
                echo "Failed to connect to MySQL: " . mysqli_connect_error();
            }

            $irem_fp_username = mysqli_real_escape_string($conn, $username);
            $irem_fp_username = filter_var($irem_fp_username, FILTER_SANITIZE_EMAIL);
            $irem_fp_username = filter_var($irem_fp_username, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $this->logger->debug("attempting query >>>> SELECT * FROM irem_forgot_password WHERE irem_fp_username='$irem_fp_username' LIMIT 1", self::MASK_LOG_TRUE);

            // check if user already exists
            $query = mysqli_query($conn, "SELECT * FROM irem_forgot_password WHERE irem_fp_username='$irem_fp_username' LIMIT 1");

            if (!$query) {
                die('Error: ' . mysqli_error($conn));
            }

            if (mysqli_num_rows($query) > 0) {

                $this->logger->debug('found matching rows', self::MASK_LOG_TRUE);

                $userRecord = $query->fetch_assoc();
                //check if passwords are matching
                if (password_verify($password, $userRecord['irem_usr_password'])) {
                    $this->logger->debug('passwords are matching', self::MASK_LOG_TRUE);

                    $userRecordModel = new LoginUserRecord();

                    $this->logger->debug('test1', self::MASK_LOG_TRUE);
                    $userRecordModel->setUserId($userRecord['irem_usr_userid'])
                        ->setUserName($userRecord['irem_usr_username'])
                        ->setUserRole($userRecord['irem_usr_userrole']);

                    // $this->logger->debug('test2 >>>' . DBConstants::StatusFlags->Success, self::MASK_LOG_TRUE);
                    // $this->logger->debug('test2.1 >>>' . $this->constResponseCode['LoginSuccess'], self::MASK_LOG_TRUE);



                    $queryResponse = $queryResponse->setStatus($this->constStatusFlags['Success'])
                        ->setDisplayMessage('')
                        ->setErrorMessage('')
                        ->setResponseCode($this->constResponseCode['LoginSuccess'])
                        ->setUserRecord($userRecordModel);

                    $this->logger->debug('test3 >>>'
                        . var_export($queryResponse, true), self::MASK_LOG_TRUE);
                } else { }
            } else {

                $this->logger->debug('no user found >>> ' . $irem_usr_username, self::MASK_LOG_TRUE);

                $queryResponse = $queryResponse->setStatus($this->constStatusFlags['Failure'])
                    ->setDisplayMessage($this->constDisplayMessages['LoginIncorrectUserNamePassword'])
                    ->setErrorMessage('')
                    ->setResponseCode($this->constResponseCode['LoginFailure']);
            }

            mysqli_close($conn);
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . var_export($e, true) . "\n");
            $queryResponse = $queryResponse->setStatus($this->constStatusFlags['Failure'])
                ->setDisplayMessage($this->constDisplayMessages['TemporaryServiceDownMessage'])
                ->setErrorMessage(var_export($e, true))
                ->setResponseCode($this->constResponseCode['RegistrationFailure']);
        } finally {
            // $this->logger->debug('Authenticator >>> login >>> queryResponse is' . var_export($queryResponse, true), self::MASK_LOG_TRUE);
            $this->logger->debug('ForgotPasswordTableTransactions >>> out of getUser method ', self::MASK_LOG_TRUE);
            return $queryResponse;
        }
    }

    public function isUser($username)
    {
        $queryResponse = new LoginResponseModel();
        try {
            $this->logger->debug('ForgotPasswordTableTransactions >>> into of getUser method', self::MASK_LOG_TRUE);

            $dbConfig = $this->getIRememberDBProperties();

            $serverName = $dbConfig["iRemember_servername"];
            $dbName = "techdotm_iremakoz";
            $dbUser = "techdotm_iRemNRMLSub";
            $dbPassword = "whsGiF04brLNV10f";

            $conn = mysqli_connect($serverName, $dbUser, $dbPassword, $dbName);
            // Check connection
            if (mysqli_connect_errno()) {
                echo "Failed to connect to MySQL: " . mysqli_connect_error();
            }

            $irem_usr_username = mysqli_real_escape_string($conn, $username);
            $irem_usr_username = filter_var($irem_usr_username, FILTER_SANITIZE_EMAIL);
            $irem_usr_username = filter_var($irem_usr_username, FILTER_SANITIZE_FULL_SPECIAL_CHARS);


            $this->logger->debug("attempting query >>>> SELECT * FROM irem_users WHERE irem_usr_username='$irem_usr_username' LIMIT 1", self::MASK_LOG_TRUE);

            // check if user already exists
            $query = mysqli_query($conn, "SELECT * FROM irem_users WHERE irem_usr_username='$irem_usr_username' LIMIT 1");

            if (!$query) {
                die('Error: ' . mysqli_error($conn));
            }

            if (mysqli_num_rows($query) > 0) {

                $this->logger->debug('found matching rows', self::MASK_LOG_TRUE);

                $userRecord = $query->fetch_assoc();

                $userRecordModel = new LoginUserRecord();

                $userRecordModel->setUserId($userRecord['irem_usr_userid'])
                    ->setUserName($userRecord['irem_usr_username'])
                    ->setUserRole($userRecord['irem_usr_userrole']);

                // $this->logger->debug('test2 >>>' . DBConstants::StatusFlags->Success, self::MASK_LOG_TRUE);
                // $this->logger->debug('test2.1 >>>' . $this->constResponseCode['LoginSuccess'], self::MASK_LOG_TRUE);



                $queryResponse = $queryResponse->setStatus($this->constStatusFlags['Success'])
                    ->setDisplayMessage('')
                    ->setErrorMessage('')
                    ->setResponseCode($this->constResponseCode['LoginSuccess'])
                    ->setUserRecord($userRecordModel);
            } else {

                $this->logger->debug('no user found >>> ' . $irem_usr_username, self::MASK_LOG_TRUE);

                $queryResponse = $queryResponse->setStatus($this->constStatusFlags['Failure'])
                    ->setDisplayMessage($this->constDisplayMessages['LoginIncorrectUserNamePassword'])
                    ->setErrorMessage('')
                    ->setResponseCode($this->constResponseCode['LoginFailure']);
            }

            mysqli_close($conn);
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . var_export($e, true) . "\n");
            $queryResponse = $queryResponse->setStatus($this->constStatusFlags['Failure'])
                ->setDisplayMessage($this->constDisplayMessages['TemporaryServiceDownMessage'])
                ->setErrorMessage(var_export($e, true))
                ->setResponseCode($this->constResponseCode['RegistrationFailure']);
        } finally {
            // $this->logger->debug('Authenticator >>> login >>> queryResponse is' . var_export($queryResponse, true), self::MASK_LOG_TRUE);
            $this->logger->debug('ForgotPasswordTableTransactions >>> out of getUser method ', self::MASK_LOG_TRUE);
            return $queryResponse;
        }
    }

    public function addForgotPasswordEntry($username)
    {
        $addForgotPasswordEntryResponse = new GenericTableTransactionResponseModel();
        try {
            $this->logger->debug('ForgotPasswordTableTransactions >>> into of addForgotPasswordEntry method', self::MASK_LOG_TRUE);

            $addForgotPasswordEntryResponse->setStatus('SUCCESS')
                ->setDisplayMessage($this->constDisplayMessages['TemporaryServiceDownMessage'])
                ->setErrorMessage('')
                ->setResponseCode($this->constResponseCode['RegistrationFailure']);

            // $iremProps = $this->getIRememberProperties();
            $dbConfig = $this->getIRememberDBProperties();

            $serverName = $dbConfig["iRemember_servername"];
            $dbName = "techdotm_iremakoz";
            $dbUser = "techdotm_iRemNRMLSub";
            $dbPassword = "whsGiF04brLNV10f";

            $conn = mysqli_connect($serverName, $dbUser, $dbPassword, $dbName);
            // Check connection
            if (mysqli_connect_errno()) {
                echo "Failed to connect to MySQL: " . mysqli_connect_error();
            }

            // new row details
            $irem_fp_index = uniqid();
            $irem_fp_username = mysqli_real_escape_string($conn, $username);
            $irem_fp_username = filter_var($irem_fp_username, FILTER_SANITIZE_EMAIL);
            $irem_fp_username = filter_var($irem_fp_username, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $irem_fp_accesskey = uniqid();
            $irem_fp_date = date('Y-m-d h:m:s');

            $this->logger->debug('ForgotPasswordTableTransactions >>> attempting query >>> ' . "INSERT INTO irem_forgot_password (irem_fp_index, irem_fp_username, irem_fp_accesskey,irem_fp_date)
                VALUES ('$irem_fp_index','$irem_fp_username','$irem_fp_accesskey','$irem_fp_date')", self::MASK_LOG_TRUE);


            mysqli_query($conn, "INSERT INTO irem_forgot_password (irem_fp_index, irem_fp_username, irem_fp_accesskey,irem_fp_date)
                VALUES ('$irem_fp_index','$irem_fp_username','$irem_fp_accesskey','$irem_fp_date')");

            mysqli_close($conn);

            $headers = array(
                "From: $irem_fp_username",
                "Reply-To: $irem_fp_username",
                "X-Mailer: PHP/" . PHP_VERSION
            );
            $headers = implode("\r\n", $headers);
            mail($irem_fp_username, 'reset forgotten password', 'content to help reset forgotten password', $headers);
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . var_export($e, true) . "\n");
            $addForgotPasswordEntryResponse->setStatus('Failure')
                ->setErrorMessage(var_export($e, true))
                ->setResponseCode($this->constResponseCode['RegistrationFailure']);
        } finally {
            $this->disConnect();
            $stmt = null;
            $this->logger->debug('ForgotPasswordTableTransactions >>> out of addForgotPasswordEntry method ', self::MASK_LOG_TRUE);

            return $addForgotPasswordEntryResponse;
        }
    }
}
