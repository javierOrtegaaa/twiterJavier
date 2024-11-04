<?php
require_once 'connection.php'; 

if (isset($_GET['query'])) {
    $query = mysqli_real_escape_string($connect, $_GET['query']);
    $sql = "SELECT username FROM users WHERE username LIKE '%$query%'";
    $result = mysqli_query($connect, $sql);

    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row['username'];
    }
    echo json_encode($users);
}
?>
