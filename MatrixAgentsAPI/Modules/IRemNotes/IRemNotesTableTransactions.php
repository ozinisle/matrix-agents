<?php namespace MatrixAgentsAPI\Modules\IRemNotes;

use MatrixAgentsAPI\Utilities\EventLogger;
use MatrixAgentsAPI\DatabaseModel\DBConstants;
use MatrixAgentsAPI\Security\JWT\Token;

use MatrixAgentsAPI\Modules\IRemNotes\Model\IRemNoteItem;

use MatrixAgentsAPI\Modules\Login\Model\GenericTableTransactionResponseModel;
use MatrixAgentsAPI\Modules\IRemNotes\Model\IRemNoteItemCategory;

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

    public function updateNote($noteData, $headers)
    {
        $this->logger->debug('IRemNotesTableTransactions >>> into method updateNote ',  self::MASK_LOG_TRUE);
        $methodExectionComplete = false;

        $updateNoteResponse = new GenericTableTransactionResponseModel();

        $bearerToken = null;
        $token = null;
        $payloadInToken = null;
        $payloadJSON = null;
        $loggedInUserId = null;

        $dbConfig = null;
        $serverName = null;
        $dbName = "techdotm_iremakoz";
        $dbUser = "techdotm_iRemNRMLSub";
        $dbPassword = "whsGiF04brLNV10f";
        $conn = null;

        $isNewRecord = false;
        $isFormDataValid = false;

        $formData_noteTitle = null;
        $formData_categoryTags =  null;
        $formData_noteDescription = null;
        $iremNoteItem = new IRemNoteItem();;

        $irem_notes_id = null;
        $irem_notes_title = null;
        $irem_notes_tags = null;
        $irem_notes_description = null;
        $irem_notes_usrid = null;
        $irem_notes_created = null;
        $irem_notes_lastupdated = null;

        $markedForDeletion = false;

        try {
            $updateNoteResponse->setStatus($this->constStatusFlags['Failure'])
                ->setDisplayMessage($this->constDisplayMessages['TemporaryServiceDownMessage'])
                ->setErrorMessage($this->constDisplayMessages['TemporaryServiceDownMessage'])
                ->setResponseCode($this->constResponseCode["RandomError"]);

            $bearerToken = $this->getBearerToken();
            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> bearer token >>> ' . $bearerToken,  self::MASK_LOG_TRUE);

            $token = new Token();
            $payloadInToken = $token->getPayload($bearerToken);
            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> payloadInToken >>> ' . $payloadInToken,  self::MASK_LOG_TRUE);

            $payloadJSON = json_decode($payloadInToken, true);
            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> payloadJSON >>> ' . var_export($payloadJSON, true),  self::MASK_LOG_TRUE);

            $loggedInUserId = $payloadJSON['user_id'];
            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> userId >>> ' . $loggedInUserId,  self::MASK_LOG_TRUE);

            $dbConfig = $this->getIRememberDBProperties();
            $serverName = $dbConfig["iRemember_servername"];

            $conn = mysqli_connect($serverName, $dbUser, $dbPassword, $dbName);
            // Check connection
            if (mysqli_connect_errno()) {
                echo "Failed to connect to MySQL: " . mysqli_connect_error();
            }

            // new row details

            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> request data is >>> ' . var_export($noteData, true),  self::MASK_LOG_TRUE);

            $noteData = (array)$noteData;

            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> $noteData[noteTitle] >>> ' . $noteData['noteTitle'], self::MASK_LOG_TRUE);

            if (isset($noteData['noteId'])) {
                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> noteid is set', self::MASK_LOG_TRUE);
                if ($noteData['noteId'] === null) {
                    $isNewRecord = true;
                } else {
                    $bufferNoteId = mysqli_real_escape_string($conn, $noteData['noteId']);
                    if (!is_string($bufferNoteId)) {
                        $isNewRecord = true;
                    } else if (strlen($bufferNoteId) < 13) {
                        $isNewRecord = true;
                    }
                }
            } else {
                $isNewRecord = true;
            }



            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 1', self::MASK_LOG_TRUE);
            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> sizeof($noteData[categoryTags]) >>> ' . sizeof($noteData['categoryTags']), self::MASK_LOG_TRUE);



            for ($categoryTagEntityItr = 0; $categoryTagEntityItr < sizeof($noteData['categoryTags']); $categoryTagEntityItr++) {
                $categoryTagEntity = $noteData['categoryTags'][$categoryTagEntityItr];

                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> 1.categoryTagItem >>> ' . var_export($categoryTagEntity, true), self::MASK_LOG_TRUE);
                $categoryTagEntityArr = (array)$categoryTagEntity;
                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 1 >>> ', self::MASK_LOG_TRUE);
                $iremNoteItemCategory  = new IRemNoteItemCategory();
                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 2 >>> ', self::MASK_LOG_TRUE);
                if (isset($categoryTagEntityArr['categoryName'])) {
                    $categoryName = mysqli_real_escape_string($conn, $categoryTagEntityArr['categoryName']);
                    $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> $categoryName >>> ' . $categoryName, self::MASK_LOG_TRUE);
                    $formData_categoryTags .= "$categoryName, ";
                    $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> $formData_categoryTags>>> ' . $formData_categoryTags, self::MASK_LOG_TRUE);
                }
            }

            $formData_noteTitle = $noteData['noteTitle'] ?: null;
            $formData_noteDescription = $noteData['noteDescription'] ?: null;

            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 2', self::MASK_LOG_TRUE);
            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> $formData_categoryTags >>>     ' . $formData_categoryTags, self::MASK_LOG_TRUE);

            $formData_categoryTags = substr($formData_categoryTags, 0, strlen($formData_categoryTags) - 2);

            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> typeof $loggedInUserId >>> ' . gettype($loggedInUserId), self::MASK_LOG_TRUE);
            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> typeof $formData_noteTitle >>> ' . gettype($formData_noteTitle), self::MASK_LOG_TRUE);
            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> typeof $formData_noteDescription >>> ' . gettype($formData_noteDescription), self::MASK_LOG_TRUE);

            if (is_string($loggedInUserId) && is_string($formData_noteTitle)  && is_string($formData_noteDescription)) {
                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 2.0', self::MASK_LOG_TRUE);
                $formData_noteTitle = trim($formData_noteTitle, " ");
                $formData_noteDescription = trim($formData_noteDescription, " ");
                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 2.0.1', self::MASK_LOG_TRUE);

                if (strlen($formData_noteTitle) >= 1 && strlen($formData_categoryTags) >= 1 && strlen($formData_noteDescription) >= 1) {
                    $isFormDataValid = true;
                    $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 2.1', self::MASK_LOG_TRUE);
                } else {
                    $updateNoteResponse->setStatus($this->constStatusFlags['Failure'])
                        ->setDisplayMessage($this->constDisplayMessages['TemporaryServiceDownMessage'])
                        ->setErrorMessage(var_export($e, true))
                        ->setResponseCode($this->constResponseCode["CodeError"]);
                    $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 2.2', self::MASK_LOG_TRUE);
                }
            } else {
                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 2.3', self::MASK_LOG_TRUE);
            }
            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 3', self::MASK_LOG_TRUE);
            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>>  $isFormDataValid >>> ' .
                json_encode($isFormDataValid),  self::MASK_LOG_TRUE);

            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>>  check 3.1',  self::MASK_LOG_TRUE);

            if ($isFormDataValid) {
                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>>  check 3.5',  self::MASK_LOG_TRUE);
                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>>  $isNewRecord >>> ' .
                    $isNewRecord ? 'true' : 'false',  self::MASK_LOG_TRUE);
                if ($isNewRecord === true) {
                    $irem_notes_id = uniqid();
                    $irem_notes_title =  $formData_noteTitle;
                    $irem_notes_tags = $formData_categoryTags;
                    $irem_notes_description =  $formData_noteDescription;
                    $irem_notes_usrid = $loggedInUserId;
                    $irem_notes_created = date('Y-m-d h:m:s');
                    $irem_notes_lastupdated = date('Y-m-d h:m:s');

                    $markedForDeletion = false;
                    if (isset($noteData['markedForDeletion'])) {
                        if ($noteData['markedForDeletion'] === "true" || $noteData['markedForDeletion'] === "false") {
                            $markedForDeletion = $noteData['markedForDeletion'] == "true" ? true : false;
                        } else {
                            $markedForDeletion = false;
                        }
                    }

                    $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> query is >>> ' . "INSERT INTO irem_notes (irem_notes_id,irem_notes_title,irem_notes_tags,irem_notes_description,irem_notes_usrid,irem_notes_created,irem_notes_lastupdated,irem_notes_ismarkedfordeletion) VALUES ('$irem_notes_id','$irem_notes_title','$irem_notes_tags','$irem_notes_description','$irem_notes_usrid','$irem_notes_created','$irem_notes_lastupdated','$markedForDeletion')",  self::MASK_LOG_TRUE);

                    // mysqli_query($conn, "INSERT INTO irem_forgot_password (irem_fp_index, irem_fp_username, irem_fp_accesskey,irem_fp_date)
                    // VALUES ('$irem_fp_index','$irem_fp_username','$irem_fp_accesskey','$irem_fp_date')");
                    mysqli_query($conn, "INSERT INTO irem_notes (irem_notes_id,irem_notes_title,irem_notes_tags,irem_notes_description,irem_notes_usrid,irem_notes_created,irem_notes_lastupdated,irem_notes_ismarkedfordeletion) VALUES ('$irem_notes_id','$irem_notes_title','$irem_notes_tags','$irem_notes_description','$irem_notes_usrid','$irem_notes_created','$irem_notes_lastupdated','$markedForDeletion')");

                    $iremNoteItemCategoryGroup = [];
                    for ($categoryTagEntityItr = 0; $categoryTagEntityItr < sizeof($noteData['categoryTags']); $categoryTagEntityItr++) {
                        $categoryTagEntity = $noteData['categoryTags'][$categoryTagEntityItr];

                        $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> 2.categoryTagItem >>> ' . var_export($categoryTagEntity, true), self::MASK_LOG_TRUE);
                        $categoryTagEntityArr = (array)$categoryTagEntity;

                        $categoryName = mysqli_real_escape_string($conn, $categoryTagEntityArr['categoryName']);
                        $categoryId = "";
                        if (!isset($categoryTagEntityArr['categoryId'])) {

                            $categoryId = uniqid();
                            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> updating categoryName to table>>> ' . $categoryName, self::MASK_LOG_TRUE);

                            $existingCategoryRecordIfAny = mysqli_query($conn, "SELECT * FROM irem_notes_categories WHERE irem_nc_categoryname='$categoryName' LIMIT 1");
                            if (mysqli_num_rows($existingCategoryRecordIfAny) == 0) {
                                mysqli_query($conn, "INSERT INTO irem_notes_categories (irem_nc_categoryid,irem_nc_categoryname,irem_nc_ismarkedfordeletion) VALUES ('$categoryId','$categoryName','false')");
                            }
                        } else {
                            $categoryId = $categoryTagEntityArr['categoryId'];
                        }

                        $iremNoteItemCategory->setCategoryName($categoryName);
                        $iremNoteItemCategory->setCategoryId($categoryId);
                        if (isset($categoryTagEntityArr['markedForDeletion'])) {
                            $iremNoteItemCategory->setMarkedForDeletion($categoryTagEntityArr['markedForDeletion']);
                        } else {
                            $iremNoteItemCategory->setMarkedForDeletion(false);
                        }

                        array_push($iremNoteItemCategoryGroup, $iremNoteItemCategory);
                    }

                    $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> $iremNoteItemCategoryGroup >>> ' . var_export($iremNoteItemCategoryGroup, true),  self::MASK_LOG_TRUE);

                    mysqli_close($conn);

                    $updateNoteResponse->setStatus($this->constStatusFlags['Success'])
                        ->setDisplayMessage('Note has been saved')
                        ->setErrorMessage('')
                        ->setResponseCode($this->constResponseCode["NewNoteCreatedSuccess"]);
                } else {
                    $updateNoteResponse->setStatus($this->constStatusFlags['Failure'])
                        ->setDisplayMessage($this->constDisplayMessages['InvalidInputData'])
                        ->setErrorMessage($this->constDisplayMessages['InvalidInputData'])
                        ->setResponseCode($this->constResponseCode['InvalidInputData']);
                }
            } else {
                $updateNoteResponse->setStatus($this->constStatusFlags['Failure'])
                    ->setDisplayMessage($this->constDisplayMessages['InvalidInputData'])
                    ->setErrorMessage($this->constDisplayMessages['InvalidInputData'])
                    ->setResponseCode($this->constResponseCode['InvalidInputData']);
            }
            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> about to construct response if success response',  self::MASK_LOG_TRUE);

            if ($updateNoteResponse->getStatus() === $this->constStatusFlags['Success']) {
                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> constructing response',  self::MASK_LOG_TRUE);
                $iremNoteItem->setCategoryTags($iremNoteItemCategoryGroup);
                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 3.5.0',  self::MASK_LOG_TRUE);
                $iremNoteItem->setNoteId($irem_notes_id);
                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 3.5.1',  self::MASK_LOG_TRUE);
                $iremNoteItem->setNoteTitle($irem_notes_title);
                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 3.5.2',  self::MASK_LOG_TRUE);
                $iremNoteItem->setNoteDescription($irem_notes_description);
                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 3.5.3',  self::MASK_LOG_TRUE);
                $iremNoteItem->setUserId($irem_notes_usrid);
                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 3.5.4',  self::MASK_LOG_TRUE);
                $iremNoteItem->setCreated($irem_notes_created);
                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 3.5.5',  self::MASK_LOG_TRUE);
                $iremNoteItem->setLastUpdated($irem_notes_lastupdated);
                $iremNoteItem->setMarkedForDeletion($markedForDeletion);

                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> $iremNoteItem is ' . var_export($iremNoteItem, true),  self::MASK_LOG_TRUE);

                $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> $iremNoteItem json is ' . var_export($iremNoteItem->getJson(), true),  self::MASK_LOG_TRUE);

                $updateNoteResponse->setMatchingRecords($iremNoteItem->getJson());
            }

            $this->logger->debug('IRemNotesTableTransactions >>> updateNote >>> check 4 ',  self::MASK_LOG_TRUE);
            $methodExectionComplete = true;
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
            if ($methodExectionComplete) {
                return $updateNoteResponse;
            } else {
                $updateNoteResponse->setStatus($this->constStatusFlags['Failure'])
                    ->setDisplayMessage($this->constDisplayMessages['TemporaryServiceDownMessage'])
                    ->setErrorMessage($this->constDisplayMessages['SilentFailure'])
                    ->setResponseCode($this->constResponseCode["SilentFailure"]);
                return  $updateNoteResponse;
            }
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
