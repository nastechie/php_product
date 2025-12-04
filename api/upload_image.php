<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

// Check if file exists
if (!isset($_FILES['product_image'])) {
    echo json_encode([
        "status" => "error",
        "message" => "No image uploaded"
    ]);
    exit();
}

$uploads_dir = "../uploads/";

// Ensure folder exists
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0777, true);
}

// Create unique file name
$filename = time() . "_" . basename($_FILES["product_image"]["name"]);
$target_path = $uploads_dir . $filename;

// Move file
if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_path)) {
    echo json_encode([
        "status" => "success",
        "file_name" => $filename,
        "image_url" => "http://127.0.0.1/midterm_project/uploads/" . $filename
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Upload failed"
    ]);
}
?>
