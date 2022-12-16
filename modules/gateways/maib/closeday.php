<?php
// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

// Detect module name from filename.
$gatewayModuleName = "maib";

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

$testmod = $gatewayParams['testMode'];
	if ($testmod == "on") {
	$merchant_url = "https://maib.ecommerce.md:21440/ecomm/MerchantHandler";
	} else {
	$merchant_url = "https://maib.ecommerce.md:11440/ecomm01/MerchantHandler";
	}

$ssl_cert = $gatewayParams['certificate'];
$ssl_key = $gatewayParams['certkey'];
$pasw = $gatewayParams['pass'];

$posts = [
    'command' => 'b',
];

        $reqs = curl_init();
		curl_setopt($reqs, CURLOPT_URL, $merchant_url);
        curl_setopt($reqs, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($reqs, CURLOPT_TIMEOUT, 35);
        curl_setopt($reqs, CURLOPT_HEADER, TRUE);
        curl_setopt($reqs, CURLOPT_POST, TRUE);
        curl_setopt($reqs, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($reqs, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($reqs, CURLOPT_SSLKEY, $ssl_key);
        curl_setopt($reqs, CURLOPT_SSLCERT, $ssl_cert);
        curl_setopt($reqs, CURLOPT_SSLCERTPASSWD, $pasw);
        curl_setopt($reqs, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($reqs, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($reqs, CURLOPT_POSTFIELDS, http_build_query($posts));
        $response = curl_exec($reqs);
		$errors = curl_error($reqs);
		curl_close($reqs);
		
$resp = [];
$parses = explode("\n", $response);

        foreach ($parses as $str) {
            $explode = explode(": ", $str);
            if (isset($explode[1])) {
                list($key, $value) = $explode;
                $resp[$key] = $value;
            }
        }

$closedayStatus = $resp['RESULT'];

if ($closedayStatus === "OK") {
echo "OK";  
} else {
echo "FAILED";    
}