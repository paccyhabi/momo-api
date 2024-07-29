<?php
include_once("config.php"); // Ensure this file contains the baseUrl and any other necessary configurations

function createApiUser($referenceId, $secondaryKey, $callbackHost) {
    global $baseUrl;
    
    // Prepare the data
    $data = [
        "providerCallbackHost" => $callbackHost
    ];
    
    // Prepare the URL
    $url = "$baseUrl/v1_0/apiuser";
    
    // Initialize cURL session
    $ch = curl_init($url);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "X-Reference-Id: " . $referenceId,
        "Ocp-Apim-Subscription-Key: " . $secondaryKey,
        "Content-Type: Application/json"
    ));
    
    // Execute cURL session
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return [
            'success' => false,
            'error' => $error
        ];
    } else {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => $httpCode == 201,
            'http_code' => $httpCode,
            'response' => $response
        ];
    }
}

function createApiKey($referenceId, $secondaryKey) {
    global $baseUrl;
    $url = "$baseUrl/v1_0/apiuser/$referenceId/apikey";

    // Initialize cURL session
    $curl = curl_init($url);

    // Set cURL options
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    // Set request headers
    $headers = array(
        'Cache-Control: no-cache',
        'Ocp-Apim-Subscription-Key: ' . $secondaryKey,
        'Content-Length: 0', // Set Content-Length header to zero if body is empty
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    // Execute cURL request
    $response = curl_exec($curl);
    
    // Check for cURL errors
    if (curl_errno($curl)) {
        $error = curl_error($curl);
        curl_close($curl);
        return [
            'success' => false,
            'http_code' => null,
            'response' => null,
            'error' => $error
        ];
    } else {
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return [
            'success' => $http_code == 201,
            'http_code' => $http_code,
            'response' => $response
        ];
    }
}

function getAccessToken($referenceId, $apiKey, $secondaryKey) {
    global $baseUrl;
    $url = "$baseUrl/collection/token/";

    // Initialize cURL session
    $curl = curl_init($url);

    // Prepare the Authorization header with Basic Authentication
    $auth = base64_encode("$referenceId:$apiKey");

    // Set cURL options
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    // Set request headers
    $headers = array(
        'Authorization: Basic ' . $auth,
        'Cache-Control: no-cache',
        'Ocp-Apim-Subscription-Key: ' . $secondaryKey,
        'Content-Length: 0', // Set Content-Length header to zero if body is empty
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    // Execute cURL request
    $response = curl_exec($curl);
    
    // Check for cURL errors
    if (curl_errno($curl)) {
        $error = curl_error($curl);
        curl_close($curl);
        return [
            'success' => false,
            'http_code' => null,
            'response' => null,
            'error' => $error
        ];
    } else {
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return [
            'success' => $http_code == 200,
            'http_code' => $http_code,
            'response' => $response
        ];
    }
}


function requestToPay($accessToken, $secondaryKey, $referenceId, $amount, $externalId, $payerPartyId, $callbackHost) {
    global $baseUrl;
    $url = "$baseUrl/collection/v1_0/requesttopay";

    // Prepare the data
    $data = [
        "amount" => $amount,
        "currency" => "EUR",
        "externalId" => $externalId,
        "payer" => [
            "partyIdType" => "MSISDN",
            "partyId" => $payerPartyId
        ],
        "payerMessage" => "Payment successful",
        "payeeNote" => "You have received"
    ];

    // Initialize cURL session
    $curl = curl_init($url);

    // Prepare the Authorization header with Bearer Token
    $headers = array(
        "Authorization: Bearer " . $accessToken,
        'X-Callback-Url: ' . $callbackHost,
        'X-Reference-Id: ' . $referenceId,
        'X-Target-Environment: sandbox',
        'Content-Type: application/json',
        'Cache-Control: no-cache',
        'Ocp-Apim-Subscription-Key: ' . $secondaryKey
    );
    // Log the request data and headers for debugging
    error_log("Request Data: " . json_encode($data));
    error_log("Request Headers: " . implode(", ", $headers));

    // Set cURL options
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

    // Execute cURL request
    $response = curl_exec($curl);

    // Check for cURL errors
    if (curl_errno($curl)) {
        $error = curl_error($curl);
        curl_close($curl);
        return [
            'success' => false,
            'http_code' => null,
            'response' => null,
            'error' => $error
        ];
    } else {
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $responseData = json_decode($response, true);

        return [
            'success' => $http_code == 202,
            'http_code' => $http_code,
            'response' => $response,
            'error' => isset($responseData['error']) ? $responseData['error'] : 'Unknown error'
        ];
    }
}


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $referenceId = $uuid; // Generate or set the UUID as needed
    $callbackHost = "localhost/momo/api"; // Replace with actual callback host if necessary
    $secondaryKey = '1820814545d34fcfb29eb7543f2ea4cf'; 
    $amount = $_POST['amount'];
    $externalId = $shortUuid;
    $payerPartyId = $_POST['phoneNumber'];

    // Create API User
    $response = createApiUser($referenceId, $secondaryKey, $callbackHost);    
    if ($response['success']) {
        echo "API User creation was successful! The response code is: " . $response['http_code'];
        echo "Response: " . $response['response'] . "\n";
        echo "<br>";
    } else {
        echo "API User creation failed with the code: " . $response['http_code'];
        echo "Error: " . $response['error'] . "\n";
    }
    
    // Create API Key
    $response = createApiKey($referenceId, $secondaryKey);
    if ($response['success']) {
        echo "API Key creation was successful! The response code is: " . $response['http_code'];
        echo "Response: " . $response['response'] . "\n";
        echo "<br>";
        $apiKey = json_decode($response['response'], true)['apiKey']; // Extract the API Key from the response
    } else {
        echo "API Key creation failed with the code: " . $response['http_code'];
        echo "Error: " . (isset($response['error']) ? $response['error'] : 'Unknown error') . "\n";
        exit;
    }

    // Retrieve Access Token
    $response = getAccessToken($referenceId, $apiKey, $secondaryKey);
    if ($response['success']) {
        echo "Access Token retrieval was successful! The response code is: " . $response['http_code'];
        echo "Response: " . $response['response'] . "\n";
        echo "<br>";
        $data = json_decode($response['response'], true);
            $accessToken = $data['access_token'];
        
    } else {
        echo "Access Token retrieval failed with the code: " . $response['http_code'];
        echo "Error: " . (isset($response['error']) ? $response['error'] : 'Unknown error') . "\n";
    }   
    
    $response = requestToPay($accessToken, $secondaryKey, $referenceId, $amount, $externalId, $payerPartyId, $callbackHost);
    if ($response['success']) {
        echo "Request to Pay was successful! The response code is: " . $response['http_code'];
        echo "Response: " . $response['response'] . "\n";
    } else {
        echo "Request to Pay failed with the code: " . $response['http_code'];
        echo "Error: " . $response['error'] . "\n";
        echo "<br>";
    }
}
?>
