<?php
session_start();
require 'vendor/autoload.php'; // PHPMailer Autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $ip_address = $_POST['ip_address']; 
    
    $_SESSION['phone'] = $phone;
    $_SESSION['ip_address'] = $ip_address;

    // Database Connection 
    $servername = "localhost";
    $username = "root"; // CHANGE IF NEEDED
    $password = ""; // CHANGE IF NEEDED
    $dbname = "mico_wifi";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert or Update User
    $stmt = $conn->prepare("INSERT INTO users (phone_number, ip_address) VALUES (?, ?) ON DUPLICATE KEY UPDATE ip_address = ?");
    $stmt->bind_param("sss", $phone, $ip_address, $ip_address);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $_SESSION['user_id'] = $user_id;
    $stmt->close();
    $conn->close();

    // Send Email to Admin
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.gmail.com';                    
        $mail->SMTPAuth   = true;                                   
        $mail->Username   = 'your_email@gmail.com';  // 🔸 CHANGE THIS!
        $mail->Password   = 'your_app_password';    // 🔸 USE APP PASSWORD!
        $mail->SMTPSecure = 'tls';         
        $mail->Port       = 587;                                    

        $mail->setFrom('your_email@gmail.com', 'Mico Internet Services');
        $mail->addAddress('ignatiusperez86@gmail.com', 'Admin');     // Admin email
        
        $mail->isHTML(true);                                  
        $mail->Subject = '🆕 New WiFi Login';
        $mail->Body    = 'A new user has logged in to Mico Internet Services.<br><strong>Phone Number:</strong> ' . htmlspecialchars($phone) . 
                        '<br><strong>IP Address:</strong> ' . htmlspecialchars($ip_address);

        $mail->send();
        header("Location: payment.php"); 
        exit();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        header("Location: payment.php"); 
        exit();
    }
}
?>
