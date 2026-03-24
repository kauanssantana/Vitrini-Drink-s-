<?php
session_start();
require_once('conexao.php');

if (!isset($_SESSION['user'])) {
    echo "<script>alert('Você precisa estar logado para finalizar a compra.'); window.location.href='login_cliente.php';</script>";
    exit();
}

// 1. COLETA DE DADOS DA SESSÃO
$cliente_id = $_SESSION['user']['id'];
$endereco_id_session = $_SESSION['endereco_id'] ?? null; 
$forma_pagamento = $_SESSION['forma_pagamento'] ?? 'Não informada';
$subtotal = $_SESSION['subtotal'] ?? 0; 
$valor_frete = $_SESSION['frete_valor'] ?? 0; // Nome da variável conforme nosso carrinho.php
$total_com_frete = $_SESSION['total_com_frete'] ?? 0;
$carrinho = $_SESSION['carrinho'] ?? [];

if (empty($carrinho)) {
    header("Location: index.php");
    exit();
}

$mostrar_qrcode = ($forma_pagamento == 'pix');
$endereco_sql_value = empty($endereco_id_session) ? "NULL" : "'$endereco_id_session'";
$numero_pedido = 'PED' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

// 2. SALVAR PEDIDO NO BANCO (USANDO PREPARED STATEMENTS PARA SEGURANÇA)
$query_pedido = "INSERT INTO pedidos (numero_pedido, cliente_id, endereco_id, forma_pagamento, subtotal, valor_frete, total_com_frete, status, data_criacao) 
                 VALUES (?, ?, $endereco_sql_value, ?, ?, ?, ?, 'aguardando pagamento', NOW())";

$stmt_ped = mysqli_prepare($con, $query_pedido);
mysqli_stmt_bind_param($stmt_ped, "sisddd", $numero_pedido, $cliente_id, $forma_pagamento, $subtotal, $valor_frete, $total_com_frete);

if (mysqli_stmt_execute($stmt_ped)) {
    $pedido_id = mysqli_insert_id($con);

    // 3. SALVAR ITENS DO PEDIDO
    // Como nosso carrinho usa [id => quantidade], buscamos os preços atuais no banco
    foreach ($carrinho as $prod_id => $qtd) {
        $res_p = mysqli_query($con, "SELECT valor FROM produtos WHERE id = $prod_id");
        $prod_data = mysqli_fetch_assoc($res_p);
        
        if ($prod_data) {
            $preco = $prod_data['valor'];
            $sub_item = $preco * $qtd;

            $query_item = "INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco, subtotal) VALUES (?, ?, ?, ?, ?)";
            $stmt_item = mysqli_prepare($con, $query_item);
            mysqli_stmt_bind_param($stmt_item, "iiidd", $pedido_id, $prod_id, $qtd, $preco, $sub_item);
            mysqli_stmt_execute($stmt_item);
        }
    }

    $sucesso = true;
    $mensagem = "Seu pedido foi gerado com sucesso!";
} else {
    $sucesso = false;
    $mensagem = "Erro ao processar o pedido: " . mysqli_error($con);
}

// 4. LIMPEZA TOTAL DA SESSÃO DO CARRINHO
unset($_SESSION['carrinho'], $_SESSION['forma_pagamento'], $_SESSION['endereco_id'], $_SESSION['frete_valor'], $_SESSION['subtotal'], $_SESSION['total_com_frete'], $_SESSION['frete_tipo']);

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Finalizado - Vitrini Drink's</title>
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

        .container { max-width: 800px; margin: 60px auto; padding: 0 20px; text-align: center; }
        
        .success-card { background: #fff; padding: 50px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        
        .icon-box { width: 100px; height: 100px; background: #d4edda; color: #198754; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3em; margin: 0 auto 30px; }
        
        h1 { font-size: 2.2em; color: #212529; margin-bottom: 15px; }
        .order-number { font-size: 1.2em; color: #6c757d; margin-bottom: 40px; }
        .order-number strong { color: #0d6efd; }

        /* PIX BOX */
        .pix-container { background: #f8f9fa; border: 2px dashed #32bcad; padding: 30px; border-radius: 15px; margin: 30px 0; }
        .pix-container h3 { color: #32bcad; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .pix-qr { width: 220px; background: white; padding: 10px; border: 1px solid #eee; border-radius: 10px; margin-bottom: 15px; }

        .btn-home { display: inline-block; background: #0d6efd; color: white; padding: 18px 40px; border-radius: 10px; text-decoration: none; font-weight: bold; font-size: 1.1em; transition: 0.3s; margin-top: 20px; }
        .btn-home:hover { background: #0b5ed7; transform: translateY(-3px); }
    </style>
</head>
<body>

<header class="store-header">
    <div class="logo"><a href="index.php">Vitrini Drink's</a></div>
</header>

<div class="container">
    <div class="success-card">
        <?php if ($sucesso): ?>
            <div class="icon-box"><i class="fas fa-check"></i></div>
            <h1><?php echo $mensagem; ?></h1>
            <p class="order-number">Código do Pedido: <strong><?php echo $numero_pedido; ?></strong></p>

            <?php if ($mostrar_qrcode): ?>
                <div class="pix-container">
                    <h3><i class="fas fa-qrcode"></i> Pagamento via PIX</h3>
                    <p style="margin-bottom: 20px;">Escaneie o código abaixo para pagar R$ <?php echo number_format($total_com_frete, 2, ',', '.'); ?></p>
                    <img src="qr.png" alt="QR Code" class="pix-qr" onerror="this.style.display='none'">
                    <p style="font-size: 0.9em; color: #666;"><i class="fas fa-info-circle"></i> O seu pedido será enviado após a confirmação do pagamento.</p>
                </div>
            <?php else: ?>
                <div style="padding: 20px; background: #eef4ff; border-radius: 10px; margin: 30px 0;">
                    <p style="color: #055160;"><i class="fas fa-credit-card"></i> Pagamento via <strong>Cartão</strong> em processamento.</p>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="icon-box" style="background: #f8d7da; color: #dc3545;"><i class="fas fa-times"></i></div>
            <h1>Ops! Algo deu errado</h1>
            <p><?php echo $mensagem; ?></p>
        <?php endif; ?>

        <a href="index.php" class="btn-home"><i class="fas fa-shopping-bag"></i> VOLTAR PARA A LOJA</a>
    </div>
</div>

</body>
</html>