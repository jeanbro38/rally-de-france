<?php
$host = "localhost";
$user = "fggjwoia_rally";
$pass = "gLYyV8hVW2j5QSSsJffN";
$dbname = "fggjwoia_rally";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT UUIDrally, title FROM dance_rallies WHERE creator_uuid = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$rallies = [];
$total_views = 0;
$dates = [];

while ($row = $result->fetch_assoc()) {
    $UUIDrally = $row['UUIDrally'];
    $title = $row['title'];

    // Get total views for this rally
    $stmt_views = $conn->prepare("SELECT COUNT(*) AS views FROM rally_views WHERE rally_id = ?");
    $stmt_views->bind_param("s", $UUIDrally);
    $stmt_views->execute();
    $views_result = $stmt_views->get_result();
    $views_row = $views_result->fetch_assoc();
    $views = $views_row['views'] ?? 0;

    $total_views += $views;

    $stmt_yearly = $conn->prepare("
    SELECT DATE_FORMAT(view_date, '%Y') AS year, COUNT(*) AS yearly_views 
    FROM rally_views 
    WHERE rally_id = ? 
    GROUP BY year");
    $stmt_yearly->bind_param("s", $UUIDrally);
    $stmt_yearly->execute();
    $daily_result = $stmt_yearly->get_result();

    $views_per_year = [];
    while ($year_row = $stmt_yearly->get_result()->fetch_assoc()) {
    $views_per_year[$year_row['year']] = (int)$year_row['yearly_views'];
    if (!in_array($year_row['year'], $dates)) {
        $dates[] = $year_row['year'];
    }
}

    $rallies[] = [
        "UUIDrally" => $UUIDrally,
        "title" => $title,
        "views" => $views,
        "views_per_year" => $views_per_year
    ];
}

$stmt->close();
$conn->close();

sort($dates);

echo json_encode([
    "total_views" => $total_views,
    "rallies" => $rallies,
    "dates" => $dates
]);
?>