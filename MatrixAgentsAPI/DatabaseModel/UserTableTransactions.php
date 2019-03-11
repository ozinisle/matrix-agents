<?php namespace MatrixAgentsAPI\DatabaseModel;

use MatrixAgentsAPI\Utilities\EventLogger;

class UserTableTransactions
{

    private $logger;
    private $dbConnection;
    private $iRememberProperties = null;
    private $userName;
    private $password;
    private $serverName;
    private $dbName;

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
            $this->iRememberProperties = parse_ini_file(realpath('../i-remember-properties.ini'), $PROCESS_SECTIONS);
        }
        return $this->iRememberProperties;
    }

    public function setIRememberProperties($_iRememberProperties)
    {
        $this->iRememberProperties = $_iRememberProperties;
        return $this;
    }

    private function readIRememberPropertiesData()
    {
        $iremProps = $this->getIRememberProperties();
        $dbConfig = $iremProps['database-configuration'];
        $this->serverName = $dbConfig['iRemember_servername'];
        $this->dbName = $dbConfig['iRemember_db'];
        $this->userName = $dbConfig['iRemember_nrml_user'];
        $this->password = $dbConfig['iRemember_nrml_user_password'];
    }

    private function connect()
    {
        try {
            $this->logger->debug('UserTableTransactions >>> into create connection method ', self::MASK_LOG_TRUE);

            $this->dbConnection = new PDO(
                "mysql:host=$this->serverName;dbname=$this->dbName",
                $this->userName,
                $this->password
            );


        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . $e->getMessage() . "\n");
        }
        finally {
            $this->logger->debug('UserTableTransactions >>> out of create connection method', self::MASK_LOG_TRUE);
        }
    }

    private function disConnect()
    {
        $this->dbConnection = null;
    }

    public function addUser($username, $password, $userrole)
    {
        try {
            $this->logger->debug('UserTableTransactions >>> into of addUser method', self::MASK_LOG_TRUE);

            $this->connect();

            // prepare sql and bind parameters
            $stmt = $this->dbConnection->prepare("INSERT INTO users 
                (irem_usr_userid, irem_usr_username, irem_usr_password,irem_usr_userrole) 
                VALUES (:irem_usr_userid, :irem_usr_username, :irem_usr_password, :irem_usr_userrole)");
            $stmt->bindParam(':irem_usr_userid', $irem_usr_userid);
            $stmt->bindParam(':irem_usr_username', $irem_usr_username);
            $stmt->bindParam(':irem_usr_password', $irem_usr_password);
            $stmt->bindParam(':irem_usr_userrole', $irem_usr_userrole);

            // insert a row
            $irem_usr_userid = uniqid();
            $irem_usr_username = $username;
            $irem_usr_password = $password;
            $irem_usr_userrole = $userrole;

            $stmt->execute();

        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . $e->getMessage() . "\n");
        }
        finally {
            $this->disConnect();
            $this->logger->debug('UserTableTransactions >>> out of addUser method', self::MASK_LOG_TRUE);
        }
    }
}