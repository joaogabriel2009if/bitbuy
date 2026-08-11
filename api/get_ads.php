<?php
require_once 'db.php';

$sql = "SELECT * FROM ads ORDER BY createdAt DESC";
$res = $conn->query($sql);
$ads = [];

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $adId = (int)$row['id'];

        $revStmt = $conn->prepare("SELECT * FROM reviews WHERE adId = ? ORDER BY date DESC");
        $revStmt->bind_param("i", $adId);
        $revStmt->execute();
        $revRes  = $revStmt->get_result();
        $reviews = [];

        if ($revRes) {
            while ($revRow = $revRes->fetch_assoc()) {
                $reviews[] = [
                    "id"       => (string)$revRow['id'],
                    "userId"   => (string)$revRow['userId'],
                    "username" => $revRow['username'],
                    "rating"   => (int)$revRow['rating'],
                    "comment"  => $revRow['comment'] ?? "",
                    "date"     => $revRow['date']
                ];
            }
        }
        $revStmt->close();

        $ads[] = [
            "id"             => (string)$row['id'],
            "sellerId"       => (string)$row['sellerId'],
            "sellerUsername" => $row['sellerUsername'],
            "title"          => $row['title'],
            "description"    => $row['description'] ?? "",
            "price"          => (float)$row['price'],
            "stock"          => isset($row['stock']) ? (int)$row['stock'] : 0,
            "type"           => $row['type'] === "Servico" ? "Serviço" : $row['type'],
            "category"       => $row['category'],
            "image"          => $row['image'] ?? "",
            "createdAt"      => $row['createdAt'],
            "reviews"        => $reviews
        ];
    }
    echo json_encode(["success" => true, "ads" => $ads]);
} else {
    echo json_encode(["success" => false, "ads" => [], "message" => "Erro ao buscar anúncios: " . $conn->error]);
}

$conn->close();