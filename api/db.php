<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

$host   = "localhost";
$user   = "root";
$pass   = ""; // Coloque a senha do MySQL aqui, se houver
$dbname = "bitbuy_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Erro na conexão: " . $conn->connect_error]);
    exit;
}

$conn->set_charset("utf8mb4");