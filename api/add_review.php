<?php
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$adId     = isset($data["adId"])     ? (int)$data["adId"]     : null;
$userId   = isset($data["userId"])   ? (int)$data["userId"]   : null;
$username = trim($data["username"]   ?? "");
$rating   = isset($data["rating"])   ? (int)$data["rating"]   : null;
$comment  = trim($data["comment"]    ?? "");

if (!$adId || !$userId || !$username || $rating === null) {
    echo json_encode(["success" => false, "message" => "Dados da avaliação incompletos."]);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(["success" => false, "message" => "Avaliação deve ser entre 1 e 5."]);
    exit;
}

$date = date('Y-m-d H:i:s');

$stmt = $conn->prepare("INSERT INTO reviews (adId, userId, username, rating, comment, date) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisiss", $adId, $userId, $username, $rating, $comment, $date);

if ($stmt->execute()) {
    $id = $conn->insert_id;
    echo json_encode([
        "success"   => true,
        "newReview" => [
            "id"       => (string)$id,
            "userId"   => (string)$userId,
            "username" => $username,
            "rating"   => $rating,
            "comment"  => $comment,
            "date"     => $date
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao salvar avaliação: " . $stmt->error]);
}

$stmt->close();
$conn->close();