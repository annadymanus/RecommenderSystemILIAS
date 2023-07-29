<?php

class ilRecSysCoreDB {

    private $RecSysCoreDBdriver;
    private $ConfigModel;

    //Array ( [result] => true [info] => stored sucessfully [id] => 4 [httpcode] => 200 )
    private $responseArray;

    public function __construct($context)
    {
        $this->ConfigModel = new ilRecSysModelConfig();
        $this->RecSysCoreDBdriver = new ilRecSysCoreDBdriverLibrary();

        if($context == "tracking") {
            $username = $this->ConfigModel->getConfigItem("recsyscore_tracking_username");
            $passw = $this->ConfigModel->getConfigItem("recsyscore_tracking_password");
        } else {
            $username = $this->ConfigModel->getConfigItem("recsyscore_username");
            $passw = $this->ConfigModel->getConfigItem("recsyscore_password");
        }
        $this->RecSysCoreDBdriver->init($username,$passw, $this->ConfigModel->getConfigItem("recsyscore_apiurl"));
    }

    //getter
    public function getResponseArray() { return $this->responseArray; }
    public function returnHttpCode() { return $this->responseArray['httpcode']; }
    public function returnResult() { return $this->responseArray['result']; }
    public function returnInfo() { return $this->responseArray['info']; }

    public function httpOK() {
        return ($this->returnHttpCode() == "200");
    }

    public function resultOK() {
        return ($this->returnResult() == "true");
    }

    public function returnData() {
        if($this->resultOK()){
            if(array_key_exists('data', $this->responseArray)){
                return $this->responseArray['data'];
            }
        }
        return False;
    }


    public function checkLastConnection() {
        if($this->returnHttpCode() == 0) {
            ilUtil::sendFailure("No connection for " . $this->responseArray['fullpath'], True);
            return False;
        } elseif(!$this->httpOK()) {
            ilUtil::sendFailure("No connection for " . $this->responseArray['fullpath'] . " . HTTP response code: " . $this->returnHttpCode(), True);
            return False;
        } elseif(!$this->resultOK()) {
            ilUtil::sendFailure("Faulty connection for " . $this->responseArray['fullpath'] . " . \"" . $this->responseArray['info'] . "\"", True);
            return False;
        }
        return True;
    }

    //functions to test the RecSysCoreDBdriver
    public function getHelloWorld() {
        $this->responseArray = $this->RecSysCoreDBdriver->get("hello", array());
        return $this->responseArray;
    }

    public function getTrackingHelloWorld() {
        $data = array('hash'           => 'hello',
                      'iliascourseid'  => '0',
                      'iliasresouceid' => '0',
                      'type'           => '',
                      'content'        => '');
      $this->responseArray = $this->RecSysCoreDBdriver->post("event", $data);
      return $this->responseArray;  
    }

    public function createCourse($RecSysCourse){
        
    }
}

?>