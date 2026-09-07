<?php
require_once 'db.php'; // conecta

$data = json_decode(file_get_contents("php://input"), true); // puxa os dados

$adId   = isset($data["adId"])   ? (int)$data["adId"]   : null; // pega o id do anúncio
$userId = isset($data["userId"]) ? (int)$data["userId"] : null; // pega o id do usuário

if (!$adId || !$userId) { 
    echo json_encode(["success" => false, "message" => "Dados insuficientes."]); // se não tiver os dois sai
    exit;
}

$stmt = $conn->prepare("SELECT sellerId FROM ads WHERE id = ?"); // pega o id de quem vende o anúncio - ta como ? pq conecta em baixo//
$stmt->bind_param("i", $adId); // da o parametro da interrogacao com o adId
$stmt->execute();                  // executa
$result = $stmt->get_result(); // pega a resposta

if ($result->num_rows === 0) {
    echo json_encode(["sucess" => false, "message" => "Anúncio não encontrado"]); // não achou o anúncio pq nn tem linhas com o adId
    exit;
}

$ad = $result->fetch_assoc();

if ((int)$ad["sellerId"] !== $userId){
    echo json_encode(["sucess" => false, "message" => "Você não possui permissão para excluir o anúncio"]); // o id do usuário é diferente do vendedor 
    exit;
}

$stmt->close(); // fecha a conexao pra abrir a proxima

$stmtRev = $conn->prepare("DELETE FROM reviews WHERE adid = ?"); // deleta as avaliacoes do anuncio, msm esquema de ? la de cima
$stmtRev->bind_param("i", $adId); // da o id pra executar
$stmtRev->execute(); // executa
$stmtRev->close(); // fecha

$stmtDel = $conn->prepare("DELETE From ads WHERE id = ?"); // deleta o anuncio do banco de dados
$stmtDel->bind_param("i", $adId); // da o id pra executar

if ($stmtDel->execute()) {
    echo json_encode(["success" => true, "message" => "Excluído com sucesso."]); // excluiu de tudo
} else {
    echo json_encode(["success" => false, "message" => "Erro ao excluir anúncio: " . $stmtDel->error]); // deu erro
}

$stmtDel->close(); // fecha a conexao
$conn->close();