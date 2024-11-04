<?php
// Incluir el archivo de conexión a la base de datos
require_once '../scripts/connection.php'; 

// Inicializar variable para los resultados
$users = [];

// Verificar si se ha enviado el nombre de usuario
if (isset($_POST['username'])) {
    $username = $_POST['username'];

    // Proteger contra inyecciones SQL
    $username = mysqli_real_escape_string($connect, $username);

    // Realizar la consulta en la base de datos
    $sql = "SELECT * FROM users WHERE username LIKE '%$username%'";
    $result = mysqli_query($connect, $sql);

    // Verificar si la consulta devolvió resultados
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row; // Guardar los resultados en un array
        }
    } else {
        echo "Error en la consulta SQL: " . mysqli_error($connect);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="../scripts/buscar.js" defer></script>
    <title>Buscar Usuario</title>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 p-5">
        <h1 class="text-3xl font-bold text-center mb-6">Buscar Usuario</h1>

        <div class="mb-4">
            <label for="username" class="block text-sm font-medium text-gray-700">Nombre de usuario:</label>
            <input type="text" id="username" name="username" required class="mt-1 block w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" onkeyup="searchUser(this.value)">
        </div>

        <div id="results" class="mt-3 space-y-4"></div>
        
        <a href="../main/welcome.php" class="mt-3 inline-block bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Volver</a>
    </div>
    <script>
    function searchUser(username) {
    if (username.length === 0) {
        document.getElementById("results").innerHTML = ""; // Limpiar resultados si no hay entrada
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "search_user.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onload = function() {
        if (this.status === 200) {
            document.getElementById("results").innerHTML = this.responseText; // Mostrar resultados
        } else {
            console.error("Error en la búsqueda:", this.statusText);
        }
    };

    xhr.send("username=" + encodeURIComponent(username));
}
</script>
</body>
</html>
