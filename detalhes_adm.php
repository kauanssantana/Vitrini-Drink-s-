<?php
session_start();
require_once('conexao.php');

// SEGURANÇA: Permite tanto Administrador quanto Estoquista
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['grupo'], ['Administrador', 'Estoquista'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "<script>alert('Pedido não encontrado.'); window.location.href='listar_pedidos.php';</script>";
    exit();
}

$id_pedido = mysqli_real_escape_string($con, $_GET['id']);

// 1. BUSCA DADOS GERAIS DO PEDIDO
$query_pedido = "SELECT * FROM pedidos WHERE id = '$id_pedido'";
$result_pedido = mysqli_query($con, $query_pedido);
$pedido = mysqli_fetch_assoc($result_pedido);

if (!$pedido) {
    echo "<script>alert('Pedido não encontrado.'); window.location.href='listar_pedidos.php';</script>";
    exit();
}

// 2. BUSCA ITENS DO PEDIDO COM IMAGENS
$query_itens = "SELECT ip.*, p.nome, pi.caminho_imagem 
                FROM itens_pedido ip
                JOIN produtos p ON ip.produto_id = p.id
                LEFT JOIN imagens_produto pi ON pi.produto_id = p.id AND pi.principal = 1
                WHERE ip.pedido_id = '$id_pedido'";
$result_itens = mysqli_query($con, $query_itens);

// 3. BUSCA ENDEREÇO (Trata retirada ou entrega)
$endereco_display = "Retirada na Loja (Sítio Boa Vista - Cotia/SP)";
if (!empty($pedido['endereco_id'])) {
    $query_endereco = "SELECT * FROM enderecos_entrega WHERE id = '{$pedido['endereco_id']}'";
    $result_endereco = mysqli_query($con, $query_endereco);
    $end = mysqli_fetch_assoc($result_endereco);
    if ($end) {
        $endereco_display = $end['endereco'] . ', ' . $end['numero'] . ' - ' . $end['bairro'] . ', ' . $end['cidade'] . '/' . $end['uf'] . ' | CEP: ' . $end['cep'];
    }
}

$forma_pagamento = $pedido['forma_pagamento'] == 'cartao' ? 'Cartão de Crédito' : 'Boleto / Outros';

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido - Administração</title>
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

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        .details-card { background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        
        .main-title { font-size: 1.8em; font-weight: bold; margin-bottom: 30px; display: flex; align-items: center; gap: 15px; border-bottom: 2px solid #f4f4f4; padding-bottom: 15px; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 35px; }
        .items-table th { text-align: left; padding: 15px; background: #f8f9fa; color: #666; font-size: 0.85em; text-transform: uppercase; }
        .items-table td { padding: 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        .prod-img { width: 60px; height: 60px; object-fit: contain; border: 1px solid #eee; border-radius: 8px; margin-right: 15px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 35px; }
        .info-box { background: #f8f9fa; padding: 20px; border-radius: 10px; border: 1px solid #eee; }
        .info-box h4 { font-size: 0.85em; color: #888; text-transform: uppercase; margin-bottom: 10px; }
        .info-box p { font-weight: 600; color: #212529; line-height: 1.5; }

        .totals-section { background: #1a1a1a; color: white; padding: 30px; border-radius: 12px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .grand-total { border-top: 1px solid #333; padding-top: 15px; margin-top: 15px; font-size: 1.8em; font-weight: 800; color: #ffc107; }

        .btn-footer { display: flex; justify-content: center; margin-top: 30px; }
        .btn-back { padding: 15px 40px; background: #0d6efd; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; transition: 0.3s; }
        .btn-back:hover { background: #0b5ed7; transform: translateY(-2px); }
    </style>
</head>
<body>

<header class="admin-header">
    <div class="logo"><a href="principal.php">Vitrini Drink's</a></div>
    <div>ID Pedido: <strong>#<?php echo $pedido['numero_pedido']; ?></strong></div>
</header>

<div class="container">
    <div class="details-card">
        <h1 class="main-title"><i class="fas fa-file-invoice"></i> Detalhes do Pedido Nº <?php echo $pedido['numero_pedido']; ?></h1>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th style="text-align: center;">Quantidade</th>
                    <th style="text-align: right;">Preço Unit.</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = mysqli_fetch_assoc($result_itens)): ?>
                    <tr>
                        <td style="display: flex; align-items: center;">
                            <img src="<?php echo $item['caminho_imagem'] ?? 'vitrine.jpg'; ?>" onerror="this.src='vitrine.jpg'" class="prod-img">
                            <span style="font-weight: bold;"><?php echo htmlspecialchars($item['nome']); ?></span>
                        </td>
                        <td style="text-align: center; font-weight: bold; font-size: 1.1em;"><?php echo $item['quantidade']; ?></td>
                        <td style="text-align: right;">R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                        <td style="text-align: right; font-weight: bold;">R$ <?php echo number_format($item['preco'] * $item['quantidade'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="info-grid">
            <div class="info-box">
                <h4><i class="fas fa-map-marker-alt"></i> Destino / Endereço</h4>
                <p><?php echo $endereco_display; ?></p>
            </div>
            <div class="info-box">
                <h4><i class="fas fa-wallet"></i> Pagamento</h4>
                <p><?php echo $forma_pagamento; ?></p>
                <p style="color: #0d6efd; font-size: 0.9em; margin-top: 5px;">Status: <?php echo strtoupper($pedido['status']); ?></p>
            </div>
        </div>

        <div class="totals-section">
            <div class="total-row">
                <span>Subtotal dos Itens:</span>
                <span>R$ <?php echo number_format($pedido['subtotal'], 2, ',', '.'); ?></span>
            </div>
            <div class="total-row">
                <span>Custo de Frete:</span>
                <span>R$ <?php echo number_format($pedido['valor_frete'], 2, ',', '.'); ?></span>
            </div>
            <div class="total-row grand-total">
                <span>TOTAL A RECEBER:</span>
                <span>R$ <?php echo number_format($pedido['total_com_frete'], 2, ',', '.'); ?></span>
            </div>
        </div>

        <div class="btn-footer">
            <a href="listar_pedidos.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar para a Listagem</a>
        </div>
    </div>
</div>

</body>
</html>