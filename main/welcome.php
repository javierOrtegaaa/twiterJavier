<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["userId"])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost:3306";
$username = "root";  
$password = "root";  
$dbname = "social_network";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION["userId"]; 
    $content = $_POST['content'];

    $stmt = $conn->prepare("INSERT INTO publications (userId, text, createDate) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $userId, $content);

    if ($stmt->execute()) {
        echo "<p class='bg-green-100 text-green-800 p-3 rounded-md mb-4'>Tweet enviado exitosamente.</p>";
    } else {
        echo "<p class='bg-red-100 text-red-800 p-3 rounded-md mb-4'>Error al enviar el tweet: " . htmlspecialchars($stmt->error) . "</p>";
    }

    $stmt->close();
}

// Obtener el ID del usuario actual
$userId = $_SESSION["userId"];

// Consultar los tweets de las personas que sigues
$sqlFollowing = "SELECT p.*, u.username, u.id AS userId, 
                       (SELECT COUNT(*) FROM likes WHERE tweetId = p.id) AS likeCount,
                       (SELECT COUNT(*) FROM likes WHERE userId = ? AND tweetId = p.id) AS userLiked
                FROM follows f
                INNER JOIN publications p ON f.userToFollowId = p.userId
                INNER JOIN users u ON p.userId = u.id
                WHERE f.users_id = ? 
                ORDER BY p.createDate DESC"; 

$stmtFollowing = $conn->prepare($sqlFollowing);
$stmtFollowing->bind_param("ii", $userId, $userId);
$stmtFollowing->execute();
$resultFollowing = $stmtFollowing->get_result();

// Consultar tweets relevantes
$sqlForYou = "SELECT p.*, u.username, u.id AS userId, 
                      (SELECT COUNT(*) FROM likes WHERE tweetId = p.id) AS likeCount,
                      (SELECT COUNT(*) FROM likes WHERE userId = ? AND tweetId = p.id) AS userLiked
               FROM publications p 
               INNER JOIN users u ON p.userId = u.id 
               ORDER BY p.createDate DESC";

$stmtForYou = $conn->prepare($sqlForYou);
$stmtForYou->bind_param("i", $userId);
$stmtForYou->execute();
$resultForYou = $stmtForYou->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Bienvenida</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .fade-in { opacity: 0; transform: translateY(10px); animation: fadeIn 0.5s forwards; }
        @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
        .dropdown-menu { display: none; position: absolute; left: 0; margin-top: 0.5rem; background-color: white; border-radius: 0.5rem; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1); z-index: 10; }
        .dropdown-menu.show { display: block; }
        .dropdown-menu a { padding: 0.75rem 1rem; color: #4a5568; transition: background-color 0.2s; }
        .dropdown-menu a:hover { background-color: #edf2f7; }
    </style>
</head>

<body class="bg-gray-50 font-sans flex flex-col items-center p-5">
    <header class="w-full max-w-6xl mx-auto mb-8">
        <h1 class="text-3xl font-bold text-center text-blue-700 fade-in">Bienvenido</h1>
    </header>

    <div class="flex w-full max-w-6xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Barra lateral -->
        <aside class="w-1/4 p-5 border-r border-gray-200 bg-gray-50">
            <ul class="space-y-4">
                <li class="dropdown relative">
                    <button id="menuButton" class="w-full text-left text-blue-700 font-semibold hover:text-blue-800 focus:outline-none flex items-center">
                        <i class="fas fa-bars mr-2"></i> Opciones
                    </button>
                    <ul class="dropdown-menu rounded-lg mt-2">
                        <li><a href="../main/miperfil.php?id=<?php echo htmlspecialchars($userId); ?>" class="block hover:bg-blue-50">Perfil</a></li>
                        <li><a href="../main/buscar.php" class="block hover:bg-blue-50">Buscar Usuario</a></li>
                        <li><a href="../main/siguiendo.php" class="block hover:bg-blue-50">Siguiendo</a></li>
                        <li><a href="../main/seguidores.php" class="block hover:bg-blue-50">Seguidores</a></li>
                        <li><a href="../index.php" class="block text-red-500 hover:bg-red-50">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </aside>

        <!-- Contenido principal -->
        <main class="w-3/4 p-5">
            <section class="mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Subir Tweet</h2>
                <form id="tweetForm" action="../main/welcome.php" method="POST" class="mb-6">
                    <textarea name="content" id="content" rows="3" placeholder="Escribe tu tweet..." required
                        class="w-full border border-gray-300 rounded-lg p-3 mb-2 text-gray-700 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    <button type="submit" class="bg-blue-600 text-white py-2 px-5 rounded-lg hover:bg-blue-700 transition-all duration-300">Tweetear</button>
                </form>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Tweets Recientes de Personas que Sigues</h2>
                <div id="recentTweets" class="space-y-4">
                    <?php if ($resultFollowing->num_rows > 0) {
                        while ($row = $resultFollowing->fetch_assoc()) {
                            echo "<div class='tweet p-4 border border-gray-200 rounded-lg bg-gray-50 transition-all duration-300 hover:shadow-md'>";
                            echo "<p class='font-semibold'><a href='../main/perfil.php?user_id=" . htmlspecialchars($row['userId']) . "' class='text-blue-600 hover:underline'>" . htmlspecialchars($row['username']) . "</a>:</p>";
                            echo "<p class='text-gray-700'>" . htmlspecialchars($row['text']) . "</p>";
                            echo "<small class='text-gray-400'>" . htmlspecialchars($row['createDate']) . "</small>";
                            // Botón de "Me gusta"
                            echo "<form method='post' action='../main/like_tweet.php' class='mt-2'>";
                            echo "<input type='hidden' name='tweetId' value='" . htmlspecialchars($row['id']) . "'>";
                            echo "<button type='submit' class='text-blue-500 hover:underline'>";
                            echo $row['userLiked'] > 0 ? "❤️ " . htmlspecialchars($row['likeCount']) : "🤍 " . htmlspecialchars($row['likeCount']);
                            echo "</button>";
                            echo "</form>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p class='text-gray-500'>No hay tweets recientes de personas que sigues.</p>";
                    } ?>
                </div>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Tweets Relevantes para Ti</h2>
                <div id="relevantTweets" class="space-y-4">
                <?php if ($resultForYou->num_rows > 0) {
                        while ($row = $resultForYou->fetch_assoc()) {
                            echo "<div class='tweet p-4 border border-gray-200 rounded-lg bg-gray-50 transition-all duration-300 hover:shadow-md'>";
                            echo "<p class='font-semibold'><a href='../main/perfil.php?user_id=" . htmlspecialchars($row['userId']) . "' class='text-blue-600 hover:underline'>" . htmlspecialchars($row['username']) . "</a>:</p>";
                            echo "<p class='text-gray-700'>" . htmlspecialchars($row['text']) . "</p>";
                            echo "<small class='text-gray-400'>" . htmlspecialchars($row['createDate']) . "</small>";
                            // Botón de "Me gusta"
                            echo "<form method='post' action='../main/like_tweet.php' class='mt-2'>";
                            echo "<input type='hidden' name='tweetId' value='" . htmlspecialchars($row['id']) . "'>";
                            echo "<button type='submit' class='text-blue-500 hover:underline'>";
                            echo $row['userLiked'] > 0 ? "❤️ " . htmlspecialchars($row['likeCount']) : "🤍 " . htmlspecialchars($row['likeCount']);
                            echo "</button>";
                            echo "</form>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p class='text-gray-500'>No hay tweets relevantes para ti.</p>";
                    } ?>
                </div>
            </section>
        </main>
    </div>

    <?php
    $conn->close(); // Cerrar la conexión al final
    ?>

    <!-- Script de JavaScript para manejar el menú desplegable -->
    <script>
        // Manejo del menú desplegable
        const menuButton = document.getElementById('menuButton');
        const dropdownMenu = document.querySelector('.dropdown-menu');

        menuButton.addEventListener('click', (event) => {
            event.stopPropagation(); // Evita el cierre al hacer clic en el menú
            dropdownMenu.classList.toggle('show'); // Alternar visibilidad del menú
        });

        // Cierra el menú si se hace clic fuera de él
        document.addEventListener('click', () => {
            dropdownMenu.classList.remove('show');
        });
    </script>
</body>
</html>
