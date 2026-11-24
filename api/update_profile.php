<?php
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = isset($data["id"]) ? (int)$data["id"] : null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "ID não fornecido."]);
    exit;
}

$allowedFields = ["username", "contact", "profilePhoto"];
$setParts      = [];
$types         = "";
$values        = [];

foreach ($allowedFields as $field) {
    if (isset($data[$field])) {
        $setParts[] = "$field = ?";
        $types     .= "s";
        $values[]   = $data[$field];
    }
}

if (empty($setParts)) {
    // Nada a atualizar — considera sucesso
    echo json_encode(["success" => true]);
    exit;
}

$types  .= "i";
$values[] = $id;

$sql  = "UPDATE users SET " . implode(", ", $setParts) . " WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao atualizar: " . $stmt->error]);
}

$stmt->close();
$conn->close();