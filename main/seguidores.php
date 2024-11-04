<?php
session_start();
require_once("../scripts/connection.php");

// Verificar si el usuario ha iniciado sesión
if (isset($_SESSION['userId'])) {
    $user_id = $_SESSION['userId'];
    $user_id = mysqli_real_escape_string($connect, $user_id);

    // Obtener los seguidores del usuario actual
    $sql = "SELECT u.id, u.username, u.email, u.description
            FROM users u
            JOIN follows f ON u.id = f.users_id
            WHERE f.userToFollowId = $user_id;";  // Obtenemos usuarios que siguen al usuario actual

    $query = mysqli_query($connect, $sql);
    
    if (!$query) {
        // Manejo de errores en caso de que la consulta falle
        echo "<div class='alert alert-danger'>Error en la consulta: " . mysqli_error($connect) . "</div>";
        exit();
    }
} else {
    $error_message = "No estás autenticado. Por favor inicia sesión.";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguidores</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Estilos adicionales */
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-8 p-5">
        <h1 class="text-center text-3xl font-bold mb-6">Seguidores</h1>

        <a href="../main/welcome.php" class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded-md shadow-md hover:bg-blue-700 transition duration-200">Volver al perfil</a>

        <?php if (isset($error_message)): ?>
            <div class="bg-yellow-200 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <div class="card bg-white rounded-lg shadow-md overflow-hidden transition duration-200">
                        <div class="p-4">
                            <h5 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($row['username']); ?></h5>
                            <h6 class="text-gray-600"><?php echo htmlspecialchars($row['email']); ?></h6>
                          
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
