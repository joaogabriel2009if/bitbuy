<?php
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$login    = trim($data["emailOrUsername"] ?? "");
$password = $data["password"] ?? "";

if (!$login || !$password) {
    echo json_encode(["success" => false, "message" => "Dados incompletos."]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1");
$stmt->bind_param("ss", $login, $login);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        unset($user['password']);
        $user['id']           = (string)$user['id'];
        $user['profilePhoto'] = $user['profilePhoto'] ?? "";
        $user['contact']      = $user['contact'] ?? "";
        echo json_encode(["success" => true, "user" => $user]);
    } else {
        echo json_encode(["success" => false, "message" => "Senha incorreta."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Usuário não encontrado."]);
}

$stmt->close();
$conn->close();