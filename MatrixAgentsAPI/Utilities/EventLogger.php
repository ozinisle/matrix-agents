<?php
namespace MatrixAgentsAPI\Utilities;

use phpDocumentor\Reflection\Types\Boolean;


class EventLogger
{
    private $regularInfoLogEvent = true;
    private $debugEvent = false;
    private $securityEvent = false;
    private $errorEvent = false;
    private $warningEvent = false;

    private $appLoggerConfig = null;

    private function getEventLogger() : EventLogger
    {
        return $this;
    }

    public function log(string $logText)
    {
        //write code to log the event
        echo $logText;
    }

    public function debug(string $logText, bool $maskFlag)
    {
        if (!$maskFlag && $_SESSION['debug_mode']) {
            $this->getEventLogger()::debugEvent();
            $this->getEventLogger()::log($logText . PHP_EOL);
        }
    }

    public function getAppLoggerConfig()
    {
        if ($this->appLoggerConfig !== null) {
            return $this->appLoggerConfig;
        } else {
            //get properties as a section segrated array
            $PROCESS_SECTIONS = true;
            //get app properties
            $matrix_agents_properties = parse_ini_file(realpath("../matrix-agents-properties.ini"), $PROCESS_SECTIONS);
            //get the matrix log config info from the properties file
            $this->appLoggerConfig = $matrix_agents_properties["matrix-Log-config"];
        }

        return $this->appLoggerConfig;
    }

    public function setAppLoggerConfig($matrix_agents_properties) : EventLogger
    {
        //get the matrix log config info from the properties file
        $this->appLoggerConfig = $matrix_agents_properties["matrix-Log-config"];
        return $this;
    }

    public function debugEvent() : EventLogger
    {
        $this->debugEvent = true;
        $this->regularInfoLogEvent = false;

        //do pre-requisites for a debug event here 

        return $this;
    }

    public function securityEvent() : EventLogger
    {
        $this->securityEvent = true;
        $this->regularInfoLogEvent = false;

        //do pre-requisites for a security event here 

        return $this;
    }

    public function errorEvent() : EventLogger
    {
        $this->errorEvent = true;
        $this->regularInfoLogEvent = false;

        //do pre-requisites for a error event here 

        return $this;
    }

    public function warningEvent() : EventLogger
    {
        $this->warningEvent = true;
        $this->regularInfoLogEvent = false;

        //do pre-requisites for a warning event here 

        return $this;
    }
}
?>