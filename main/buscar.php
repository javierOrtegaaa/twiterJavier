<?php
session_start();

require_once("../scripts/connection.php");

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

// Inicializar variable para los resultados
$results = [];

// Comprobar si hay un término de búsqueda
if (isset($_GET['search'])) {
    $search_term = $_GET['search'];
    $sql = "SELECT id, username FROM users WHERE username LIKE ?";
    $stmt = $connect->prepare($sql);
    $like_search_term = "%" . $search_term . "%";
    $stmt->bind_param("s", $like_search_term);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $results[] = $row; // Almacenar resultados
    }

    $stmt->close();
}

// Enviar los resultados como JSON para la búsqueda dinámica
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($results);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .search-container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .result-card {
            margin-top: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e3e3e3;
            border-radius: 8px;
            padding: 15px;
            background-color: #f9f9f9;
        }
        .result-card:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .no-results {
            display: none;
            text-align: center;
            color: #ff0000;
            font-weight: bold;
        }
        .loading {
            display: none;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Buscar Usuarios</h1>
        <div class="search-container">
            <form id="searchForm" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Buscar usuario..." id="searchInput" value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </form>

            <!-- Contenedor para mostrar resultados de búsqueda -->
            <div id="resultsContainer" class="mt-3"></div>
            <div id="noResults" class="no-results">No se encontraron usuarios.</div>
            <div id="loading" class="loading">
                <i class="fas fa-spinner fa-spin"></i> Cargando...
            </div>
        </div>

        <a href="../main/welcome.php" class="btn btn-secondary mt-4">Volver a la página principal</a>
    </div>

    <script>
        const searchForm = document.getElementById('searchForm');
        const resultsContainer = document.getElementById('resultsContainer');
        const noResults = document.getElementById('noResults');
        const loading = document.getElementById('loading');
        const searchInput = document.getElementById('searchInput');

        // Función para realizar la búsqueda
        function searchUsers() {
            const searchTerm = searchInput.value.trim();

            if (searchTerm.length === 0) {
                resultsContainer.innerHTML = ''; // Limpiar resultados si el campo está vacío
                noResults.style.display = 'none'; // Ocultar mensaje de "sin resultados"
                return;
            }

            loading.style.display = 'block'; // Mostrar mensaje de carga
            fetch('buscar.php?ajax=1&search=' + encodeURIComponent(searchTerm))
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = ''; // Limpiar resultados anteriores
                    loading.style.display = 'none'; // Ocultar mensaje de carga
                    noResults.style.display = 'none'; // Ocultar mensaje de "sin resultados"

                    if (data.length > 0) {
                        data.forEach(user => {
                            const userCard = document.createElement('div');
                            userCard.className = 'result-card';
                            userCard.innerHTML = `
                                <h5>${user.username}</h5>
                                <a href="perfil.php?user_id=${user.id}" class="btn btn-info">Ver Perfil</a>
                            `;
                            resultsContainer.appendChild(userCard);
                        });
                    } else {
                        noResults.style.display = 'block'; // Mostrar mensaje de "sin resultados"
                    }
                })
                .catch(error => {
                    console.error('Error en la búsqueda:', error);
                    loading.style.display = 'none'; // Ocultar mensaje de carga
                });
        }

        // Manejar el evento de entrada en el campo de búsqueda
        searchInput.addEventListener('input', function() {
            searchUsers(); // Llamar a la función de búsqueda
        });

        // Manejar el evento de envío del formulario
        searchForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevenir el envío normal del formulario
            searchUsers(); // Llamar a la función de búsqueda
        });
    </script>
</body>
</html>
