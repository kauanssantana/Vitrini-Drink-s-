<?php
session_start();
require_once('conexao.php');

if (!isset($_SESSION['user'])) {
    echo "<script>alert('Você precisa estar logado para visualizar os detalhes do pedido.'); window.location.href='login_cliente.php';</script>";
    exit();
}

if (!isset($_GET['id'])) {
    echo "<script>alert('Pedido não encontrado.'); window.location.href='meus_pedidos.php';</script>";
    exit();
}

$id_pedido = mysqli_real_escape_string($con, $_GET['id']);
$cliente_id = $_SESSION['user']['id'];

// 1. BUSCA DADOS GERAIS DO PEDIDO
$query_pedido = "SELECT * FROM pedidos WHERE id = '$id_pedido' AND cliente_id = '$cliente_id'";
$result_pedido = mysqli_query($con, $query_pedido);
$pedido = mysqli_fetch_assoc($result_pedido);

if (!$pedido) {
    echo "<script>alert('Pedido não encontrado.'); window.location.href='meus_pedidos.php';</script>";
    exit();
}

// 2. BUSCA ITENS DO PEDIDO COM IMAGENS
$query_itens = "SELECT ip.*, p.nome, pi.caminho_imagem 
                FROM itens_pedido ip
                JOIN produtos p ON ip.produto_id = p.id
                LEFT JOIN imagens_produto pi ON pi.produto_id = p.id AND pi.principal = 1
                WHERE ip.pedido_id = '$id_pedido'";
$result_itens = mysqli_query($con, $query_itens);

// 3. BUSCA ENDEREÇO (Trata caso de retirada/nulo)
$endereco_display = "Retirada na loja: Avenida Raposo Tavares, km 37 - Sítio Boa Vista, Cotia/SP";
if (!empty($pedido['endereco_id'])) {
    $query_endereco = "SELECT * FROM enderecos_entrega WHERE id = '{$pedido['endereco_id']}'";
    $result_endereco = mysqli_query($con, $query_endereco);
    $end = mysqli_fetch_assoc($result_endereco);
    if ($end) {
        $endereco_display = $end['endereco'] . ', ' . $end['numero'] . ' - ' . $end['bairro'] . ', ' . $end['cidade'] . '/' . $end['uf'] . ' | CEP: ' . $end['cep'];
    }
}

$forma_pagamento_texto = $pedido['forma_pagamento'] == 'cartao' ? 'Cartão de Crédito' : strtoupper($pedido['forma_pagamento']);

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido - Vitrini Drink's</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; color: #333; padding-bottom: 50px; }

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
        .store-header .header-actions span { color: white; font-weight: 600; font-size: 1.1em; }

        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        
        .details-card { background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        
        .main-title { font-size: 1.8em; font-weight: bold; margin-bottom: 30px; display: flex; align-items: center; gap: 15px; border-bottom: 2px solid #f4f4f4; padding-bottom: 15px; }

        .items-list { margin-bottom: 40px; }
        .item-row { display: flex; align-items: center; padding: 15px 0; border-bottom: 1px solid #f8f9fa; }
        .item-img { width: 60px; height: 60px; object-fit: contain; border: 1px solid #eee; border-radius: 8px; margin-right: 20px; }
        .item-details { flex: 1; }
        .item-name { font-weight: bold; font-size: 1.1em; }
        .item-meta { color: #666; font-size: 0.9em; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 35px; }
        .info-box { background: #f8f9fa; padding: 20px; border-radius: 10px; border: 1px solid #eee; }
        .info-box h4 { font-size: 0.85em; color: #888; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px; }
        .info-box p { font-weight: 600; color: #333; }

        .totals-box { background: #1a1a1a; color: white; padding: 30px; border-radius: 12px; }
        .total-line { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .grand-total { border-top: 1px solid #333; padding-top: 15px; margin-top: 15px; font-size: 1.8em; font-weight: 800; color: #ffc107; }

        .btn-footer { display: flex; justify-content: center; margin-top: 30px; }
        .btn-back { padding: 15px 40px; background: #0d6efd; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; transition: 0.3s; }
        .btn-back:hover { background: #0b5ed7; transform: translateY(-2px); }
    </style>
</head>
<body>

<header class="store-header">
    <div class="logo"><a href="index.php">Vitrini Drink's</a></div>
    <div class="header-actions">
        <span><i class="fas fa-history"></i> Detalhes do Pedido</span>
    </div>
</header>

<div class="container">
    <div class="details-card">
        <h1 class="main-title"><i class="fas fa-file-invoice"></i> Pedido Nº <?php echo $pedido['numero_pedido']; ?></h1>

        <div class="items-list">
            <?php while ($item = mysqli_fetch_assoc($result_itens)): ?>
                <div class="item-row">
                    <img src="<?php echo $item['caminho_imagem'] ?? 'vitrine.jpg'; ?>" onerror="this.src='vitrine.jpg'" class="item-img">
                    <div class="item-details">
                        <div class="item-name"><?php echo htmlspecialchars($item['nome']); ?></div>
                        <div class="item-meta">
                            Quantidade: <?php echo $item['quantidade']; ?> | 
                            Unitário: R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?>
                        </div>
                    </div>
                    <div style="font-weight: bold; font-size: 1.1em;">
                        R$ <?php echo number_format($item['preco'] * $item['quantidade'], 2, ',', '.'); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h4><i class="fas fa-map-marker-alt"></i> Local de Entrega</h4>
                <p><?php echo $endereco_display; ?></p>
            </div>
            <div class="info-box">
                <h4><i class="fas fa-wallet"></i> Forma de Pagamento</h4>
                <p><?php echo $forma_pagamento_texto; ?></p>
            </div>
            <div class="info-box">
                <h4><i class="fas fa-calendar-alt"></i> Data da Compra</h4>
                <p><?php echo date('d/m/Y H:i', strtotime($pedido['data_criacao'])); ?></p>
            </div>
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Status do Pedido</h4>
                <p style="color: #0d6efd;"><?php echo strtoupper($pedido['status']); ?></p>
            </div>
        </div>

        <div class="totals-box">
            <div class="total-line">
                <span>Subtotal:</span>
                <span>R$ <?php echo number_format($pedido['subtotal'], 2, ',', '.'); ?></span>
            </div>
            <div class="total-line">
                <span>Frete:</span>
                <span>R$ <?php echo number_format($pedido['valor_frete'], 2, ',', '.'); ?></span>
            </div>
            <div class="total-line grand-total">
                <span>TOTAL DO PEDIDO:</span>
                <span>R$ <?php echo number_format($pedido['total_com_frete'], 2, ',', '.'); ?></span>
            </div>
        </div>

        <div class="btn-footer">
            <a href="meus_pedidos.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar para Meus Pedidos</a>
        </div>
    </div>
</div>

</body>
</html>