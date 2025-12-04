<?php
// api/insert_product.php

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../db.php';

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Get POST data
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Check if data is valid
if (!$data) {
    echo json_encode([
        "status" => "error", 
        "message" => "No data received"
    ]);
    exit();
}

// Extract and sanitize data with defaults
$product_name = $conn->real_escape_string($data['product_name'] ?? '');
$category = $conn->real_escape_string($data['category'] ?? '');
$description = $conn->real_escape_string($data['description'] ?? '');
$qty = isset($data['qty']) ? (int)$data['qty'] : 0;
$unit_price = isset($data['unit_price']) ? (float)$data['unit_price'] : 0.00;
$product_image = $conn->real_escape_string($data['product_image'] ?? '');
$status = $conn->real_escape_string($data['status'] ?? 'active');

// Handle image upload if provided via multipart (not JSON)
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
    $target_dir = "../uploads/";
    $image_name = time() . "_" . basename($_FILES['product_image']['name']);
    $target_file = $target_dir . $image_name;
    
    // Create uploads directory if not exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
        $product_image = $image_name;
    }
}

// Validate required fields
if (empty($product_name) || empty($category)) {
    echo json_encode([
        "status" => "error",
        "message" => "Product name and category are required"
    ]);
    exit();
}

// Insert query
$sql = "INSERT INTO tblproduct (product_name, category, description, qty, unit_price, product_image, status)
        VALUES ('$product_name', '$category', '$description', $qty, $unit_price, '$product_image', '$status')";

if ($conn->query($sql)) {
    $product_id = $conn->insert_id;
    
    echo json_encode([
        "status" => "success",
        "message" => "Product added successfully",
        "product_id" => $product_id,
        "data" => [
            "product_id" => $product_id,
            "product_name" => $product_name,
            "category" => $category,
            "description" => $description,
            "qty" => $qty,
            "unit_price" => $unit_price,
            "product_image" => $product_image,
            "status" => $status
        ]
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $conn->error
    ]);
}

$conn->close();
?>