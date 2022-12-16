<?php
use WHMCS\Database\Capsule;
// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

if(empty($_POST['trans_id'])) {
   die("Error! Transaction not completed.");
}

$testmod = $gatewayParams['testMode'];
	if ($testmod == "on") {
	$merchant_url = "https://maib.ecommerce.md:21440/ecomm/MerchantHandler";
	} else {
	$merchant_url = "https://maib.ecommerce.md:11440/ecomm01/MerchantHandler";
	}

$trans_id = $_POST['trans_id'];
$ssl_cert = $gatewayParams['certificate'];
$ssl_key = $gatewayParams['certkey'];
$pasw = $gatewayParams['pass'];

$ip = $_SERVER['REMOTE_ADDR'];
if(preg_match("#^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$#", $ip)) {
$ip = $_SERVER['REMOTE_ADDR'];
} else {
$ip = "127.0.0.1";
}

$posts = [
    'command' => 'c',
    'trans_id' => $trans_id,
    'client_ip_addr' => $ip,
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

$transactionStatus = $resp['RESULT'];
$transactionId = $trans_id;

try { 
$invoiceId = Capsule::table('maib_transactions')
        ->where("trans_id", "=", $transactionId)
        ->value('invoice_id');
  
$paymentAmount = Capsule::table('maib_transactions')
        ->where("trans_id", "=", $transactionId)
        ->value('amount');  
} catch(\Illuminate\Database\QueryException $ex){
    echo $ex->getMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}

checkCbTransID($transactionId);

logTransaction($gatewayParams['name'], $_POST, $transactionStatus);
$paymentSuccess = false;
 
if ($transactionStatus === "OK") {
     /**
     * Add Invoice Payment.
     *
     * Applies a payment transaction entry to the given invoice ID.
     *
     * @param int $invoiceId         Invoice ID
     * @param string $transactionId  Transaction ID
     * @param float $paymentAmount   Amount paid (defaults to full balance)
     * @param float $paymentFee      Payment fee (optional)
     * @param string $gatewayModule  Gateway module name
     */
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $paymentAmount,
        0,
        $gatewayModuleName 
    );

    $paymentSuccess = true;
}

callback3DSecureRedirect($invoiceId, $paymentSuccess);