<?php
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$title          = trim($data["title"]          ?? "");
$description    = trim($data["description"]    ?? "");
$price          = isset($data["price"])        ? (float)$data["price"] : null;
$stock          = isset($data["stock"])        ? (int)$data["stock"]   : 0;
$type           = trim($data["type"]           ?? "");
$category       = trim($data["category"]       ?? "");
$image          = $data["image"]               ?? "";
$sellerId       = isset($data["sellerId"])     ? (int)$data["sellerId"] : null;
$sellerUsername = trim($data["sellerUsername"] ?? "");

if (!$title || $price === null || !$sellerId || !$sellerUsername) {
    echo json_encode(["success" => false, "message" => "Dados insuficientes."]);
    exit;
}

$validTypes      = ["Produto", "Servico"];
$validCategories = ["Alimentos", "Informatica", "Roupas", "Cosmeticos","Material_Escolar", "Outros"];

if (!in_array($type, $validTypes)) {
    echo json_encode(["success" => false, "message" => "Tipo invalido: $type"]);
    exit;
}


if (!in_array($category, $validCategories)) {
    echo json_encode(["success" => false, "message" => "Categoria invalida: $category"]);
    exit;
}

$createdAt = date('Y-m-d H:i:s');

// 10 parametros: i s s s d i s s s s
$stmt = $conn->prepare(
    "INSERT INTO ads (sellerId, sellerUsername, title, description, price, stock, type, category, image, createdAt)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("isssdissss", $sellerId, $sellerUsername, $title, $description, $price, $stock, $type, $category, $image, $createdAt);

if ($stmt->execute()) {
    $id = $conn->insert_id;
    echo json_encode([
        "success" => true,
        "newAd"   => [
            "id"             => (string)$id,
            "sellerId"       => (string)$sellerId,
            "sellerUsername" => $sellerUsername,
            "title"          => $title,
            "description"    => $description,
            "price"          => $price,
            "stock"          => $stock,
            "type"           => $type,
            "category"       => $category,
            "image"          => $image,
            "createdAt"      => $createdAt,
            "reviews"        => []
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao salvar: " . $stmt->error]);
}

$stmt->close();
$conn->close();