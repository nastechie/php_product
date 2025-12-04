<?php
// api/get_products.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database connection
require_once '../db.php';

try {
    // Query to get all products
    $sql = "SELECT * FROM tblproduct ORDER BY product_id DESC";
    $result = $conn->query($sql);
    
    $products = array();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Add full image URL if image exists
            if (!empty($row['product_image'])) {
                $row['image_url'] = "http://" . $_SERVER['HTTP_HOST'] . "/midterm_project/uploads/" . $row['product_image'];
            } else {
                $row['image_url'] = null;
            }
            
            $products[] = $row;
        }
        
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "count" => count($products),
            "data" => $products
        ]);
    } else {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "count" => 0,
            "data" => [],
            "message" => "No products found"
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}

$conn->close();
?>