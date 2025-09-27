<?php
session_start();
if(!isset($_SESSION['police_id'])){
    header("Location: index.php");
    exit();
}

$uploadDir = __DIR__ . "/assets/uploads/";
if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if(isset($_FILES['plate_image']) && $_FILES['plate_image']['error'] === UPLOAD_ERR_OK){
    $tmpName = $_FILES['plate_image']['tmp_name'];
    $fileName = time() . "_" . basename($_FILES['plate_image']['name']);
    $targetFile = $uploadDir . $fileName;

    if(move_uploaded_file($tmpName, $targetFile)){

        // ---------- Plate Recognizer OCR ----------
        $apiKey = "07f9335cc0e65f77dcfa8fe3dfd29855afe35077";  // <-- Replace with your API key
        $apiUrl = "https://api.platerecognizer.com/v1/plate-reader/";

        $ch = curl_init();
        $data = ['upload' => curl_file_create($targetFile)];
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Token $apiKey"]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if(!empty($result['results'][0]['plate'])){
            $plate = strtoupper($result['results'][0]['plate']);
        } else {
            $plate = "UNKNOWN";
        }

        // Redirect to dashboard with plate number
        header("Location: police_dashboard.php?plate=" . urlencode($plate));
        exit();

    } else {
        die("❌ Failed to move uploaded file.");
    }
} else {
    die("❌ File upload error.");
}
?>
