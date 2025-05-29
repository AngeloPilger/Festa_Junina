<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
include 'conexao.php';

$usuario = $_SESSION['usuario'];
$stmt = $mysqli->prepare("SELECT comidas FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

$comidas = [];
if ($row = $result->fetch_assoc()) {
    $comidas = array_map('trim', explode(',', $row['comidas']));
}

$imagens = [
    "Cachorro-quente" => "imagens/cachorro quente.png",
    "Algodão doce" => "imagens/algodão doce.png",
    "Salgado no copo" => "imagens/salgado no copo.png",
    "Churros" => "imagens/churros.png",
    "Pipoca doce" => "imagens/pipoca doce.png",
    "Pipoca salgada" => "imagens/pipoca salgada.png",
    "Amendoim cri-cri" => "imagens/cri-cri.png",
    "Maçã do amor" => "imagens/maçã do amor.png",
    "Bolo" => "imagens/bolo.png",
    "Bebidas" => "imagens/bebidas.png",
    "Pastel" => "imagens/pastel.png",
    "Crepe" => "imagens/crepe.png"
];

$valores = [
    "Cachorro-quente" => 5,
    "Algodão doce" => 4,
    "Salgado no copo" => 3,
    "Churros" => 4,
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

$msgPedido = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fechar_pedido'])) {
    $pedido = [];
    $total = 0;

    foreach ($_POST['quantidades'] as $comida => $qtd) {
        $qtd = intval($qtd);
        if ($qtd > 0) {
            $valor_unitario = $valores[$comida];
            $valor_total = $qtd * $valor_unitario;
            $custo_unitario = $custos[$comida] ?? 0;
            $lucro = $qtd * ($valor_unitario - $custo_unitario);

            $stmt = $mysqli->prepare("INSERT INTO vendas (usuario, comida, quantidade, valor_total, lucro, data) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssidd", $usuario, $comida, $qtd, $valor_total, $lucro);
            $stmt->execute();

            $pedido[] = "$qtd $comida";
            $total += $valor_total;
        }
    }

    if ($total > 0) {
        $resumo = implode(", ", $pedido);
        $msgPedido = "Pedido fechado: <strong>$resumo</strong>. Total: <strong>R$ " . number_format($total, 2, ',', '.') . "</strong>";
    } else {
        $msgPedido = "Nenhum item selecionado para o pedido.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>🛒 Caixa de Vendas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: #fff8f0;
            min-height: 100vh;
        }
        .produto-card {
            cursor: pointer;
            transition: transform 0.2s ease-in-out;
            user-select: none;
        }
        .produto-card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(255, 153, 51, 0.4);
        }
        .icone-produto {
            text-align: center;
        }
        .imagem-produto {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-bottom: 0.5rem;
        }
        .quantidade-input {
            width: 70px;
            text-align: center;
            font-weight: bold;
        }
        .total-box {
            background: #fff3e0;
            border: 2px solid #ffa500;
            border-radius: 8px;
            padding: 1rem;
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
            color: #b85a00;
        }
        .msg-pedido {
            margin-top: 1rem;
            font-size: 1.2rem;
            color: #007a00;
        }
        @media (min-width: 992px) {
            .main-row {
                display: flex;
                gap: 2rem;
            }
            .produtos-col {
                flex: 3;
            }
            .resumo-col {
                flex: 1;
                position: sticky;
                top: 2rem;
                height: fit-content;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h2 class="mb-4 text-center text-warning">🛒 Caixa - Registrar Pedido</h2>

        <form method="POST" oninput="calcularTotal(); mostrarTroco();" autocomplete="off">
            <div class="main-row">
                <div class="produtos-col row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                    <?php foreach ($comidas as $comida): ?>
                        <div class="col">
                            <div class="card produto-card shadow-sm">
                                <div class="card-body d-flex flex-column align-items-center">
                                    <div class="icone-produto mb-2">
                                        <?php if (isset($imagens[$comida])): ?>
                                            <img src="<?= $imagens[$comida] ?>" alt="<?= htmlspecialchars($comida) ?>" class="imagem-produto" />
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="card-title text-center"><?= htmlspecialchars($comida) ?></h5>
                                    <p class="card-text text-warning fw-semibold">R$ <?= number_format($valores[$comida], 2, ',', '.') ?></p>
                                    <input
                                        type="number"
                                        min="0"
                                        value="0"
                                        name="quantidades[<?= htmlspecialchars($comida) ?>]"
                                        class="form-control quantidade quantidade-input"
                                        data-comida="<?= htmlspecialchars($comida) ?>"
                                        aria-label="Quantidade de <?= htmlspecialchars($comida) ?>"
                                    />
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="resumo-col">
                    <div class="total-box mb-4">
                        Total:<br />
                        <span id="total">R$ 0,00</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Forma de Pagamento:</label><br />
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="pagamento" value="pix" id="pag_pix" checked />
                            <label class="form-check-label" for="pag_pix">Pix / Cartão</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="pagamento" value="dinheiro" id="pag_dinheiro" />
                            <label class="form-check-label" for="pag_dinheiro">Dinheiro</label>
                        </div>
                    </div>

                    <div id="trocoContainer" class="mb-3" style="display:none;">
                        <label for="valor_pago" class="form-label fw-semibold">Valor Pago:</label>
                        <input type="number" step="0.01" min="0" name="valor_pago" id="valor_pago" class="form-control" placeholder="Digite o valor pago" />
                        <div id="troco" class="form-text fw-bold text-success"></div>
                    </div>

                    <button name="fechar_pedido" class="btn btn-warning w-100 fw-bold">✅ Fechar Pedido</button>
                    <a href="relatorio.php" class="btn btn-outline-secondary w-100 mt-3">📊 Ver Relatório</a>
                    <a href="logout.php" class="btn btn-outline-danger w-100 mt-2">🚪 Sair</a>

                    <?php if($msgPedido): ?>
                        <div class="msg-pedido alert alert-success mt-3" role="alert">
                            <?= $msgPedido ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

<script>
    const valores = <?= json_encode($valores) ?>;

    function calcularTotal() {
        let total = 0;
        document.querySelectorAll('.quantidade').forEach(input => {
            const comida = input.dataset.comida;
            const qtd = parseInt(input.value) || 0;
            total += qtd * valores[comida];
        });
        document.getElementById('total').innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
        calcularTroco();
    }

    function mostrarTroco() {
        const metodo = document.querySelector('input[name="pagamento"]:checked').value;
        const trocoContainer = document.getElementById('trocoContainer');
        if (metodo === 'dinheiro') {
            trocoContainer.style.display = 'block';
        } else {
            trocoContainer.style.display = 'none';
            document.getElementById('valor_pago').value = '';
            document.getElementById('troco').innerText = '';
        }
        calcularTroco();
    }

    function calcularTroco() {
        const metodo = document.querySelector('input[name="pagamento"]:checked').value;
        if (metodo !== 'dinheiro') {
            document.getElementById('troco').innerText = '';
            return;
        }
        const totalText = document.getElementById('total').innerText.replace('R$ ', '').replace(',', '.');
        const total = parseFloat(totalText) || 0;
        const pago = parseFloat(document.getElementById('valor_pago').value) || 0;
        const troco = pago - total;
        const trocoEl = document.getElementById('troco');

        if (troco < 0) {
            trocoEl.innerText = 'Valor pago insuficiente.';
            trocoEl.classList.remove('text-success');
            trocoEl.classList.add('text-danger');
        } else {
            trocoEl.innerText = 'Troco: R$ ' + troco.toFixed(2).replace('.', ',');
            trocoEl.classList.remove('text-danger');
            trocoEl.classList.add('text-success');
        }
    }

    document.querySelectorAll('.quantidade').forEach(input => {
        input.addEventListener('input', calcularTotal);
    });

    document.querySelectorAll('input[name="pagamento"]').forEach(radio => {
        radio.addEventListener('change', mostrarTroco);
    });

    document.getElementById('valor_pago').addEventListener('input', calcularTroco);

    calcularTotal();
    mostrarTroco();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>