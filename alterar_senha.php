<?php
session_start();
require_once('conexao.php');

// Verifica se o cliente está logado
if (!isset($_SESSION['user'])) {
    header("Location: login_cliente.php");
    exit();
}

$cliente_id = $_SESSION['user']['id'];
$mensagem = "";
$tipo_mensagem = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirma_senha = $_POST['confirma_senha'];

    // Busca a senha atual no banco para comparar
    $sql = "SELECT senha FROM clientes WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $cliente_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($resultado);

    // Validações de segurança
    if (!password_verify($senha_atual, $user['senha'])) {
        $mensagem = "A senha atual está incorreta.";
        $tipo_mensagem = "error";
    } elseif ($nova_senha !== $confirma_senha) {
        $mensagem = "As novas senhas não coincidem.";
        $tipo_mensagem = "error";
    } elseif (strlen($nova_senha) < 8) {
        $mensagem = "A nova senha deve ter pelo menos 8 caracteres.";
        $tipo_mensagem = "error";
    } else {
        // Atualiza a senha (Criptografada)
        $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $sql_update = "UPDATE clientes SET senha = ? WHERE id = ?";
        $stmt_up = mysqli_prepare($con, $sql_update);
        mysqli_stmt_bind_param($stmt_up, "si", $nova_senha_hash, $cliente_id);

        if (mysqli_stmt_execute($stmt_up)) {
            $mensagem = "Senha alterada com sucesso!";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Erro ao atualizar a senha. Tente novamente.";
            $tipo_mensagem = "error";
        }
    }
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha - Vitrini Drink's</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; color: #333; }

        .store-header { 
            background-color: #1a1a1a; 
            color: white; 
            padding: 25px 2%; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .store-header .logo a { 
            color: #ffc107; 
            text-decoration: none; 
            font-size: 1.8em; 
            font-weight: 800; 
            text-transform: uppercase;
        }

        .container { max-width: 600px; margin: 60px auto; padding: 0 20px; }
        
        .form-card { 
            background: #fff; 
            padding: 45px; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
        }

        .form-title { 
            font-size: 1.6em; 
            font-weight: bold; 
            margin-bottom: 30px; 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            color: #212529; 
            border-bottom: 2px solid #f4f4f4; 
            padding-bottom: 15px; 
        }

        .input-group { margin-bottom: 25px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #495057; }
        .input-group input { 
            width: 100%; 
            padding: 14px; 
            border: 1px solid #ddd; 
            border-radius: 10px; 
            font-size: 1em; 
            outline: none; 
        }
        .input-group input:focus { border-color: #0d6efd; box-shadow: 0 0 8px rgba(13, 110, 253, 0.1); }

        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .btn-save { 
            width: 100%; 
            padding: 18px; 
            background: #198754; 
            color: white; 
            border: none; 
            border-radius: 10px; 
            font-size: 1.2em; 
            font-weight: bold; 
            cursor: pointer; 
            transition: 0.3s; 
        }
        .btn-save:hover { background: #157347; transform: translateY(-2px); }

        .btn-back { display: block; text-align: center; margin-top: 25px; color: #6c757d; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<header class="store-header">
    <div class="logo"><a href="index.php">Vitrini Drink's</a></div>
    <div class="header-actions">
        <a href="area_cliente.php"><i class="fas fa-arrow-left"></i> Painel da Conta</a>
    </div>
</header>

<div class="container">
    <div class="form-card">
        <h1 class="form-title"><i class="fas fa-key"></i> Alterar Senha</h1>

        <?php if ($mensagem): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                <i class="fas <?php echo ($tipo_mensagem == 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form action="alterar_senha.php" method="POST">
            <div class="input-group">
                <label>Senha Atual</label>
                <input type="password" name="senha_atual" placeholder="Sua senha antiga" required>
            </div>

            <div class="input-group">
                <label>Nova Senha</label>
                <input type="password" name="nova_senha" placeholder="Mínimo 8 caracteres" required>
            </div>

            <div class="input-group">
                <label>Confirmar Nova Senha</label>
                <input type="password" name="confirma_senha" placeholder="Repita a nova senha" required>
            </div>

            <button type="submit" class="btn-save">ATUALIZAR SENHA</button>
        </form>

        <a href="area_cliente.php" class="btn-back">Cancelar e Voltar</a>
    </div>
</div>

</body>
</html>