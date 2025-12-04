<?php
// api/get_products.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include your database connection
require_once '../db.php';

try {
    // Fetch products
    $sql = "SELECT * FROM tblproduct ORDER BY product_id DESC";
    $result = $conn->query($sql);

    $products = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            // FIXED: Use 127.0.0.1 (Flutter Web cannot load localhost images)
            if (!empty($row['product_image'])) {
                $row['image_url'] = "http://127.0.0.1/midterm_project/uploads/" . $row['product_image'];
            } else {
                $row['image_url'] = null;
            }

            $products[] = $row;
        }
    }

    echo json_encode([
        "status" => "success",
        "count" => count($products),
        "data" => $products
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}

$conn->close();
