<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $comida = $_POST['comida'];
    $quantidade = $_POST['quantidade'];
    $usuario = $_SESSION['usuario'];

    $stmt = $mysqli->prepare("INSERT INTO vendas (usuario, comida, quantidade) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $usuario, $comida, $quantidade);
    $stmt->execute();
}

$comidas = $_SESSION['comidas'];
$icones = [
    "Pamonha" => "🌽", "Canjica" => "🥣", "Milho Cozido" => "🌽",
    "Bolo de Fubá" => "🍰", "Arroz Doce" => "🍚", "Maçã do Amor" => "🍎",
    "Pé-de-moleque" => "🍬", "Cocada" => "🥥"
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Registrar Venda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
    <div class="container">
        <h3 class="mb-3">🎯 Registrar Venda</h3>
        <form method="POST">
            <div class="mb-2">
                <label>Comida:</label>
                <select name="comida" class="form-select" required>
                    <?php foreach ($comidas as $c): ?>
                        <option value="<?= $c ?>"><?= $icones[$c] ?? '' ?> <?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-2">
                <label>Quantidade:</label>
                <input type="number" name="quantidade" class="form-control" min="1" required>
            </div>
            <button class="btn btn-primary">Registrar</button>
            <a href="logout.php" class="btn btn-danger">Sair</a>
            <a href="relatorio.php" class="btn btn-secondary">Ver Relatório</a>
        </form>
    </div>
</body>
</html>
