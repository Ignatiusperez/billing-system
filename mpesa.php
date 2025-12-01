<?php
header('Content-Type: application/json');
session_start();

$phone = $_POST['phone'];
$amount = $_POST['amount'];
$package = $_POST['package'];
$ip_address = $_POST['ip_address'];

// 🔸 REPLACE WITH YOUR SANDBOX CREDENTIALS!
$consumerKey = 'YOUR_SANDBOX_CONSUMER_KEY';  
$consumerSecret = 'YOUR_SANDBOX_CONSUMER_SECRET'; 
$passkey = 'YOUR_SANDBOX_PASSKEY';
$shortCode = '174379'; 
$callbackURL = 'http://192.168.1.1/wifi_billing/mpesa_callback.php'; 

// Generate Access Token
$credentials = base64_encode($consumerKey . ':' . $consumerSecret);
$url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

$token = json_decode($result)->access_token;

// Prepare STK Push Request
$timestamp = date('YmdHis');
$password = base64_encode($shortCode . $passkey . $timestamp);

$stkPushURL = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

$postData = [
    'BusinessShortCode' => $shortCode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $amount,
    'PartyA' => $phone,
    'PartyB' => $shortCode,
    'PhoneNumber' => $phone,
    'CallBackURL' => $callbackURL,
    'AccountReference' => 'Mico Internet',
    'TransactionDesc' => 'Payment for ' . $package . ' package'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $stkPushURL);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $token]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

$response = json_decode($result);

if (isset($response->ResponseCode) && $response->ResponseCode == 0) {
    
    // Save payment info to database 
    $servername = "localhost";
    $username = "root"; 
    $password = ""; 
    $dbname = "mico_wifi";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO payments (user_id, package, amount, phone_number, transaction_id, status, ip_address) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
    $stmt->bind_param("ississs", $user_id, $package, $amount, $phone, $response->CheckoutRequestID, $ip_address);
    $stmt->execute();
    $stmt->close();
    $conn->close();
   

    echo json_encode(['success' => true, 'message' => '✅ STK Push Sent']);
} else {
    echo json_encode(['success' => false, 'message' => '❌ STK Push failed: ' . $response->errorMessage]);
}
?>
