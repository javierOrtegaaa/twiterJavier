<?php
session_start();
require_once("../scripts/connection.php");

if (isset($_SESSION['userId'])) {
    $user_id = $_SESSION['userId'];
    $user_id = mysqli_real_escape_string($connect, $user_id);

    // Obtener los usuarios a los que sigue el usuario actual
    $sql = "SELECT u.id, u.username, u.email, u.description,
                   'Dejar de seguir' AS follow_action
            FROM users u
            JOIN follows f ON u.id = f.userToFollowId
            WHERE f.users_id = $user_id;";

    $query = mysqli_query($connect, $sql);

    // Manejar la acción de dejar de seguir
    if (isset($_POST['unfollow'])) {
        $unfollowId = intval($_POST['unfollow']);
        $delete_sql = "DELETE FROM follows WHERE users_id = $user_id AND userToFollowId = $unfollowId";
        mysqli_query($connect, $delete_sql);
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
    <title>Siguiendo</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-8 p-5">
        <h1 class="text-center text-3xl font-bold mb-6 text-gray-800">Siguiendo</h1>

        <!-- Botón de volver al perfil -->
        <a href="../main/welcome.php" class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded-md shadow-md hover:bg-blue-700 transition duration-200">Volver a la página principal</a>

        <?php if (isset($error_message)): ?>
            <div class="bg-yellow-200 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php
                if (!$query) {
                    echo "<div class='bg-red-200 border-l-4 border-red-500 text-red-700 p-4' role='alert'>Error en la consulta: " . mysqli_error($connect) . "</div>";
                } else {
                    while ($row = mysqli_fetch_assoc($query)) {
                        echo "<div class='card bg-white rounded-lg shadow-md overflow-hidden transition duration-200'>";
                        echo "<div class='p-4'>";
                        echo "<h5 class='text-xl font-semibold text-gray-800'>" . htmlspecialchars($row['username']) . "</h5>";
                        echo "<h6 class='text-gray-600'>" . htmlspecialchars($row['email']) . "</h6>";
                       

                        // Botón de dejar de seguir
                        echo "<form method='post' action=''>
                                <input type='hidden' name='unfollow' value='" . $row['id'] . "'>
                                <button
                                <button type='submit' class='mt-4 px-4 py-2 bg-red-600 text-white rounded-md'>Dejar de seguir</button>
                              </form>";
                        
                        echo "</div>"; // Cierre del div .p-4
                        echo "</div>"; // Cierre del div .card
                    }
                }
                ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
