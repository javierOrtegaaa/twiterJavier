<?php
session_start();
require_once("../scripts/connection.php");

// Verificar si el usuario ha iniciado sesión
if (isset($_SESSION['userId'])) {
    $user_id = $_SESSION['userId'];
    $user_id = mysqli_real_escape_string($connect, $user_id);

    // Obtener los datos del perfil del usuario actual
    $sql = "SELECT username, email, description 
            FROM users 
            WHERE id = $user_id;";

    $query = mysqli_query($connect, $sql);

    if ($query && mysqli_num_rows($query) > 0) {
        $user_data = mysqli_fetch_assoc($query);
    } else {
        $error_message = "No se pudo obtener la información del perfil.";
    }

    // Manejar la actualización de la descripción
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_description'])) {
        $new_description = mysqli_real_escape_string($connect, trim($_POST['description']));

        // Actualizar la descripción en la base de datos
        $sql_update = "UPDATE users SET description = '$new_description' WHERE id = $user_id;";
        if (mysqli_query($connect, $sql_update)) {
            $user_data['description'] = $new_description; // Actualizar la descripción en la variable
            $success_message = "Descripción actualizada correctamente.";
        } else {
            $error_message = "Error al actualizar la descripción.";
        }
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
    <title>Mi Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Personalización del estilo */
        body {
            background-color: #f3f4f6; /* Color de fondo más suave */
        }
        .profile-card {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .profile-card:hover {
            transform: translateY(-5px);
        }
        /* Estilo mejorado para los botones */
        .btn-update, .btn-edit, .btn-cancel, .btn-back {
            background-color: #4F46E5; /* Color primario */
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-update:hover, .btn-edit:hover, .btn-cancel:hover, .btn-back:hover {
            background-color: #4338CA; /* Color más oscuro al pasar el mouse */
            transform: scale(1.05); /* Efecto de zoom */
        }
    </style>
    <script>
        function toggleEdit() {
            const editSection = document.getElementById('edit-section');
            editSection.classList.toggle('hidden');
        }
    </script>
</head>
<body>
    <div class="container mx-auto mt-8 p-5">
        <h1 class="text-center text-4xl font-bold mb-6 text-gray-800">Mi Perfil</h1>

        <?php if (isset($error_message)): ?>
            <div class="bg-yellow-200 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php elseif (isset($success_message)): ?>
            <div class="bg-green-200 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php else: ?>
            <div class="profile-card mx-auto max-w-lg text-center">
                <!-- Imagen de perfil -->
                <img src="https://via.placeholder.com/150" alt="Imagen de perfil" class="rounded-full mx-auto mb-4 w-32 h-32 object-cover border-4 border-indigo-500">
                <h2 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($user_data['username']); ?></h2>
                <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($user_data['email']); ?></p>
                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($user_data['description']); ?></p>

                <!-- Botón para editar la descripción -->
                <button onclick="toggleEdit()" class="btn-edit mb-4">Editar Descripción</button>

                <!-- Sección de edición oculta inicialmente -->
                <div id="edit-section" class="hidden mt-4">
                    <form method="post">
                        <textarea name="description" rows="3" class="w-full p-2 border border-gray-300 rounded-md" placeholder="Escribe tu nueva descripción aquí..." required><?php echo htmlspecialchars($user_data['description']); ?></textarea>
                        <div class="flex justify-between mt-2">
                            <button type="submit" name="update_description" class="btn-update">Guardar</button>
                            <button type="button" onclick="toggleEdit()" class="btn-cancel">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Botón de volver a la página principal -->
        <div class="text-center mt-6">
            <a href="../main/welcome.php" class="btn-back">Volver a la página principal</a>
        </div>
    </div>
</body>
</html>
