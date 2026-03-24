<?php
session_start();
require_once('conexao.php');

$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuarios WHERE email = ? AND status = 'Ativo'";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['user'] = [
            'id' => $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'grupo' => $usuario['grupo']
        ];
        header("Location: principal.php");
        exit();
    } else {
        $erro = "Credenciais inválidas ou conta inativa.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo - Vitrini Drink's</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }

        .admin-header { 
            background-color: #1a1a1a; 
            color: #ffc107; 
            padding: 30px 0; 
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            text-transform: uppercase;
            font-weight: 900;
            font-size: 1.5em;
            letter-spacing: 3px;
        }

        .login-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            padding: 20px;
        }

        .login-card {
            background: #fff;
            padding: 60px; 
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 550px; 
            text-align: center;
            border-top: 5px solid #ffc107;
        }

        .login-card h2 { margin-bottom: 40px; color: #1a1a1a; font-size: 2em; }

        .input-group { margin-bottom: 25px; text-align: left; }
        .input-group label { display: block; margin-bottom: 10px; font-weight: 700; color: #444; font-size: 1.1em; }
        
        .input-group input {
            width: 100%;
            padding: 18px; 
            border: 2px solid #eee;
            border-radius: 12px;
            font-size: 1.1em;
            transition: 0.3s;
            outline: none;
        }
        
        .input-group input:focus { border-color: #ffc107; background-color: #fffdf5; }

        .btn-login {
            width: 100%;
            padding: 20px;
            background-color: #1a1a1a;
            color: #ffc107;
            border: none;
            border-radius: 12px;
            font-size: 1.3em;
            font-weight: 800;
            cursor: pointer;
            transition: 0.4s;
            margin-top: 20px;
            text-transform: uppercase;
        }
        
        .btn-login:hover { background-color: #000; letter-spacing: 1px; box-shadow: 0 10px 20px rgba(0,0,0,0.2); }

        .error-msg {
            background-color: #f8d7da; color: #721c24; padding: 15px;
            border-radius: 10px; margin-bottom: 30px; font-weight: bold;
        }
    </style>
</head>
<body>

<header class="admin-header">
    PAINEL ADMINISTRATIVO
</header>

<div class="login-wrapper">
    <div class="login-card">
        <img src="vitrine.jpg" alt="Logo" style="max-width: 140px; margin-bottom: 30px; border-radius: 15px;">
        <h2>Acesso ao Sistema</h2>

        <?php if ($erro): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="input-group">
                <label><i class="fas fa-envelope"></i> E-mail Corporativo</label>
                <input type="email" name="email" placeholder="Digite seu e-mail" required>
            </div>

            <div class="input-group">
                <label><i class="fas fa-lock"></i> Senha de Acesso</label>
                <input type="password" name="senha" placeholder="Digite sua senha" required>
            </div>

            <button type="submit" class="btn-login">ENTRAR AGORA</button>
        </form>
    </div>
</div>

</body>
</html>