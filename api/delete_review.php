<?php
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

requireAdmin($conn, $data["adminId"] ?? 0);

$reviewId = (int)($data["reviewId"] ?? 0);
if (!$reviewId) {
    echo json_encode(["success" => false, "message" => "ID do comentário não fornecido."]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
$stmt->bind_param("i", $reviewId);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao excluir: " . $stmt->error]);
}

$stmt->close();
$conn->close();
