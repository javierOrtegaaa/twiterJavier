<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["userId"])) {
    header("Location: login.php");
    exit();
}

// Conectar a la base de datos
$servername = "localhost:3306";
$username = "root";  
$password = "root";  
$dbname = "social_network";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener IDs de seguimiento y seguido
$followerId = intval($_POST['follower_id']);
$followedId = intval($_POST['followed_id']);

// Verificar si ya se está siguiendo
$stmt = $conn->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ? AND followed_id = ?");
$stmt->bind_param("ii", $followerId, $followedId);
$stmt->execute();
$stmt->bind_result($isFollowing);
$stmt->fetch();
$stmt->close();

if ($isFollowing) {
    // Si ya se sigue, eliminar la relación
    $stmt = $conn->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->bind_param("ii", $followerId, $followedId);
    $stmt->execute();
    $stmt->close();
} else {
    // Si no se sigue, agregar la relación
    $stmt = $conn->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $followerId, $followedId);
    $stmt->execute();
    $stmt->close();
}

// Redirigir de vuelta al perfil
header("Location: perfil.php?id=" . $followedId);
exit();
