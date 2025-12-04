<?php
// api/get_products.php

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

// Get all products
$sql = "SELECT * FROM tblproduct ORDER BY product_id DESC";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "status" => "error",
        "message" => "Query failed: " . $conn->error
    ]);
    exit();
}

$products = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Add full URL for images if exists
        if (!empty($row['product_image'])) {
            // Get current protocol and host
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $base_url = "$protocol://$host/midterm_project";
            $row['image_url'] = "$base_url/uploads/" . $row['product_image'];
        } else {
            $row['image_url'] = null;
        }
        $products[] = $row;
    }
    
    echo json_encode([
        "status" => "success",
        "count" => count($products),
        "data" => $products
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "count" => 0,
        "data" => [],
        "message" => "No products found"
    ]);
}

$conn->close();
?>