<?php
// api/delete_product.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['product_id'])) {
    echo json_encode(["status" => "error", "message" => "Product ID required"]);
    exit;
}

$id = intval($data['product_id']);

$sql = "DELETE FROM tblproduct WHERE product_id = $id";

if ($conn->query($sql)) {
    echo json_encode(["status" => "success", "message" => "Product deleted"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$conn->close();
?>