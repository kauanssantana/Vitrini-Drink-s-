<?php
session_start();
require_once('conexao.php');

$error = "";

// Se já estiver logado, manda para a home
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    // CORREÇÃO: Agora busca na tabela 'clientes' que você mostrou na imagem
    $sql = "SELECT * FROM clientes WHERE email = ? AND status = 'Ativo'";
    $stmt = mysqli_prepare($con, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($senha, $user['senha'])) {
            // Salva os dados do cliente na sessão
            $_SESSION['user'] = $user;
            $_SESSION['user']['tipo'] = 'cliente'; // Marcador para sabermos que é cliente
            
            header("Location: index.php");
            exit();
        } else {
            $error = "E-mail ou senha incorretos!";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($con);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login do Cliente - Vitrini Drink's</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-card { background: #fff; width: 100%; max-width: 500px; padding: 50px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); text-align: center; }
        .login-card img { max-width: 120px; margin-bottom: 25px; border-radius: 10px; }
        .login-card h2 { color: #333; margin-bottom: 12px; font-size: 2em; }
        .login-card p { color: #6c757d; margin-bottom: 35px; font-size: 1.1em; }
        .input-group { text-align: left; margin-bottom: 25px; position: relative; }
        .input-group label { display: block; margin-bottom: 10px; font-weight: bold; color: #495057; font-size: 1em; }
        .input-group input { width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 10px; font-size: 1.1em; outline: none; transition: all 0.3s; }
        .input-group input:focus { border-color: #0d6efd; box-shadow: 0 0 8px rgba(13, 110, 253, 0.15); }
        .toggle-password { position: absolute; right: 18px; top: 45px; color: #6c757d; cursor: pointer; font-size: 1.1em; }
        .alert-error { background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 25px; font-size: 1em; border: 1px solid #f5c6cb; }
        .btn-login { width: 100%; padding: 16px; background-color: #0d6efd; color: white; border: none; border-radius: 10px; font-size: 1.2em; font-weight: bold; cursor: pointer; transition: background 0.3s; margin-bottom: 25px; }
        .btn-login:hover { background-color: #0b5ed7; }
        .register-link { display: block; margin-bottom: 25px; color: #0d6efd; text-decoration: none; font-weight: bold; font-size: 1.05em; }
        .register-link:hover { text-decoration: underline; }
        .btn-back { display: inline-block; color: #6c757d; text-decoration: none; font-size: 1em; }
    </style>
</head>
<body>

<div class="login-card">
    <img src="vitrine.jpg" alt="Vitrini Drink's" onerror="this.style.display='none'">
    <h2>Acessar Conta</h2>
    <p>Identifique-se para gerir os seus pedidos.</p>

    <?php if (!empty($error)): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="login_cliente.php" method="POST">
        <div class="input-group">
            <label for="email">E-mail de Cadastro:</label>
            <input type="email" name="email" id="email" placeholder="cliente@gmail.com" required>
        </div>

        <div class="input-group">
            <label for="senha">Sua Senha:</label>
            <input type="password" name="senha" id="senha" placeholder="••••••••" required>
            <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
        </div>

        <button type="submit" class="btn-login">Entrar no Painel</button>
    </form>

    <a href="criar_conta.php" class="register-link">Ainda não tem conta? Criar uma agora</a>
    
    <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar para a loja</a>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('senha');
        const icon = document.querySelector('.toggle-password');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>

</body>
</html>