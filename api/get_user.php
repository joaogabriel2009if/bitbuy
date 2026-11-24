<?php
require_once 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "ID não fornecido."]);
    exit;
}

$stmt = $conn->prepare("SELECT id, username, school, accountType, contact, profilePhoto FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user['id']           = (string)$user['id'];
    $user['contact']      = $user['contact']      ?? "";
    $user['profilePhoto'] = $user['profilePhoto'] ?? "";
    echo json_encode(["success" => true, "user" => $user]);
} else {
    echo json_encode(["success" => false, "message" => "Usuário não encontrado."]);
}

$stmt->close();
$conn->close();
