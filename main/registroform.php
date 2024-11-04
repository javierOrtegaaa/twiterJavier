<?php

    if (isset($_POST["submit"])) {

        require_once("../scripts/connection.php");

        $username = mysqli_real_escape_string($connect, $_POST["username"]);
        $email = mysqli_real_escape_string($connect, $_POST["email"]);
        $password = mysqli_real_escape_string($connect, $_POST["password"]);

        if ($username && $username !== "" && $password && $password !== "" && $email && $email !== "") {
            $pass = password_hash($password, PASSWORD_BCRYPT, ["cost" => 4]);
            $sql = "INSERT INTO users(id, username, email, password, description, createDate) VALUES(null, '$username', '$email','$pass', null, curdate());";
            $guardar = mysqli_query($connect, $sql);

            if ($guardar) {
                header("Location: ../index.php");
            } else {
                header("Location: ../error/error.php");
            }
        }
    }