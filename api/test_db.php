<?php
// api/test_db.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../db.php';

$response = [
    'status' => 'success',
    'message' => 'Database test',
    'database_info' => []
];

// Test connection
if ($conn->connect_error) {
    $response['status'] = 'error';
    $response['message'] = 'Connection failed';
    $response['database_info']['connection_error'] = $conn->connect_error;
} else {
    $response['database_info']['connection'] = 'Connected successfully';
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'tblproduct'");
    if ($result->num_rows > 0) {
        $response['database_info']['table_exists'] = true;
        
        // Count products
        $countResult = $conn->query("SELECT COUNT(*) as count FROM tblproduct");
        if ($countResult) {
            $row = $countResult->fetch_assoc();
            $response['database_info']['product_count'] = $row['count'];
        }
        
        // Get sample products
        $sampleResult = $conn->query("SELECT product_id, product_name FROM tblproduct LIMIT 5");
        $samples = [];
        while($row = $sampleResult->fetch_assoc()) {
            $samples[] = $row;
        }
        $response['database_info']['sample_products'] = $samples;
    } else {
        $response['database_info']['table_exists'] = false;
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);

$conn->close();
?>