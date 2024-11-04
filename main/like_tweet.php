<?php
session_start();

if (!isset($_SESSION["userId"])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION["userId"];
$tweetId = filter_input(INPUT_POST, 'tweetId', FILTER_VALIDATE_INT);

if (!$tweetId) {
    die("ID de tweet no válido.");
}

$servername = "localhost:3306";
$username = "root";
$password = "root";
$dbname = "social_network";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar si el usuario ya dio "Me gusta" al tweet
$stmt = $conn->prepare("SELECT * FROM likes WHERE userId = ? AND tweetId = ?");
$stmt->bind_param("ii", $userId, $tweetId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Eliminar el "Me gusta" y reducir el contador
    $stmtDelete = $conn->prepare("DELETE FROM likes WHERE userId = ? AND tweetId = ?");
    $stmtDelete->bind_param("ii", $userId, $tweetId);
    $stmtDelete->execute();
    $stmtDelete->close();

    // Reducir el contador de "Me gusta" en la tabla publications
    $stmtUpdate = $conn->prepare("UPDATE publications SET likes = likes - 1 WHERE id = ?");
    $stmtUpdate->bind_param("i", $tweetId);
    $stmtUpdate->execute();
    $stmtUpdate->close();
} else {
    // Registrar el "Me gusta" en la tabla de likes
    $stmtInsert = $conn->prepare("INSERT INTO likes (userId, tweetId) VALUES (?, ?)");
    $stmtInsert->bind_param("ii", $userId, $tweetId);
    $stmtInsert->execute();
    $stmtInsert->close();

    // Incrementar el contador de "Me gusta" en la tabla publications
    $stmtUpdate = $conn->prepare("UPDATE publications SET likes = likes + 1 WHERE id = ?");
    $stmtUpdate->bind_param("i", $tweetId);
    $stmtUpdate->execute();
    $stmtUpdate->close();
}

$stmt->close();
$conn->close();

header("Location: welcome.php");
exit();
?>