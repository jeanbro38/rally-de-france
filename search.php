<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
session_start();

// Connexion à la base de données
$host = "localhost";
$user = "fggjwoia_rally";
$pass = "gLYyV8hVW2j5QSSsJffN";
$dbname = "fggjwoia_rally";

$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset("utf8mb4");

$user_id = $_SESSION['user_id'] ?? null;

$sql = "SELECT UUIDrally, photos, description, title, location, dress_code, entry_fee, music_type, latitude, longitude FROM dance_rallies";

if (!empty($_POST['query'])) {
    $query = trim($_POST['query']);
    $sql .= " WHERE title LIKE ? OR location LIKE ? OR description LIKE ? OR music_type LIKE ?";
}

$stmt = $conn->prepare($sql);

if (!empty($_POST['query'])) {
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
}

$stmt->execute();
$result = $stmt->get_result();

$rallies = [];
while ($row = $result->fetch_assoc()) {
    $rallies[] = $row;

    if ($user_id) {
        $stmt2 = $conn->prepare("INSERT IGNORE INTO rally_views (rally_id, user_id) VALUES (?, ?)");
        $stmt2->bind_param("si", $row['UUIDrally'], $user_id);
        $stmt2->execute();
        $stmt2->close();
    }
}

$stmt->close();
$result->free();
$conn->close();

echo json_encode($rallies);
?>
