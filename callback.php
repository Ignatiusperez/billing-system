<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mico_wifi";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$checkoutRequestID = $data['Body']['stkCallback']['CheckoutRequestID'];
$status = $data['Body']['stkCallback']['ResultCode'] == 0 ? 'completed' : 'failed';

// Update Payment Status
$stmt = $conn->prepare("UPDATE payments SET status = ? WHERE transaction_id = ?");
$stmt->bind_param("ss", $status, $checkoutRequestID);
$stmt->execute();
$stmt->close();


if ($status == 'completed') {
    $result = $data['Body']['stkCallback']['CallbackMetadata']['Item'];
    $mpesaReceiptNumber = '';
    $phoneNumber = '';
    $amount = '';
    foreach ($result as $item) {
        if ($item['Name'] == 'MpesaReceiptNumber') $mpesaReceiptNumber = $item['Value'];
        if ($item['Name'] == 'PhoneNumber') $phoneNumber = $item['Value'];
        if ($item['Name'] == 'Amount') $amount = $item['Value'];
    }
	
    // Get Package and IP from DB
    $stmt = $conn->prepare("SELECT package, ip_address FROM payments WHERE transaction_id = ?");
    $stmt->bind_param("s", $checkoutRequestID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $package = $row['package'];
    $ip_address = $row['ip_address'];
    $stmt->close();
	
    // Calculate End Time
    if ($package == '1hour') $duration = ' +1 HOUR';
    if ($package == '3hours') $duration = ' +3 HOUR';
    if ($package == 'daily') $duration = ' +1 DAY';
    if ($package == 'weekly') $duration = ' +1 WEEK';
    if ($package == 'monthly') $duration = ' +1 MONTH';
	
    $startTime = date('Y-m-d H:i:s');
    $endTime = date('Y-m-d H:i:s', strtotime($startTime . $duration));
	
    // Update Payment
    $stmt = $conn->prepare("UPDATE payments SET mpesa_receipt_number = ?, start_time = ?, end_time = ? WHERE transaction_id = ?");
    $stmt->bind_param("ssss", $mpesaReceiptNumber, $startTime, $endTime, $checkoutRequestID);
    $stmt->execute();
    $stmt->close();
	
    // 🔸 REPLACE WITH YOUR AFRICA'S TALKING CREDENTIALS!
    $username =  'YOUR_AT_USERNAME';     
    $apiKey =  'YOUR_AT_APIKEY'; 
    $AT_URL = 'https://api.africastalking.com/version1/messaging';

    $message = "✅ Thank you for purchasing internet from Mico Internet Services! 
    Your access is now active until: $endTime.
    Transaction ID: $mpesaReceiptNumber. 
    Enjoy your browsing!";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $AT_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json','apiKey:'.$apiKey]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'to' => $phoneNumber,
        'message' => $message
    ]));

    $response = curl_exec($ch);
    curl_close($ch);
	
    // 🔥 ACTIVATE INTERNET ACCESS (Run on Router!)
    exec("sudo iptables -A FORWARD -s $ip_address -j ACCEPT");
    exec("sudo iptables -A INPUT -s $ip_address -j ACCEPT");

}
$conn->close();

echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
?>
