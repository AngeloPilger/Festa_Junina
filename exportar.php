<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
include 'conexao.php';

// Definir cabeçalhos para download Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=vendas_festa_junina.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1'>";
echo "<tr><th>Usuário</th><th>Comida</th><th>Quantidade</th><th>Data</th></tr>";

$result = $mysqli->query("SELECT * FROM vendas ORDER BY data DESC");
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['usuario']) . "</td>";
    echo "<td>" . htmlspecialchars($row['comida']) . "</td>";
    echo "<td>" . $row['quantidade'] . "</td>";
    echo "<td>" . date('d/m/Y H:i', strtotime($row['data'])) . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
