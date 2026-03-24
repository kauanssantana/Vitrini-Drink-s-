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

// 1. PROCESSAR O ENVIO DA SOLICITAÇÃO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pedido_id = $_POST['pedido_id'];
    $motivo = trim($_POST['motivo']);

    if (empty($pedido_id) || empty($motivo)) {
        $mensagem = "Por favor, selecione um pedido e descreva o motivo.";
        $tipo_mensagem = "error";
    } else {
        // Insere a solicitação (Certifique-se de ter a tabela 'devolucoes' no seu banco)
        $sql_dev = "INSERT INTO devolucoes (pedido_id, cliente_id, motivo, status, data_solicitacao) VALUES (?, ?, ?, 'Pendente', NOW())";
        $stmt = mysqli_prepare($con, $sql_dev);
        mysqli_stmt_bind_param($stmt, "iis", $pedido_id, $cliente_id, $motivo);

        if (mysqli_stmt_execute($stmt)) {
            $mensagem = "Sua solicitação de devolução foi enviada com sucesso! Analisaremos em até 48h.";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Erro ao enviar solicitação. Tente novamente mais tarde.";
            $tipo_mensagem = "error";
        }
    }
}

// 2. BUSCAR PEDIDOS DO CLIENTE PARA O SELECT (Apenas pedidos 'Pagos' ou 'Entregues' costumam permitir devolução)
$query_pedidos = "SELECT id, numero_pedido, data_criacao FROM pedidos WHERE cliente_id = ? ORDER BY data_criacao DESC";
$stmt_p = mysqli_prepare($con, $query_pedidos);
mysqli_stmt_bind_param($stmt_p, "i", $cliente_id);
mysqli_stmt_execute($stmt_p);
$result_pedidos = mysqli_stmt_get_result($stmt_p);

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Devolução - Vitrini Drink's</title>
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

        .container { max-width: 800px; margin: 50px auto; padding: 0 20px; }
        
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

        .input-group { margin-bottom: 25px; }
        .input-group label { display: block; margin-bottom: 10px; font-weight: 600; color: #495057; }
        .input-group select, .input-group textarea { 
            width: 100%; 
            padding: 14px; 
            border: 1px solid #ddd; 
            border-radius: 10px; 
            font-size: 1em; 
            outline: none; 
            font-family: inherit;
        }
        .input-group textarea { height: 150px; resize: none; }
        .input-group select:focus, .input-group textarea:focus { border-color: #0d6efd; box-shadow: 0 0 8px rgba(13, 110, 253, 0.1); }

        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .btn-send { 
            width: 100%; 
            padding: 18px; 
            background: #0d6efd; 
            color: white; 
            border: none; 
            border-radius: 10px; 
            font-size: 1.2em; 
            font-weight: bold; 
            cursor: pointer; 
            transition: 0.3s; 
        }
        .btn-send:hover { background: #0b5ed7; transform: translateY(-2px); }

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
        <h1 class="form-title"><i class="fas fa-undo-alt"></i> Solicitar Devolução</h1>

        <?php if ($mensagem): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                <i class="fas <?php echo ($tipo_mensagem == 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form action="solicitar_devolucao.php" method="POST">
            <div class="input-group">
                <label>Selecione o Pedido</label>
                <select name="pedido_id" required>
                    <option value="">Escolha um pedido...</option>
                    <?php while ($ped = mysqli_fetch_assoc($result_pedidos)): ?>
                        <option value="<?php echo $ped['id']; ?>">
                            Pedido #<?php echo $ped['numero_pedido']; ?> - <?php echo date('d/m/Y', strtotime($ped['data_criacao'])); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Motivo da Devolução</label>
                <textarea name="motivo" placeholder="Descreva detalhadamente o problema com o produto ou o motivo da desistência..." required></textarea>
            </div>

            <button type="submit" class="btn-send">ENVIAR SOLICITAÇÃO</button>
        </form>

        <a href="area_cliente.php" class="btn-back">Voltar ao Painel</a>
    </div>
</div>

</body>
</html>