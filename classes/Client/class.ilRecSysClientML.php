<?php
class ilRecSysClientML {
    private const RECOMMENDATION_ENDPOINT = "http://127.0.0.1:8000/mlmodelrec/";
    private const TRAIN_ENDPOINT = "http://127.0.0.1:8000/mlmodeltrain/";


    public function postPredictionRequest(MLInput $ml_input) {
        $jsonData = json_encode($ml_input);

        $ch = curl_init(ilRecSysClientML::RECOMMENDATION_ENDPOINT);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $response = curl_exec($ch);
        $ml_output = null;

        if(curl_errno($ch)) {
            throw new Exception("The API request has failed with the following Error status: " . curl_errno($ch) . ". Error: " . curl_error($ch));
        } else {
            $decodedResponse = json_decode($response, true);

            if($decodedResponse === null) {
                throw new Exception("Error while decoding json response to RecSysPrediction. Response: " . $response);
            } else {
                // insert logic here
                $predictionObjects = [];
                foreach ($decodedResponse['predictions'] as $predictionData) {
                    $prediction = new Prediction($predictionData['section_id'], $predictionData['material_type'], $predictionData['score']);
                    $predictionObjects[] = $prediction;
                }

                $ml_output = new MLOutput($decodedResponse['usr_id'], $decodedResponse['crs_id'], $predictionObjects);
            }
        }
        curl_close($ch);
        return $ml_output;
    }

    public function putTrainRequest(TrainInput $trainInput) {

        // Initialize cURL session
        $ch = curl_init(ilRecSysClientML::TRAIN_ENDPOINT);

        // Set cURL options
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($trainInput));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        // Execute cURL session
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        //TODO: later change this 
        return $response;
    }
}

?>