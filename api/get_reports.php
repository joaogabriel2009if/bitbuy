<?php
require_once 'db.php';

requireAdmin($conn, $_GET["adminId"] ?? 0);

$sql = "SELECT adId, COUNT(*) AS total, MAX(createdAt) AS lastAt,
               GROUP_CONCAT(username SEPARATOR ', ') AS reporters
        FROM reports
        GROUP BY adId
        ORDER BY lastAt DESC";
$res = $conn->query($sql);
$reports = [];

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $reports[] = [
            "adId"      => (string)$row["adId"],
            "total"     => (int)$row["total"],
            "lastAt"    => $row["lastAt"],
            "reporters" => $row["reporters"]
        ];
    }
    echo json_encode(["success" => true, "reports" => $reports]);
} else {
    echo json_encode(["success" => false, "message" => "Erro: " . $conn->error]);
}

$conn->close();
