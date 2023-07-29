<?php

class ilRecSysCoreDBdriverLibrary{
    private $username;
    private $password;
    private $apiurl;
    private $domain;
    private $servicePath;
    private $response;

    public function __construct() {
        $this->username = "";
        $this->password = "";
        $this->apiurl = "";
        $this->response = array("result" => '', "info" => '', "data" => '', "httpcode" => "0");
    }

    //setter
    public function setUsername($username) { $this->username = $username; }
    public function setPassword($password) { $this->password = $password; }
    public function setApiUrl($apiurl) {
        $this->servicePath = parse_url($apiurl, PHP_URL_PATH);
        $pathStart = strpos($apiurl, $this->servicePath);
        $this->domain = substr($apiurl, $pathStart);
        $this->apiurl = $apiurl;
    }
    public function init($username, $password, $apiurl) {
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setApiUrl($apiurl);
    }

    //getter
    public function getPassword() {return $this->password; }

    public function get($controller, $args) {
        $str = "";
        if(is_array($args) && (count($args) > 0)) {
            foreach($args as $key => $value){
                $str .= $key . "=" . strval($value) . "&";
            }
            $str = "?" . rtrim($str, "&");
        }
        $path = $controller . $str;
        $response = $this->sendGet($path);
        return $response;
    }

    private function sendGet($path) {
        $requestPath = $this->servicePath . $path;
        $passwhash = base64_encode(md5($requestPath . "_" . $this->password));

        //setup CURL connection to server
        $curl = curl_init($this->apiurl . $path);
        //set options for CURL connection
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type application/json', 'Accept text/plain'));
        curl_setopt($curl, CURLOPT_USERPWD, $this->username . ":" . $passwhash);

        //execute CURL command
        $response = curl_exec($curl);

        $response = json_decode($response, true);
        $response['httpcode'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response['fullpath'] = "GET: " . $this->apiurl . $path;
        //close CURL connection
        curl_close($curl);

        return $response;
    }

    public function post($controller, $data) {
        $jsonData = json_encode($data);
        $response = $this->sendPost($controller, $jsonData);

        return $response;
    }

    private function sendPost($path, $jsonData) {
        $requestPath = $this->servicePath . $path;
        $passwhash = base64_encode(md5($requestPath . "_" . $this->password));

        //setup CURL connection to server
        $curl = curl_init($this->apiurl . $path);

        //set options for CURL connection
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Contenet-type application/json', 'Accept text/plain'));
        curl_setopt($curl, CURLOPT_USERPWD, $this->username . ":" . $passwhash);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        
        //execute CURL command
        $response = curl_exec($curl);
        $response = json_decode($response, true);
        $response['httpcode'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response['fullpath'] = "POST: " . $this->apiurl . $path;
        
        //close CURL connection
        curl_close($curl);

        return $response;
    }

    public function getPostRequest($path, $data = array()) {
        $request = array();

        $jsonData = json_encode($data);
        $requestPath = $this->servicePath . $path;
        $passwhash = base64_encode(md5($requestPath . "_" . $this->password));

        $request['url'] = $this->apiurl . $path;
        $request['userpw'] = $this->username . ":" . $passwhash;
        $request['data'] = $jsonData;
        
        return $request;
    }
}

?>