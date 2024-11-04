<?php
session_start();
require_once("../scripts/connection.php");

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario que ha iniciado sesión
$user_id = $_SESSION['userId'];

// Obtener el ID del usuario cuyo perfil se está viendo
$follow_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

// Verificar si se proporciona un ID de usuario
if ($follow_user_id === null) {
    die("Error: No se ha proporcionado un ID de usuario.");
}

// Obtener información del usuario a seguir
$sql_follow_user = "SELECT username, email, description FROM users WHERE id = ?";
$stmt_follow_user = $connect->prepare($sql_follow_user);
$stmt_follow_user->bind_param("i", $follow_user_id);
$stmt_follow_user->execute();
$result_follow_user = $stmt_follow_user->get_result();
$follow_user = $result_follow_user->fetch_assoc();
$stmt_follow_user->close();

if (!$follow_user) {
    die("Error: Usuario no encontrado.");
}

// Comprobar si ya sigues al usuario
$sql_check_follow = "SELECT * FROM follows WHERE users_id = ? AND userToFollowId = ?";
$stmt_check_follow = $connect->prepare($sql_check_follow);
$stmt_check_follow->bind_param("ii", $user_id, $follow_user_id);
$stmt_check_follow->execute();
$stmt_check_follow->store_result();
$following = $stmt_check_follow->num_rows > 0;
$stmt_check_follow->close();

// Manejar la acción de seguir o dejar de seguir
$message = '';
if (isset($_POST['follow_user'])) {
    if ($following) {
        // Si ya sigues al usuario, lo dejamos de seguir
        $sql_unfollow = "DELETE FROM follows WHERE users_id = ? AND userToFollowId = ?";
        $stmt_unfollow = $connect->prepare($sql_unfollow);
        $stmt_unfollow->bind_param("ii", $user_id, $follow_user_id);
        if ($stmt_unfollow->execute()) {
            $message = "Has dejado de seguir a " . htmlspecialchars($follow_user['username']) . ".";
        } else {
            $message = "Error al dejar de seguir al usuario.";
        }
        $stmt_unfollow->close();
    } else {
        // Si no sigues al usuario, lo seguimos
        $sql_follow = "INSERT INTO follows (users_id, userToFollowId) VALUES (?, ?)";
        $stmt_follow = $connect->prepare($sql_follow);
        $stmt_follow->bind_param("ii", $user_id, $follow_user_id);
        if ($stmt_follow->execute()) {
            $message = "Ahora sigues a " . htmlspecialchars($follow_user['username']) . ".";
        } else {
            $message = "Error al seguir al usuario.";
        }
        $stmt_follow->close();
    }

    // Volver a comprobar el estado después de seguir/dejar de seguir
    $stmt_check_follow = $connect->prepare($sql_check_follow);
    $stmt_check_follow->bind_param("ii", $user_id, $follow_user_id);
    $stmt_check_follow->execute();
    $stmt_check_follow->store_result();
    $following = $stmt_check_follow->num_rows > 0;
    $stmt_check_follow->close();
}

// Manejar el envío de mensajes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $message_text = mysqli_real_escape_string($connect, trim($_POST['message']));
    if (!empty($message_text)) {
        $sql_send_message = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
        $stmt_send_message = $connect->prepare($sql_send_message);
        $stmt_send_message->bind_param("iis", $user_id, $follow_user_id, $message_text);
        if ($stmt_send_message->execute()) {
            $success_message = "Mensaje enviado.";
        } else {
            $error_message = "Error al enviar el mensaje.";
        }
        $stmt_send_message->close();
    }
}

// Obtener los mensajes entre el usuario y el destinatario
$sql_messages = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY create_date ASC";
$stmt_messages = $connect->prepare($sql_messages);
$stmt_messages->bind_param("iiii", $user_id, $follow_user_id, $follow_user_id, $user_id);
$stmt_messages->execute();
$result_messages = $stmt_messages->get_result();
$messages = $result_messages->fetch_all(MYSQLI_ASSOC);
$stmt_messages->close();

// Obtener los tweets del usuario a seguir
$tweets = [];
$no_tweets_message = '';
if (isset($_POST['show_tweets'])) {
    $sql_tweets = "SELECT text, createDate FROM publications WHERE userId = ? ORDER BY createDate DESC";
    $stmt_tweets = $connect->prepare($sql_tweets);
    $stmt_tweets->bind_param("i", $follow_user_id);
    $stmt_tweets->execute();
    $result_tweets = $stmt_tweets->get_result();
    $tweets = $result_tweets->fetch_all(MYSQLI_ASSOC);
    $stmt_tweets->close();

    // Comprobar si no hay tweets
    if (empty($tweets)) {
        $no_tweets_message = "Este usuario no tiene tweets.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($follow_user['username']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f8; /* Fondo más claro */
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }
        .profile-header {
            background-color: #005f73; /* Color más oscuro y profesional */
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: relative; /* Para el botón de retroceso */
        }
        .back-button {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: transparent;
            border: none;
            cursor: pointer;
            color: white;
            font-size: 1.5rem;
            transition: color 0.3s;
        }
        .back-button:hover {
            color: #e0f7fa; /* Color más claro al pasar el mouse */
        }
        .profile-info {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .profile-item {
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        .btn-follow, .btn-show-tweets {
            display: inline-block;
            background-color: #008891; /* Color del botón de seguir */
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.3s;
            margin-top: 10px;
            font-weight: bold;
        }
        .btn-follow:hover, .btn-show-tweets:hover {
            background-color: #006f7f; /* Color más oscuro al pasar el mouse */
            transform: scale(1.05);
        }
        .chat-box {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 15px;
            overflow-y: auto;
            height: 300px;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .message {
            margin-bottom: 10px;
            border-radius: 8px;
            padding: 8px;
            color: #333;
        }
        .sent {
            background-color: #d9fdd3; /* Verde claro para mensajes enviados */
            text-align: right;
        }
        .received {
            background-color: #f0f9ff; /* Azul claro para mensajes recibidos */
            text-align: left;
        }
        .tweet-box {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
       
        .tweet {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .no-tweets {
            margin-top: 20px;
            color: #555;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($follow_user['username']); ?></h1>
            
            <?php if (!empty($follow_user['description'])): ?>
                <p class="mt-2"><?php echo htmlspecialchars($follow_user['description']); ?></p>
            <?php else: ?>
                <p class="mt-2">El usuario no tiene descripción.</p>
            <?php endif; ?>

            <p class="mt-2"><?php echo htmlspecialchars($follow_user['email']); ?></p>
            <form method="POST" class="mt-4">
                <button type="submit" name="follow_user" class="btn-follow"><?php echo $following ? "Dejar de seguir" : "Seguir"; ?></button>
            </form>

            <!-- Botón de retroceso -->
            <a href="javascript:history.back()" class="back-button" title="Volver atrás">&larr;</a>
        </div>

        <div class="profile-info">
            <h2 class="text-2xl font-bold">Mensajes</h2>
            <div class="chat-box">
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?php echo $msg['sender_id'] === $user_id ? 'sent' : 'received'; ?>">
                        <strong><?php echo $msg['sender_id'] === $user_id ? 'Tú' : htmlspecialchars($follow_user['username']); ?>:</strong>
                        <?php echo htmlspecialchars($msg['message']); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <form method="POST" class="mt-4">
                <textarea name="message" rows="3" class="w-full p-2 border rounded" placeholder="Escribe tu mensaje aquí..."></textarea>
                <button type="submit" name="send_message" class="btn-follow mt-2">Enviar Mensaje</button>
            </form>
        </div>

        <div class="profile-info">
            <h2 class="text-2xl font-bold">Tweets</h2>
            <form method="POST" class="mt-4">
                <button type="submit" name="show_tweets" class="btn-show-tweets">Mostrar Tweets</button>
            </form>
            <div class="tweet-box">
                <?php if (!empty($tweets)): ?>
                    <?php foreach ($tweets as $tweet): ?>
                        <div class="tweet">
                            <p><strong><?php echo htmlspecialchars($follow_user['username']); ?></strong>: <?php echo htmlspecialchars($tweet['text']); ?></p>
                            <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($tweet['createDate']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-tweets"><?php echo $no_tweets_message; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
