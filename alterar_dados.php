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

// Busca dados atualizados do banco para garantir que o formulário esteja correto
$sql = "SELECT * FROM clientes WHERE id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $cliente_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$dados = mysqli_fetch_assoc($resultado);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $genero = $_POST['genero'];
    $data_nascimento = $_POST['data_nascimento'];

    // Validação simples de nome composto
    $nomes = explode(' ', $nome);
    if (count($nomes) < 2) {
        $mensagem = "Por favor, digite seu nome completo.";
        $tipo_mensagem = "error";
    } else {
        // Atualiza os dados no banco
        $sql_update = "UPDATE clientes SET nome = ?, email = ?, genero = ?, data_nascimento = ? WHERE id = ?";
        $stmt_up = mysqli_prepare($con, $sql_update);
        mysqli_stmt_bind_param($stmt_up, "ssssi", $nome, $email, $genero, $data_nascimento, $cliente_id);

        if (mysqli_stmt_execute($stmt_up)) {
            // Atualiza a sessão com os novos dados
            $_SESSION['user']['nome'] = $nome;
            $_SESSION['user']['email'] = $email;
            
            $mensagem = "Dados atualizados com sucesso!";
            $tipo_mensagem = "success";
            
            // Recarrega os dados para exibição
            $dados['nome'] = $nome;
            $dados['email'] = $email;
            $dados['genero'] = $genero;
            $dados['data_nascimento'] = $data_nascimento;
        } else {
            $mensagem = "Erro ao atualizar os dados. Tente novamente.";
            $tipo_mensagem = "error";
        }
    }
}

$dataMaxima = (new DateTime('-18 years'))->format('Y-m-d');
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Meus Dados - Vitrini Drink's</title>
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

        .container { max-width: 900px; margin: 50px auto; padding: 0 20px; }
        
        .form-card { 
            background: #fff; 
            padding: 45px; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
        }

        .form-title { 
            font-size: 1.8em; 
            font-weight: bold; 
            margin-bottom: 30px; 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            color: #212529; 
            border-bottom: 2px solid #f4f4f4; 
            padding-bottom: 15px; 
        }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .input-group { margin-bottom: 20px; }
        .input-group.full { grid-column: span 2; }
        
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #495057; font-size: 0.95em; }
        .input-group input, .input-group select { 
            width: 100%; 
            padding: 14px; 
            border: 1px solid #ddd; 
            border-radius: 10px; 
            font-size: 1em; 
            outline: none; 
            background: #fff;
        }
        .input-group input:focus { border-color: #0d6efd; box-shadow: 0 0 8px rgba(13, 110, 253, 0.1); }
        .input-group input:disabled { background: #f8f9fa; cursor: not-allowed; color: #888; }

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
            margin-top: 20px;
        }
        .btn-save:hover { background: #157347; transform: translateY(-2px); }

        .btn-back { display: block; text-align: center; margin-top: 25px; color: #6c757d; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<header class="store-header">
    <div class="logo"><a href="index.php">Vitrini Drink's</a></div>
    <div class="header-actions">
        <a href="area_cliente.php"><i class="fas fa-user-circle"></i> Voltar ao Painel</a>
    </div>
</header>

<div class="container">
    <div class="form-card">
        <h1 class="form-title"><i class="fas fa-user-edit"></i> Alterar Dados Cadastrais</h1>

        <?php if ($mensagem): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                <i class="fas <?php echo ($tipo_mensagem == 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form action="alterar_dados.php" method="POST">
            <div class="form-grid">
                <div class="input-group full">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" value="<?php echo htmlspecialchars($dados['nome']); ?>" required>
                </div>

                <div class="input-group">
                    <label>E-mail</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($dados['email']); ?>" required>
                </div>

                <div class="input-group">
                    <label>CPF (Não pode ser alterado)</label>
                    <input type="text" value="<?php echo htmlspecialchars($dados['cpf']); ?>" disabled>
                </div>

                <div class="input-group">
                    <label>Gênero</label>
                    <select name="genero" required>
                        <option value="Masculino" <?php echo ($dados['genero'] == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                        <option value="Feminino" <?php echo ($dados['genero'] == 'Feminino') ? 'selected' : ''; ?>>Feminino</option>
                        <option value="Outro" <?php echo ($dados['genero'] == 'Outro') ? 'selected' : ''; ?>>Outro</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Data de Nascimento</label>
                    <input type="date" name="data_nascimento" value="<?php echo $dados['data_nascimento']; ?>" max="<?php echo $dataMaxima; ?>" required>
                </div>
            </div>

            <button type="submit" class="btn-save">SALVAR ALTERAÇÕES</button>
        </form>

        <a href="area_cliente.php" class="btn-back"><i class="fas fa-arrow-left"></i> Cancelar e Voltar</a>
    </div>
</div>

</body>
</html>