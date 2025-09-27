<?php
session_start();
if(!isset($_SESSION['police_id'])){
    exit("❌ Unauthorized");
}
include 'db.php';

$plate = $_GET['plate'] ?? '';
if(!$plate){
    exit("❌ Plate not provided.");
}

// Lookup vehicle
$stmt = mysqli_prepare($conn, "
    SELECT plate_no, model, owner_name, owner_email, owner_phone
    FROM vehicles
    WHERE plate_no = ?
");

mysqli_stmt_bind_param($stmt, "s", $plate);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0){
    echo "<p>🚫 No record found for plate <strong>".htmlspecialchars($plate)."</strong>.</p>";
    exit();
}

$row = mysqli_fetch_assoc($result);

// Display info
echo "
    <table border='1' cellpadding='6'>
        <tr><th>Plate Number</th><td>".htmlspecialchars($row['plate_no'])."</td></tr>
        <tr><th>Model</th><td>".htmlspecialchars($row['model'])."</td></tr>
        <tr><th>Owner Name</th><td>".htmlspecialchars($row['owner_name'])."</td></tr>
        <tr><th>Email</th><td>".htmlspecialchars($row['owner_email'])."</td></tr>
        <tr><th>Phone</th><td>".htmlspecialchars($row['owner_phone'])."</td></tr>
    </table>
";
?>
