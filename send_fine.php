<?php
session_start();
if(!isset($_SESSION['police_id'])){
    header("Location: index.php");
    exit();
}
include 'db.php';

// Get POST data
$plate_number = $_POST['plate_number'] ?? '';
$crimeData    = $_POST['crime'] ?? '';
$location     = $_POST['location'] ?? '';
$notes        = $_POST['notes'] ?? '';

if(!$plate_number || !$crimeData){
    die("❌ Missing required data.");
}

// Parse crime & fine
list($crime, $fine_amount) = explode("|", $crimeData);

// Fetch vehicle + owner info
$res = mysqli_query($conn, "SELECT * FROM vehicles WHERE plate_no='".mysqli_real_escape_string($conn,$plate_number)."'");
if(mysqli_num_rows($res) == 0){
    die("❌ Vehicle not found.");
}
$vehicle = mysqli_fetch_assoc($res);

// Insert violation record
$stmt = mysqli_prepare($conn, "
    INSERT INTO violations 
    (plate_no, owner_name, owner_email, owner_phone, vehicle_model, crime, fine_amount, location, notes, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

mysqli_stmt_bind_param($stmt, "ssssssiss",
    $vehicle['plate_no'],
    $vehicle['owner_name'],
    $vehicle['owner_email'],
    $vehicle['owner_phone'],
    $vehicle['model'],
    $crime,
    $fine_amount,
    $location,
    $notes
);

mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Send email using PHPMailer
require 'libs/PHPMailer/src/PHPMailer.php';
require 'libs/PHPMailer/src/SMTP.php';
require 'libs/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = '';
    $mail->Password   = ''; // Use app password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('', 'Traffic Police');
    $mail->addAddress($vehicle['owner_email'], $vehicle['owner_name']);
    $mail->isHTML(true);
    $mail->Subject = "Traffic Violation Notice - Plate {$vehicle['plate_no']}";

    // Email body (without photo)
    $mail->Body = '
    <!DOCTYPE html>
    <html>
    <head>
    <meta charset="UTF-8">
    <title>Traffic Violation Notice</title>
    </head>
    <body style="font-family:Segoe UI,Tahoma,Verdana,sans-serif;margin:0;padding:0;background:#f0f2f5;">
    <div style="max-width:700px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 8px 25px rgba(0,0,0,0.15);border-top:6px solid #e74c3c;">

    <div style="background:linear-gradient(90deg,#e74c3c,#f39c12);color:#fff;padding:30px;text-align:center;font-size:26px;font-weight:bold;text-shadow:1px 1px 2px rgba(0,0,0,0.3);">
    🚦 Traffic Violation Notice
    </div>

    <div style="padding:30px;color:#333;line-height:1.6;">
    <p>Dear <strong>'.htmlspecialchars($vehicle['owner_name']).'</strong>,</p>
    <p>Your vehicle has been recorded committing a traffic violation. Details are below:</p>

    <div style="background:#f9f9f9;border-left:6px solid #3498db;padding:20px;border-radius:10px;margin:20px 0;">
    <p><strong>Vehicle Plate:</strong> '.htmlspecialchars($vehicle['plate_no']).'</p>
    <p><strong>Vehicle Model:</strong> '.htmlspecialchars($vehicle['model']).'</p>
    <p><strong>Violation:</strong> '.htmlspecialchars($crime).'</p>
    <p><strong>Fine Amount:</strong> ₹'.htmlspecialchars($fine_amount).'</p>
    <p><strong>Location:</strong> '.htmlspecialchars($location).'</p>
    <p><strong>Notes:</strong> '.htmlspecialchars($notes).'</p>
    </div>

    <div style="background:#e8f8f5;border-left:5px solid #1abc9c;padding:18px;margin:20px 0;border-radius:8px;font-size:14px;">
    <strong>Traffic Safety Facts:</strong>
    <ul style="margin:8px 0;padding-left:18px;">
      <li>⚡ 30% of road accidents are due to overspeeding.</li>
      <li>🪖 Wearing helmets reduces head injuries by <strong>69%</strong>.</li>
      <li>🚦 Red-light violations cause <strong>25%</strong> of urban crashes.</li>
      <li>🥂 Driving under the influence increases accident risk by <strong>700%</strong>.</li>
      <li>🅿️ Wrong parking leads to <strong>15%</strong> of minor traffic incidents.</li>
    </ul>
    </div>

    <div style="background:#fcf8e3;border-left:5px solid #f39c12;padding:18px;margin:20px 0;border-radius:8px;font-size:14px;">
    <strong>Traffic Rules Reminder:</strong>
    <ul style="margin:8px 0;padding-left:18px;">
      <li>🚦 Always follow traffic signals.</li>
      <li>🪖 Wear helmets while riding two-wheelers.</li>
      <li>⚡ Do not overspeed. Drive responsibly.</li>
      <li>🥂 Do not drive under influence of alcohol.</li>
      <li>🅿️ Park only in designated areas.</li>
    </ul>
    </div>

    <p style="font-style:italic;color:#555;text-align:center;margin:20px 0;">"Drive safe, arrive safe – every life matters!"</p>

    <div style="text-align:center;">
    <a href="#" style="display:inline-block;background:#3498db;color:#fff;text-decoration:none;padding:12px 25px;border-radius:6px;margin-top:20px;font-weight:bold;">Pay Fine Online</a>
    </div>
    </div>

    <div style="background:linear-gradient(90deg,#2c3e50,#34495e);color:#fff;text-align:center;padding:25px;font-size:14px;">
    Traffic Police Department<br>
    &copy; '.date('Y').' Traffic Authority
    </div>
    </div>
    </body>
    </html>
    ';

    $mail->send();
    header("Location: police_dashboard.php?msg=Fine sent successfully");
    exit();
} catch (Exception $e) {
    die("❌ Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
}
?>
