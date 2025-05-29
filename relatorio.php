<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
include 'conexao.php';

$usuario = $_SESSION['usuario'];
$tipo = $_SESSION['tipo'];

// Preços e custos das comidas
$precos = [
    "Cachorro-quente" => 5,
    "Algodão doce" => 4,
    "Salgado no copo" => 3,
    "Churros" => 5,
    "Pipoca doce" => 3,
    "Pipoca salgada" => 3,
    "Amendoim cri-cri" => 2,
    "Maçã do amor" => 2,
    "Bolo" => 2,
    "Bebidas" => 2,
    "Pastel" => 2,
    "Crepe" => 2
];

$custos = [
    "Cachorro-quente" => 3,
    "Algodão doce" => 2,
    "Salgado no copo" => 1.5,
    "Churros" => 2,
    "Pipoca doce" => 1,
    "Pipoca salgada" => 1,
    "Amendoim cri-cri" => 1,
    "Maçã do amor" => 1,
    "Bolo" => 1,
    "Bebidas" => 1,
    "Pastel" => 1.5,
    "Crepe" => 1.5
];

// Barracas
$barracas = ['TerA', 'TerB', 'TerC', 'TerD', 'admin'];
$lista_comidas = array_keys($precos);

// Filtros
$filtro_usuario = $_GET['filtro_usuario'] ?? '';
$filtro_comida = $_GET['filtro_comida'] ?? '';

if ($filtro_usuario !== '' && !in_array($filtro_usuario, $barracas)) {
    $filtro_usuario = '';
}
if ($filtro_comida !== '' && !in_array($filtro_comida, $lista_comidas)) {
    $filtro_comida = '';
}

// SQL
if ($tipo !== 'admin') {
    if ($filtro_comida !== '') {
        $stmt = $mysqli->prepare("SELECT * FROM vendas WHERE usuario = ? AND comida = ? ORDER BY id DESC");
        $stmt->bind_param("ss", $usuario, $filtro_comida);
    } else {
        $stmt = $mysqli->prepare("SELECT * FROM vendas WHERE usuario = ? ORDER BY id DESC");
        $stmt->bind_param("s", $usuario);
    }
} elseif ($filtro_usuario !== '' && $filtro_comida !== '') {
    $stmt = $mysqli->prepare("SELECT * FROM vendas WHERE usuario = ? AND comida = ? ORDER BY id DESC");
    $stmt->bind_param("ss", $filtro_usuario, $filtro_comida);
} elseif ($filtro_usuario !== '') {
    $stmt = $mysqli->prepare("SELECT * FROM vendas WHERE usuario = ? ORDER BY id DESC");
    $stmt->bind_param("s", $filtro_usuario);
} elseif ($filtro_comida !== '') {
    $stmt = $mysqli->prepare("SELECT * FROM vendas WHERE comida = ? ORDER BY id DESC");
    $stmt->bind_param("s", $filtro_comida);
} else {
    $stmt = $mysqli->prepare("SELECT * FROM vendas ORDER BY id DESC");
}

$stmt->execute();
$result = $stmt->get_result();

$total_geral = 0;
$lucro_geral = 0;
$linhas = [];
while ($row = $result->fetch_assoc()) {
    $comida = $row['comida'];
    $quantidade = $row['quantidade'];
    $preco_unitario = $precos[$comida] ?? 0;
    $custo_unitario = $custos[$comida] ?? 0;
    $total = $quantidade * $preco_unitario;
    $lucro = $quantidade * ($preco_unitario - $custo_unitario);

    $row['total'] = number_format($total, 2, ',', '.');
    $row['lucro'] = number_format($lucro, 2, ',', '.');
    $total_geral += $total;
    $lucro_geral += $lucro;
    $linhas[] = $row;
}

// Dados para gráfico geral
$grafico_res = $mysqli->query("SELECT comida, SUM(quantidade) AS total FROM vendas GROUP BY comida");
$dados_grafico = [];
while ($row = $grafico_res->fetch_assoc()) {
    $dados_grafico[] = ['comida' => $row['comida'], 'total' => (int)$row['total']];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Relatório de Vendas - Festa Junina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Relatório de Vendas</h2>

        <!-- Gráfico -->
        <div class="mb-4 text-center">
            <canvas id="graficoVendas" style="max-width:800px; width:100%; margin:auto;"></canvas>
        </div>

        <!-- Totais -->
        <div class="alert alert-info text-center">
            <strong>Total Vendido (R$):</strong> <?= number_format($total_geral, 2, ',', '.') ?>
        </div>
        <div class="alert alert-success text-center">
            <strong>Lucro Total (R$):</strong> <?= number_format($lucro_geral, 2, ',', '.') ?>
        </div>

        <!-- Filtros -->
        <?php if ($tipo === 'admin') { ?>
            <form method="GET" class="mb-4 text-center">
                <label for="filtro_usuario" class="form-label me-2">Filtrar por barraca:</label>
                <select name="filtro_usuario" class="form-select d-inline-block w-auto me-3" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    <?php foreach ($barracas as $b): ?>
                        <option value="<?= htmlspecialchars($b) ?>" <?= ($filtro_usuario == $b ? 'selected' : '') ?>><?= htmlspecialchars($b) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="filtro_comida" class="form-label me-2">Filtrar por comida:</label>
                <select name="filtro_comida" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    <?php foreach ($lista_comidas as $com): ?>
                        <option value="<?= htmlspecialchars($com) ?>" <?= ($filtro_comida == $com ? 'selected' : '') ?>><?= htmlspecialchars($com) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php } ?>

        <!-- Tabela -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Comida</th>
                    <th>Quantidade</th>
                    <th>Total (R$)</th>
                    <th>Lucro (R$)</th>
                    <?php if ($tipo === 'admin') { ?>
                        <th>Ações</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($linhas as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['usuario']) ?></td>
                        <td><?= htmlspecialchars($row['comida']) ?></td>
                        <td><?= (int)$row['quantidade'] ?></td>
                        <td><?= $row['total'] ?></td>
                        <td><?= $row['lucro'] ?></td>
                        <?php if ($tipo === 'admin') { ?>
                            <td>
                                <form method="POST" action="deletar.php" onsubmit="return confirm('Confirmar exclusão?');">
                                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>" />
                                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                </form>
                            </td>
                        <?php } ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Botões -->
        <div class="text-center mt-3">
            <a href="vendas.php" class="btn btn-primary">Voltar</a>
            <a href="logout.php" class="btn btn-secondary">Sair</a>
        </div>
    </div>

    <!-- Gráfico -->
    <script>
    const ctx = document.getElementById('graficoVendas').getContext('2d');
    const dadosGrafico = {
        labels: <?= json_encode(array_column($dados_grafico, 'comida')) ?>,
        datasets: [{
            label: 'Quantidade Vendida',
            data: <?= json_encode(array_column($dados_grafico, 'total')) ?>,
            backgroundColor: 'rgba(255, 159, 64, 0.7)',
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 1,
            borderRadius: 6,
        }]
    };
    const graficoVendas = new Chart(ctx, {
        type: 'bar',
        data: dadosGrafico,
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            },
            plugins: { legend: { display: false } }
        }
    });
    </script>
</body>
</html>