<?php
if (!isset($_GET['code'])) {
    echo "Missing code from ANAF.";
    exit;
}

$code = $_GET['code'];

$clientId = 'b8fab52f1dd2966dcfb6b49577252edd0c58d20f15001268';
$clientSecret = '07e7ff87fb5e0cba4198b0db8f2d36a8ba0abe6b9e8c2edd0c58d20f15001268';
$redirectUri = 'https://crm-dev.logicnet.ro/anafcallback.php';

$tokenUrl = 'https://logincert.anaf.ro/anaf-oauth2/v1/token';

$data = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $redirectUri,
    'token_content_type' => 'jwt'
];

$options = [
    CURLOPT_URL => $tokenUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($data),
    CURLOPT_HTTPHEADER => [
        'Authorization: Basic ' . base64_encode("$clientId:$clientSecret"),
        'Content-Type: application/x-www-form-urlencoded'
    ]
];

$ch = curl_init();
curl_setopt_array($ch, $options);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "cURL error: $error";
} else {
    echo "<pre>";
    print_r(json_decode($response, true));
    echo "</pre>";
}
