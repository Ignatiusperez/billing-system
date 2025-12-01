<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mico_wifi";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$sql = "SELECT ip_address FROM payments WHERE end_time < NOW() AND status = 'completed'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $ip_address = $row['ip_address'];
		
        // Remove iptables rule
        exec("sudo iptables -D FORWARD -s $ip_address -j ACCEPT 2>/dev/null");
        exec("sudo iptables -D INPUT -s $ip_address -j ACCEPT 2>/dev/null");
		
        // Update status
        $update = "UPDATE payments SET status = 'expired' WHERE ip_address = '$ip_address'";
        $conn->query($update);
    }
}
$conn->close();
?>
