<?php
require_once 'db.php'; // conecta

$data = json_decode(file_get_contents("php://input"), true); // pega os dados:

$adId        = isset($data["adId"])      ? (int)$data["adId"] : null;
$userId      = isset($data["userId"])    ? (int)$data["userId"] : null;
$title       = trim($data["title"]       ?? "");
$description = trim($data["description"] ?? "");
$price       = isset($data["price"])     ? (float)$data["price"] : null;
$stock       = isset($data["stock"])     ? (int)$data["stock"] : 0;
$type        = trim($data["type"]        ?? "");
$category    = trim($data["category"]    ?? "");
$image       = isset($data["image"])     ? $data["image"] : null;

if (!$adId || !$userId || !$title || $price === null) {
    echo json_encode(["success" => false, "message" => "Dados insuficientes para atualizar."]); // nao veio todos os dados
    exit;
}

$validTypes      = ["Produto", "Servico"];                                                      // tipos e categorias válidas
$validCategories = ["Alimentos", "Informatica", "Roupas", "Cosmeticos", "Material_Escolar", "Outros"];

if (!in_array($type, $validTypes)) {
    echo json_encode(["success" => false, "message" => "Tipo invalido: $type"]); // se nn tiver o tipo ou a categoria como valido, sai
    exit;
}


if (!in_array($category, $validCategories)) {
    echo json_encode(["success" => false, "message" => "Categoria invalida: $category"]);
    exit;
}

$stmt = $conn->prepare("SELECT sellerId FROM ads WHERE id = ?"); // pega o id do vendedor pelo anuncio
$stmt->bind_param("i", $adId);
$stmt->execute();
$response = $stmt->get_result();

if ($response->num_rows === 0){
    echo json_encode(["success" => false, "message" => "Anúncio não encontrado."]); // se nn tiver achado o anuncio
    exit;
}

$ad = $response->fetch_assoc();

if ((int)$ad["sellerId"] !== $userId){
    echo json_encode(["sucess" => false, "message" => "Você não possui permissão para editar o anúncio"]); // o id do usuário é diferente do vendedor 
    exit;
}

$stmt->close();

if ($image !== null) {
    $stmtUp = $conn->prepare("UPDATE ads SET title = ?, description = ?, price = ?, stock = ?, type = ?, category = ?, image = ? WHERE id = ?");
    $stmtUp->bind_param("ssdisssi", $title, $description, $price, $stock, $type, $category, $image, $adId);
}
else {
    $stmtUp = $conn->prepare("UPDATE ads SET title = ?, description = ?, price = ?, stock = ?, type = ?, category = ? WHERE id = ?");
    $stmtUp->bind_param("ssdissi", $title, $description, $price, $stock, $type, $category, $adId);
}


if ($stmtUp->execute()) {
    echo json_encode([
        "success" => true, "message" => "Anúncio editado com sucesso!" ]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao editar anúncio " . $stmt->error]);
}

$stmtUp->close();
$conn->close();