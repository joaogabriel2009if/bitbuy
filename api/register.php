<?php
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$username    = trim($data["username"]    ?? "");
$email       = trim($data["email"]       ?? "");
$password    = trim($data["password"]    ?? "");
$school      = trim($data["school"]      ?? "");
$accountType = trim($data["accountType"] ?? "");
$contact     = trim($data["contact"]     ?? "");

if (!$username || !$email || !$password || !$school || !$accountType) {
    echo json_encode(["success" => false, "message" => "Preencha todos os campos obrigatórios."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "E-mail inválido."]);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(["success" => false, "message" => "A senha deve ter pelo menos 6 caracteres."]);
    exit;
}

if (!in_array($accountType, ["vendedor", "comprador"])) {
    echo json_encode(["success" => false, "message" => "Tipo de conta inválido."]);
    exit;
}

if ($accountType === "vendedor" && !$contact) {
    echo json_encode(["success" => false, "message" => "Contato é obrigatório para vendedor."]);
    exit;
}

// Verifica duplicados
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Usuário ou e-mail já cadastrado."]);
    $stmt->close();
    exit;
}
$stmt->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, email, password, school, accountType, contact) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $username, $email, $hashedPassword, $school, $accountType, $contact);

if ($stmt->execute()) {
    $userId = $conn->insert_id;
    echo json_encode([
        "success" => true,
        "user" => [
            "id"          => (string)$userId,
            "username"    => $username,
            "email"       => $email,
            "school"      => $school,
            "accountType" => $accountType,
            "contact"     => $contact,
            "profilePhoto"=> ""
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao inserir usuário: " . $stmt->error]);
}

$stmt->close();
$conn->close();