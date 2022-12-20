<?php
use WHMCS\Database\Capsule;
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function maib_MetaData()
{
    return array(
        'DisplayName' => 'maib - online payments via card',
        'APIVersion' => '1.1',
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}


function maib_config($params)
{
if (!Capsule::schema()->hasTable('maib_transactions')) {
try {
       Capsule::schema()->create('maib_transactions', function($table) {
           $table->increments('table_id');
           $table->integer('invoice_id');
           $table->string('trans_id', 100);
           $table->float('amount', 10, 2);
       });
   
} catch (\Exception $e) {
    echo "Unable to create maib_transactions: {$e->getMessage()}";
}
}
  
  
$cert_path = __DIR__.'/maib/test-certificate.pem';
$key_path = __DIR__.'/maib/test-key.pem';
$systemUrl = $params['systemurl'];
$callback_url = $systemUrl . 'modules/gateways/callback/maib.php';
$close_url = $systemUrl . 'modules/gateways/maib/closeday.php';

    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'maib - online payments via card',
        ),
        'testMode' => array(
            'FriendlyName' => 'Test Mode',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode',
        ),
        'callb' => array(
            'FriendlyName' => 'Callback',
            'Type' => 'text',
            'Size' => '',
            'Default' => $callback_url,
            'Description' => 'Send server IP and this Callback to ecom@maib.md',
        ),
        'certificate' => array(
            'FriendlyName' => 'Certificate',
            'Type' => 'text',
            'Size' => '',
            'Default' => $cert_path,
            'Description' => 'Path of certificate .pem (by default path to test certificate)',
        ),
        'certkey' => array(
            'FriendlyName' => 'Key certificate',
            'Type' => 'text',
            'Size' => '',
            'Default' => $key_path,
            'Description' => 'Path of key certificate .pem (by default path to test key)',
        ),
        'pass' => array(
            'FriendlyName' => 'Password',
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Password of certificate (test password: Za86DuC)',
        ),
        'closeday' => array(
            'FriendlyName' => 'Closing of business day',
            'Type' => 'text',
            'Size' => '',
            'Default' => $close_url,
            'Description' => 'Run this URL with CRON job every day at 23:59!',
        ),
    );
}


function maib_link($params)
{
	$testmod = $params['testMode'];
	if ($testmod == "on") {
	$merchant_url = "https://maib.ecommerce.md:21440/ecomm/MerchantHandler";
	$client_url = "https://maib.ecommerce.md:21443/ecomm/ClientHandler";
	} else {
	$merchant_url = "https://maib.ecommerce.md:11440/ecomm01/MerchantHandler";
	$client_url = "https://maib.ecommerce.md:443/ecomm01/ClientHandler";
	}

	$currency_codes = array(
			'USD' => 840,
			'EUR' => 978,
			'MDL' => 498
		);
		
    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'] * 100;
    $currency_code = $currency_codes[$params['currency']];
	
	$ssl_cert = $params['certificate'];
	$ssl_key = $params['certkey'];
	$pasw = $params['pass'];

    // Client Parameters
    $email = $params['clientdetails']['email'];

    // System Parameters
    $systemUrl = $params['systemurl'];
    $langPayNow = $params['langpaynow'];
    $moduleName = $params['paymentmethod'];

	$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    $ip = $_SERVER['REMOTE_ADDR'];
    if(preg_match("#^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$#", $ip)) {
	$ip = $_SERVER['REMOTE_ADDR'];
	} else {
	$ip = "127.0.0.1";
	}
  
    $posts = [
                'command' => 'v',
                'language' => $lang,
                'amount' => $amount,
                'currency' => $currency_code,
                'client_ip_addr' => $ip,
                'description' => "Invoice ID: " . $invoiceId,
                'msg_type' => "SMS",
    ];
	
	if (!isset($currency_codes[$params['currency']])) {
		return "Currency must be in MDL, USD or EUR!";
		} else {
   
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
     
if (isset($resp['TRANSACTION_ID'])) {
// Perform potentially risky queries in a transaction for easy rollback.
$pdo = Capsule::connection()->getPdo();
$pdo->beginTransaction();

try {
    $statement = $pdo->prepare(
        'insert into maib_transactions (invoice_id, trans_id, amount) values (:invoice_id, :trans_id, :amount)'
    );

    $statement->execute(
        [
            ':invoice_id' => $invoiceId,
            ':trans_id' => $resp['TRANSACTION_ID'],
            ':amount' => $params['amount'],
        ]
    );

    $pdo->commit();
} catch (\Exception $e) {
    echo "Error! {$e->getMessage()}";
    $pdo->rollBack();
}

header("Location: " . $client_url . "?trans_id=" . $resp['TRANSACTION_ID']);
exit;
   } else {
if(!empty($errors)) {
  logTransaction("maib", $errors, "ERROR CURL");
  return "Error! Please see gateway transaction log.";  
    }
 
if(!empty($response)) { 
  logTransaction("maib", $response, "ERROR");
  return "Error! Please see gateway transaction log.";  
    }
  }
 } 
}

function maib_refund($params)
{
	$testmod = $params['testMode'];
	if ($testmod == "on") {
	$merchant_url = "https://maib.ecommerce.md:21440/ecomm/MerchantHandler";
	} else {
	$merchant_url = "https://maib.ecommerce.md:11440/ecomm01/MerchantHandler";
	}


    // Invoice Parameters
	$transactionIdToRefund = $params['transid'];
    $refundAmount = $params['amount'] * 100;
	
	$ssl_cert = $params['certificate'];
	$ssl_key = $params['certkey'];
	$pasw = $params['pass'];

 
    $posts = [
			'command' => 'r',
            'amount' => $refundAmount,
            'trans_id' => $transactionIdToRefund
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
		
$reversStatus = $resp['RESULT'];
  
if ($reversStatus === "OK") {  
   return array(
        // 'success' if successful, otherwise 'declined', 'error' for failure
        'status' => 'success',
        'rawdata' => $response,
        'transid' => "r_" . $transactionIdToRefund,
    );
} else {
    return array(
        'status' => 'error',
        'rawdata' => $response,
    ); 
}
}
