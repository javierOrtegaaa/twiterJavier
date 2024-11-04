<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once "connection.php";

    // Limpiar los datos del formulario
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // Preparar la consulta para evitar inyecciones SQL
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("s", $username);  // "s" es para string

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $usuario = $result->fetch_assoc();

        // Verificar la contraseña usando password_verify
        if (password_verify($password, $usuario["password"])) {
            // Guardar la información del usuario en la sesión
            $_SESSION["userId"] = $usuario["id"];
            $_SESSION["username"] = $usuario["username"];
            $_SESSION["email"] = $usuario["email"];

            // Redirigir a la página de bienvenida o perfil
            header("Location: ../main/welcome.php");
        } else {
            // Contraseña incorrecta
            header("Location: ../error/error.php");
        }
    } else {
        // Usuario no encontrado
        header("Location: ../error/error.php");
    }

    $stmt->close();
}
?>
