<?php
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

requireAdmin($conn, $data["adminId"] ?? 0);

$adId = (int)($data["adId"] ?? 0);
if (!$adId) {
    echo json_encode(["success" => false, "message" => "ID do anúncio não fornecido."]);
    exit;
}

$conn->begin_transaction();
try {
    foreach (["DELETE FROM reviews WHERE adId = ?",
              "DELETE FROM reports WHERE adId = ?",
              "DELETE FROM ads WHERE id = ?"] as $sql) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $adId);
        $stmt->execute();
        $stmt->close();
    }
    $conn->commit();
    echo json_encode(["success" => true]);
} catch (Throwable $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Erro ao excluir: " . $e->getMessage()]);
}

$conn->close();