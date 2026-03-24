<?php
session_start();
require_once('conexao.php');

// SEGURANÇA AJUSTADA: Verifica a sessão de acordo com o seu Painel Principal
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['grupo'], ['Administrador', 'Estoquista'])) {
    header("Location: login.php");
    exit();
}

// 1. LÓGICA DE ALTERAÇÃO DE STATUS
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alterar_status'])) {
    $pedido_id = (int)$_POST['pedido_id'];
    $novo_status = $_POST['novo_status'];
    
    $stmt = $con->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $novo_status, $pedido_id);
    $stmt->execute();
    echo "<script>alert('Status atualizado!'); window.location.href='listar_pedidos.php';</script>";
}

// 2. BUSCA DE PEDIDOS COM FILTRO
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';
$sql = "SELECT p.*, c.nome as nome_cliente 
        FROM pedidos p 
        JOIN clientes c ON p.cliente_id = c.id";

if ($filtro) {
    $f = mysqli_real_escape_string($con, $filtro);
    $sql .= " WHERE p.status = '$f'";
}
$sql .= " ORDER BY p.data_criacao DESC";
$result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Pedidos - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; color: #333; }

        .admin-header { 
            background-color: #1a1a1a; color: white; padding: 25px 2%; 
            display: flex; justify-content: space-between; align-items: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .admin-header .logo a { color: #ffc107; text-decoration: none; font-size: 1.8em; font-weight: 800; text-transform: uppercase; }

        .container { max-width: 1600px; width: 95%; margin: 40px auto; padding: 0 20px; }
        .admin-card { background: #fff; padding: 35px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
        
        .filter-group a { text-decoration: none; padding: 8px 15px; border-radius: 20px; font-size: 0.9em; background: #eee; color: #666; transition: 0.3s; margin-right: 5px; }
        .filter-group a.active { background: #0d6efd; color: white; }

        .pedidos-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .pedidos-table th { text-align: left; padding: 15px; background: #f8f9fa; color: #666; font-size: 0.85em; text-transform: uppercase; border-bottom: 2px solid #eee; }
        .pedidos-table td { padding: 18px 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }

        .badge { padding: 6px 12px; border-radius: 20px; font-size: 0.8em; font-weight: bold; text-transform: uppercase; }
        .status-aguardando { background: #fff3cd; color: #856404; }
        .status-pago { background: #d4edda; color: #155724; }
        .status-cancelado { background: #f8d7da; color: #721c24; }

        .status-form { display: flex; gap: 8px; }
        .status-select { padding: 8px; border-radius: 6px; border: 1px solid #ddd; font-size: 0.9em; }
        .btn-update { background: #0d6efd; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85em; }
        
        .btn-details { background: #6c757d; color: white; text-decoration: none; padding: 8px 12px; border-radius: 6px; font-size: 0.85em; }
        .btn-back { display: inline-block; margin-top: 20px; color: #0d6efd; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<header class="admin-header">
    <div class="logo"><a href="principal.php">Painel Administrativo</a></div>
    <div>Olá, <strong><?php echo htmlspecialchars($_SESSION['user']['email']); ?></strong> | <a href="logout.php" style="color: #ffc107; text-decoration: none;">Sair</a></div>
</header>

<div class="container">
    <div class="admin-card">
        <div class="header-flex">
            <h2><i class="fas fa-list-ul"></i> Gerenciamento de Pedidos</h2>
            <div class="filter-group">
                <span>Filtrar:</span>
                <a href="listar_pedidos.php" class="<?php echo $filtro == '' ? 'active' : ''; ?>">Todos</a>
                <a href="listar_pedidos.php?filtro=aguardando pagamento" class="<?php echo $filtro == 'aguardando pagamento' ? 'active' : ''; ?>">Aguardando</a>
                <a href="listar_pedidos.php?filtro=pago" class="<?php echo $filtro == 'pago' ? 'active' : ''; ?>">Pagos</a>
                <a href="listar_pedidos.php?filtro=cancelado" class="<?php echo $filtro == 'cancelado' ? 'active' : ''; ?>">Cancelados</a>
            </div>
        </div>

        <table class="pedidos-table">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Data/Hora</th>
                    <th>Status Atual</th>
                    <th>Alterar Status</th>
                    <th style="text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): 
                    $class = '';
                    if($row['status'] == 'aguardando pagamento') $class = 'status-aguardando';
                    elseif($row['status'] == 'pago') $class = 'status-pago';
                    else $class = 'status-cancelado';
                ?>
                <tr>
                    <td style="font-weight: bold;"><?php echo $row['numero_pedido']; ?></td>
                    <td><?php echo htmlspecialchars($row['nome_cliente']); ?></td>
                    <td style="color: #198754; font-weight: bold;">R$ <?php echo number_format($row['total_com_frete'], 2, ',', '.'); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['data_criacao'])); ?></td>
                    <td><span class="badge <?php echo $class; ?>"><?php echo $row['status']; ?></span></td>
                    <td>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="pedido_id" value="<?php echo $row['id']; ?>">
                            <select name="novo_status" class="status-select">
                                <option value="aguardando pagamento" <?php echo $row['status'] == 'aguardando pagamento' ? 'selected' : ''; ?>>Aguardando</option>
                                <option value="pago" <?php echo $row['status'] == 'pago' ? 'selected' : ''; ?>>Pago</option>
                                <option value="cancelado" <?php echo $row['status'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                            <button type="submit" name="alterar_status" class="btn-update">Alterar</button>
                        </form>
                    </td>
                    <td style="text-align: center;">
                        <a href="detalhes_adm.php?id=<?php echo $row['id']; ?>" class="btn-details">
                            <i class="fas fa-eye"></i> Detalhes
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="principal.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar ao Painel</a>
    </div>
</div>

</body>
</html>