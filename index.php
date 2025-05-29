<?php
session_start();
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    $stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE usuario=? AND senha=?");
    $stmt->bind_param("ss", $usuario, $senha);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $dados = $resultado->fetch_assoc();
        $_SESSION['usuario'] = $dados['usuario'];
        $_SESSION['tipo'] = $dados['tipo'];
        $_SESSION['comidas'] = explode(',', $dados['comidas']);
        header("Location: vendas.php");
        exit();
    } else {
        $erro = "Usuário ou senha inválidos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login Festa Junina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-warning-subtle d-flex align-items-center justify-content-center vh-100">
    <div class="card p-4 shadow" style="min-width: 320px;">
        <h2 class="text-center mb-3">🎉 Login Festa Junina 🎉</h2>
        <form method="POST">
            <input class="form-control mb-2" type="text" name="usuario" placeholder="Usuário" required>
            <input class="form-control mb-2" type="password" name="senha" placeholder="Senha" required>
            <button class="btn btn-success w-100" type="submit">Entrar</button>
        </form>
        <?php if (isset($erro)) echo "<div class='alert alert-danger mt-2'>$erro</div>"; ?>
    </div>
</body>
</html>