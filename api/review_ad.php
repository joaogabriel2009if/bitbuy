<?php
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

requireAdmin($conn, $data["adminId"] ?? 0);

$adId   = (int)($data["adId"] ?? 0);
$action = $data["action"] ?? "";
$map    = ["aprovar" => "aprovado", "rejeitar" => "rejeitado"];

if (!$adId || !isset($map[$action])) {
    echo json_encode(["success" => false, "message" => "Dados inválidos."]);
    exit;
}

$status = $map[$action];
$stmt = $conn->prepare("UPDATE ads SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $adId);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "status" => $status]);
} else {
    echo json_encode(["success" => false, "message" => "Erro: " . $stmt->error]);
}

$stmt->close();
$conn->close();
