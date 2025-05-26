<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
include 'conexao.php';

$usuario = $_SESSION['usuario'];
$tipo = $_SESSION['tipo'];

// Busca todas as vendas
$result = $mysqli->query("SELECT * FROM vendas ORDER BY data DESC");

// Dados para o gráfico
$grafico_res = $mysqli->query("SELECT comida, SUM(quantidade) AS total FROM vendas GROUP BY comida");

$dados_grafico = [];
while ($row = $grafico_res->fetch_assoc()) {
    $dados_grafico[] = ['comida' => $row['comida'], 'total' => (int)$row['total']];
}

// Função para formatar a data
function formatarData($data) {
    return date('d/m/Y H:i', strtotime($data));
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
            <canvas id="graficoVendas" style="max-width:600px;margin:auto;"></canvas>
        </div>

        <!-- Tabela -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Comida</th>
                    <th>Quantidade</th>
                    <th>Data</th>
                    <?php if ($tipo === 'admin') { ?>
                        <th>Ações</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['usuario']) ?></td>
                        <td><?= htmlspecialchars($row['comida']) ?></td>
                        <td><?= $row['quantidade'] ?></td>
                        <td><?= formatarData($row['data']) ?></td>
                        <?php if ($tipo === 'admin') { ?>
                            <td>
                                <form method="POST" action="deletar.php" onsubmit="return confirm('Confirmar exclusão?');">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                </form>
                            </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Botões -->
        <div class="text-center mt-3">
            <a href="vendas.php" class="btn btn-primary">Voltar</a>
            <a href="exportar.php" class="btn btn-success">Exportar para Excel</a>
            <a href="logout.php" class="btn btn-secondary">Sair</a>
        </div>
    </div>

    <!-- Script do Gráfico -->
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
            borderRadius: 5,
        }]
    };
    const graficoVendas = new Chart(ctx, {
        type: 'bar',
        data: dadosGrafico,
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    stepSize: 1
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
    </script>
</body>
</html>
