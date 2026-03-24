<?php
session_start();
require_once('conexao.php');

// 1. SEGURANÇA: Só acessa se tiver itens no carrinho e se for CLIENTE logado
if (!isset($_SESSION['carrinho']) || count($_SESSION['carrinho']) == 0) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['cpf'])) {
    // Se não for cliente logado, manda para o login
    header("Location: login_cliente.php");
    exit();
}

$cliente = $_SESSION['user'];
$total_geral = 0;
$itens_resumo = array();

// 2. BUSCA DADOS DOS PRODUTOS PARA O RESUMO
foreach ($_SESSION['carrinho'] as $id => $qtd) {
    $sql = "SELECT * FROM produtos WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $prod = mysqli_fetch_assoc($res);
    
    if ($prod) {
        $subtotal = $prod['valor'] * $qtd;
        $total_geral += $subtotal;
        $prod['qtd'] = $qtd;
        $prod['subtotal'] = $subtotal;
        $itens_resumo[] = $prod;
    }
}

// 3. BUSCA ENDEREÇO DE ENTREGA PADRÃO DO CLIENTE
$sql_end = "SELECT * FROM enderecos_entrega WHERE cliente_id = ? AND status = 'Ativo' LIMIT 1";
$stmt_end = mysqli_prepare($con, $sql_end);
mysqli_stmt_bind_param($stmt_end, "i", $cliente['id']);
mysqli_stmt_execute($stmt_end);
$res_end = mysqli_stmt_get_result($stmt_end);
$endereco = mysqli_fetch_assoc($res_end);

// Caso não tenha endereço de entrega, usamos o de faturamento que está na tabela clientes
if (!$endereco) {
    $endereco = [
        'endereco' => $cliente['endereco_faturamento'],
        'numero' => $cliente['numero_faturamento'],
        'bairro' => $cliente['bairro_faturamento'],
        'cidade' => $cliente['cidade_faturamento'],
        'uf' => $cliente['uf_faturamento'],
        'cep' => $cliente['cep_faturamento']
    ];
}

// O frete vem da sessão definido no carrinho
$valor_frete = $_SESSION['frete_valor'] ?? 0;
$total_com_frete = $total_geral + $valor_frete;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Vitrini Drink's</title>
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

        .container { max-width: 1200px; margin: 40px auto; display: flex; gap: 30px; padding: 0 20px; flex-wrap: wrap; }

        .checkout-main { flex: 2; min-width: 350px; }
        .checkout-section { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .section-title { font-size: 1.2em; font-weight: bold; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: #0d6efd; border-bottom: 2px solid #f8f9fa; padding-bottom: 10px; }

        .address-box { background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #eee; line-height: 1.6; }

        .checkout-sidebar { flex: 1; min-width: 300px; }
        .summary-card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); position: sticky; top: 100px; }
        
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.95em; color: #666; }
        .summary-total { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 20px; border-top: 2px solid #f8f9fa; font-size: 1.5em; font-weight: bold; color: #198754; }

        .btn-confirm { 
            width: 100%; padding: 20px; background: #0d6efd; color: white; border: none; 
            border-radius: 8px; font-size: 1.2em; font-weight: bold; cursor: pointer; 
            margin-top: 25px; transition: 0.3s; display: block; text-align: center; text-decoration: none;
        }
        .btn-confirm:hover { background: #0b5ed7; transform: translateY(-2px); }

        .btn-back { display: block; text-align: center; margin-top: 20px; color: #666; text-decoration: none; font-size: 0.9em; }

        @media (max-width: 800px) { .container { flex-direction: column; } .summary-card { position: static; } }
    </style>
</head>
<body>

<header class="store-header">
    <div class="logo"><a href="index.php">Vitrini Drink's</a></div>
    <div class="header-actions">
        <span><i class="fas fa-lock"></i> Checkout Seguro</span>
    </div>
</header>

<div class="container">
    
    <div class="checkout-main">
        <div class="checkout-section">
            <div class="section-title"><i class="fas fa-user-check"></i> 1. Identificação</div>
            <p><strong>Nome:</strong> <?php echo htmlspecialchars($cliente['nome']); ?></p>
            <p><strong>E-mail:</strong> <?php echo htmlspecialchars($cliente['email']); ?></p>
            <p><strong>CPF:</strong> <?php echo htmlspecialchars($cliente['cpf']); ?></p>
        </div>

        <div class="checkout-section">
            <div class="section-title"><i class="fas fa-map-marker-alt"></i> 2. Endereço de Entrega</div>
            <div class="address-box">
                <p><strong><?php echo $endereco['endereco']; ?>, nº <?php echo $endereco['numero']; ?></strong></p>
                <p><?php echo $endereco['bairro']; ?> - <?php echo $endereco['cidade']; ?>/<?php echo $endereco['uf']; ?></p>
                <p>CEP: <?php echo $endereco['cep']; ?></p>
            </div>
            <a href="endereco_alternativo.php" style="font-size: 0.9em; color: #0d6efd; display: block; margin-top: 15px; font-weight: 600; text-decoration: none;">
                <i class="fas fa-plus-circle"></i> Adicionar outro endereço
            </a>
        </div>
    </div>

    <div class="checkout-sidebar">
        <div class="summary-card">
            <div class="section-title" style="color: #333;"><i class="fas fa-list"></i> Resumo da Compra</div>
            
            <?php foreach($itens_resumo as $item): ?>
                <div class="summary-item">
                    <span><?php echo $item['qtd']; ?>x <?php echo htmlspecialchars($item['nome']); ?></span>
                    <span>R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></span>
                </div>
            <?php endforeach; ?>

            <div class="summary-item" style="margin-top: 20px; border-top: 1px solid #f4f4f4; padding-top: 10px;">
                <span>Subtotal</span>
                <span>R$ <?php echo number_format($total_geral, 2, ',', '.'); ?></span>
            </div>

            <div class="summary-item">
                <span>Frete</span>
                <span>R$ <?php echo number_format($valor_frete, 2, ',', '.'); ?></span>
            </div>

            <div class="summary-total">
                <span>Total</span>
                <span>R$ <?php echo number_format($total_com_frete, 2, ',', '.'); ?></span>
            </div>

            <a href="forma_pagamento.php" class="btn-confirm">
                PROSSEGUIR PARA PAGAMENTO <i class="fas fa-chevron-right"></i>
            </a>
            
            <a href="carrinho.php" class="btn-back"><i class="fas fa-arrow-left"></i> Ajustar carrinho</a>
        </div>
    </div>

</div>

</body>
</html>