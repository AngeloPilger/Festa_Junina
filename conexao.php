<?php
$mysqli = new mysqli("localhost", "root", "", "junina");
if ($mysqli->connect_errno) {
    echo "Falha na conexão: " . $mysqli->connect_error;
    exit();
}
?>