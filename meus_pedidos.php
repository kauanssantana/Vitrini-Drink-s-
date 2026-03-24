<?php
session_start();
require_once('conexao.php');

// Verifica se o cliente está logado
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Você precisa estar logado para acessar seus pedidos.'); window.location.href='login_cliente.php';</script>";
    exit();
}

$cliente_id = $_SESSION['user']['id'];

// Busca os pedidos do cliente logado
$query = "SELECT * FROM pedidos WHERE cliente_id = ? ORDER BY data_criacao DESC";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $cliente_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - Vitrini Drink's</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, sans-serif; color: #333; }

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
        .store-header .header-actions { display: flex; align-items: center; gap: 30px; }
        .store-header .header-actions a { color: white; text-decoration: none; font-weight: 600; font-size: 1.1em; transition: color 0.3s; }
        .store-header .header-actions a:hover { color: #ffc107; }

        .container { max-width: 1200px; margin: 50px auto; padding: 0 20px; }

        .orders-card { 
            background: #fff; 
            padding: 40px; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
        }

        h1 { margin-bottom: 30px; font-size: 2em; color: #212529; display: flex; align-items: center; gap: 15px; }

        .pedido-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .pedido-table th { text-align: left; padding: 15px; background: #f8f9fa; color: #666; font-size: 0.9em; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #eee; }
        .pedido-table td { padding: 20px 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }

        .status-badge { 
            padding: 6px 12px; 
            border-radius: 20px; 
            font-size: 0.85em; 
            font-weight: bold; 
            text-transform: uppercase;
        }
        .status-aguardando { background: #fff3cd; color: #856404; }
        .status-pago { background: #d4edda; color: #155724; }
        .status-cancelado { background: #f8d7da; color: #721c24; }

        .btn-details { 
            padding: 10px 20px; 
            background: #0d6efd; 
            color: white; 
            text-decoration: none; 
            border-radius: 8px; 
            font-weight: bold; 
            font-size: 0.9em;
            transition: 0.3s;
            display: inline-block;
        }
        .btn-details:hover { background: #0b5ed7; transform: translateY(-2px); }

        .empty-orders { text-align: center; padding: 60px 0; }
        .empty-orders i { color: #eee; margin-bottom: 20px; }

        .btn-back { 
            display: inline-block; 
            margin-top: 20px; 
            color: #6c757d; 
            text-decoration: none; 
            font-weight: 600; 
        }
        .btn-back:hover { color: #333; }
    </style>
</head>
<body>

<header class="store-header">
    <div class="logo">
        <a href="index.php">Vitrini Drink's</a>
    </div>
    <div class="header-actions">
        <a href="area_cliente.php"><i class="fas fa-user-circle"></i> Minha Conta</a>
        <a href="index.php"><i class="fas fa-shopping-bag"></i> Loja</a>
    </div>
</header>

<div class="container">
    <div class="orders-card">
        <h1><i class="fas fa-box"></i> Meus Pedidos</h1>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <table class="pedido-table">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Data</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pedido = mysqli_fetch_assoc($result)): 
                        $status_class = '';
                        if($pedido['status'] == 'aguardando pagamento') $status_class = 'status-aguardando';
                        elseif($pedido['status'] == 'pago') $status_class = 'status-pago';
                        else $status_class = 'status-cancelado';
                    ?>
                        <tr>
                            <td style="font-weight: bold; color: #0d6efd;"><?php echo $pedido['numero_pedido']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($pedido['data_criacao'])); ?></td>
                            <td style="font-weight: bold;">R$ <?php echo number_format($pedido['total_com_frete'], 2, ',', '.'); ?></td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo $pedido['status']; ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <a href="detalhes.php?id=<?php echo $pedido['id']; ?>" class="btn-details">
                                    <i class="fas fa-search-plus"></i> Ver Detalhes
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-orders">
                <i class="fas fa-history fa-4x"></i>
                <h3>Você ainda não realizou pedidos.</h3>
                <p>Que tal explorar nossos drinks agora?</p>
                <a href="index.php" class="btn-details" style="margin-top: 20px;">Ir para a Loja</a>
            </div>
        <?php endif; ?>

        <a href="area_cliente.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar ao Painel</a>
    </div>
</div>

</body>
</html>
<?php 
mysqli_stmt_close($stmt);
mysqli_close($con); 
?>