<?php
// Ignorar denúncia: o admin analisou e o anúncio está ok -> limpa as denúncias dele
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

requireAdmin($conn, $data["adminId"] ?? 0);

$adId = (int)($data["adId"] ?? 0);
if (!$adId) {
    echo json_encode(["success" => false, "message" => "ID do anúncio não fornecido."]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM reports WHERE adId = ?");
$stmt->bind_param("i", $adId);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Erro: " . $stmt->error]);
}

$stmt->close();
$conn->close();
