<?php
class ilRecSysClientML {
    private const RECOMMENDATION_ENDPOINT = "http://127.0.0.1:8000/mlmodelrec/";

    public function makePostRequest($ml_input) {
        $jsonData = json_encode($ml_input);

        $ch = curl_init(ilRecSysClientML::RECOMMENDATION_ENDPOINT);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $response = curl_exec($ch);

        if(curl_errno($ch)) {
            throw new Exception("The API request has failed with the following Error status: " . curl_errno($ch) . ". The Error: " . curl_error($ch));
        } else {
            $decodedResponse = json_decode($response, true);

            if($decodedResponse === null) {
                throw new Exception("Error while decoding json response to RecSysPrediction");
            } else {
                // insert logic here
            }
        }
        curl_close($ch);
    }
}


?>