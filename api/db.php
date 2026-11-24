<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

$host   = "localhost";
$user   = "root";
$pass   = ""; // Coloque a senha do MySQL aqui, se houver
$dbname = "bitbuy_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Erro na conexão: " . $conn->connect_error]);
    exit;
}

$conn->set_charset("utf8mb4");

/* ============================================================
   Verifica se o id informado pertence a um usuário admin.
   Se não for, responde com erro e encerra o script (exit) —
   por isso os arquivos que chamam requireAdmin() não precisam
   checar o retorno: se a execução continuar, é porque é admin.
   ============================================================ */
function requireAdmin($conn, $adminId) {
    $adminId = (int)$adminId;

    if (!$adminId) {
        echo json_encode(["success" => false, "message" => "ID do administrador não fornecido."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT accountType FROM users WHERE id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Usuário não encontrado."]);
        $stmt->close();
        $conn->close();
        exit;
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row['accountType'] !== 'admin') {
        echo json_encode(["success" => false, "message" => "Acesso negado: requer permissão de administrador."]);
        $conn->close();
        exit;
    }
}
