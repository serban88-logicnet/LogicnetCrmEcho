<?php
$clientId = 'b8fab52f1dd2966dcfb6b49577252edd0c58d20f15001268';
$redirectUri = urlencode('https://crm-dev.logicnet.ro/anafcallback.php');
$authUrl = 'https://logincert.anaf.ro/anaf-oauth2/v1/authorize';

$url = $authUrl . "?response_type=code&client_id=$clientId&redirect_uri=$redirectUri";

header("Location: $url");
exit;
