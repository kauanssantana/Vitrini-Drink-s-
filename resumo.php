<?php
session_start();
require_once('conexao.php');

if (!isset($_SESSION['user'])) {
    echo "<script>alert('Você precisa estar logado para continuar a compra.'); window.location.href='login_cliente.php';</script>";
    exit();
}

// 1. LÓGICA DE ENDEREÇO
$endereco_id = $_SESSION['endereco_id'] ?? null;
$endereco_display = ""; 

if ($endereco_id != 0 && $endereco_id != null) {
    // Busca o endereço alternativo selecionado
    $query_endereco = "SELECT * FROM enderecos_entrega WHERE id = '$endereco_id'";
    $result_endereco = mysqli_query($con, $query_endereco);
    $endereco = mysqli_fetch_assoc($result_endereco);
    if ($endereco) {
        $endereco_display = $endereco['endereco'] . ', ' . $endereco['numero'] . ' - ' . $endereco['bairro'] . ', ' . $endereco['cidade'] . '/' . $endereco['uf'] . ' | CEP: ' . $endereco['cep'];
    }
} else {
    // Caso seja retirada ou use o endereço principal do cadastro se não houver ID alternativo
    $endereco_display = "Retirada na loja: Avenida Raposo Tavares, km 37 - Sítio Boa Vista, Cotia/SP";
}

// 2. FORMA DE PAGAMENTO
$forma_pagamento = $_SESSION['forma_pagamento'] ?? '';
$forma_pagamento_texto = ($forma_pagamento == 'cartao') ? 'Cartão de Crédito' : 'PIX';

// 3. CÁLCULO DOS TOTAIS
$carrinho = $_SESSION['carrinho'] ?? [];
$subtotal = 0;

// Como seu carrinho agora usa o formato [id => quantidade], buscamos os nomes e preços no banco
$itens_resumo = [];
if (is_array($carrinho)) {
    foreach ($carrinho as $id => $quantidade) {
        $res = mysqli_query($con, "SELECT p.*, i.caminho_imagem FROM produtos p LEFT JOIN imagens_produto i ON p.id = i.produto_id AND i.principal = 1 WHERE p.id = $id");
        $prod = mysqli_fetch_assoc($res);
        if ($prod) {
            $valor_item = $prod['valor'] * $quantidade;
            $subtotal += $valor_item;
            $prod['quantidade_compra'] = $quantidade;
            $prod['total_item'] = $valor_item;
            $itens_resumo[] = $prod;
        }
    }
}

$valor_frete = $_SESSION['frete_valor'] ?? 0; 
$total_com_frete = $subtotal + $valor_frete;

// Salva os totais finais para o processamento do pedido
$_SESSION['subtotal'] = $subtotal;
$_SESSION['total_com_frete'] = $total_com_frete;

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumo do Pedido - Vitrini Drink's</title>
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
        
        .summary-card { background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        
        .section-title { font-size: 1.4em; font-weight: bold; margin-bottom: 25px; color: #212529; border-bottom: 2px solid #f4f4f4; padding-bottom: 15px; display: flex; align-items: center; gap: 10px; }

        .resumo-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .resumo-table th { text-align: left; padding: 12px; color: #666; font-size: 0.85em; text-transform: uppercase; }
        .resumo-table td { padding: 15px 12px; border-bottom: 1px solid #f8f9fa; }
        
        .prod-info { display: flex; align-items: center; gap: 15px; }
        .prod-info img { width: 50px; height: 50px; object-fit: contain; border: 1px solid #eee; border-radius: 5px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-box { background: #f8f9fa; padding: 20px; border-radius: 10px; border: 1px solid #eee; }
        .info-box h4 { font-size: 0.9em; color: #6c757d; text-transform: uppercase; margin-bottom: 8px; }
        .info-box p { font-weight: 600; font-size: 1.05em; color: #333; }

        .totals-section { background: #1a1a1a; color: white; padding: 30px; border-radius: 12px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 1.1em; }
        .grand-total { border-top: 1px solid #333; padding-top: 15px; margin-top: 15px; font-size: 2em; font-weight: 800; color: #ffc107; }

        .btn-group { display: flex; gap: 15px; margin-top: 30px; }
        .btn { flex: 1; padding: 20px; border-radius: 10px; font-size: 1.2em; font-weight: bold; cursor: pointer; text-decoration: none; text-align: center; border: none; transition: 0.3s; }
        .btn-back { background: #e9ecef; color: #495057; }
        .btn-confirm { background: #198754; color: white; }
        .btn-confirm:hover { background: #157347; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3); }
    </style>
</head>
<body>

<header class="store-header">
    <div class="logo"><a href="index.php">Vitrini Drink's</a></div>
    <div class="header-actions">
        <span><i class="fas fa-check-double"></i> Revisão Final do Pedido</span>
    </div>
</header>

<div class="container">
    <div class="summary-card">
        <h2 class="section-title"><i class="fas fa-shopping-cart"></i> Itens do Pedido</h2>
        
        <table class="resumo-table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th style="text-align: center;">Qtd</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens_resumo as $item): ?>
                    <tr>
                        <td class="prod-info">
                            <img src="<?php echo $item['caminho_imagem'] ?? 'vitrine.jpg'; ?>" onerror="this.src='vitrine.jpg'">
                            <span><?php echo htmlspecialchars($item['nome']); ?></span>
                        </td>
                        <td style="text-align: center; font-weight: bold;"><?php echo $item['quantidade_compra']; ?></td>
                        <td style="text-align: right; font-weight: bold;">R$ <?php echo number_format($item['total_item'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="info-grid">
            <div class="info-box">
                <h4><i class="fas fa-truck"></i> Local de Entrega</h4>
                <p><?php echo $endereco_display; ?></p>
            </div>
            <div class="info-box">
                <h4><i class="fas fa-wallet"></i> Forma de Pagamento</h4>
                <p><?php echo $forma_pagamento_texto; ?></p>
            </div>
        </div>

        <div class="totals-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
            </div>
            <div class="total-row">
                <span>Frete:</span>
                <span>R$ <?php echo number_format($valor_frete, 2, ',', '.'); ?></span>
            </div>
            <div class="total-row grand-total">
                <span>Total Geral:</span>
                <span>R$ <?php echo number_format($total_com_frete, 2, ',', '.'); ?></span>
            </div>
        </div>

        <div class="btn-group">
            <a href="forma_pagamento.php" class="btn btn-back">Voltar</a>
            <a href="concluir_compra.php" class="btn btn-confirm">Confirmar e Finalizar <i class="fas fa-check-circle"></i></a>
        </div>
    </div>
</div>

</body>
</html>