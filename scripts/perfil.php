<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["userId"])) {
    header("Location: login.php"); // Redirigir al inicio de sesión si no ha iniciado sesión
    exit();
}

$servername = "localhost:3306";
$username = "root";  
$password = "root";  
$dbname = "social_network";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el ID del usuario del perfil a mostrar
if (isset($_GET['userId'])) {
    $userId = intval($_GET['userId']);
} else {
    echo "ID de usuario no proporcionado.";
    exit();
}

// Consultar los detalles del usuario
$sql = "SELECT username, email, createDate FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Usuario no encontrado.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($user['username']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-5">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-5">
        <h2 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($user['username']); ?></h2>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Fecha de creación:</strong> <?php echo htmlspecialchars($user['createDate']); ?></p>
        <!-- Aquí puedes agregar más detalles del perfil si es necesario -->
        <a href="welcome.php" class="mt-4 inline-block bg-blue-500 text-white py-2 px-4 rounded">Volver</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>
