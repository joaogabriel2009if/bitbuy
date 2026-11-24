<?php
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$adId     = (int)($data["adId"]   ?? 0);
$userId   = (int)($data["userId"] ?? 0);
$username = trim($data["username"] ?? "");

if (!$adId || !$userId || !$username) {
    echo json_encode(["success" => false, "message" => "Dados incompletos."]);
    exit;
}

$createdAt = date('Y-m-d H:i:s');

// INSERT IGNORE: se o mesmo usuário denunciar de novo, não duplica
$stmt = $conn->prepare("INSERT IGNORE INTO reports (adId, userId, username, createdAt) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $adId, $userId, $username, $createdAt);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao denunciar: " . $stmt->error]);
}

$stmt->close();
$conn->close();
