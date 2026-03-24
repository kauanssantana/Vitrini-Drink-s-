<?php
session_start();
require_once('conexao.php');

// 1. LÓGICA DE MANIPULAÇÃO DO CARRINHO (AÇÕES)
if (isset($_GET['acao'])) {
    $id = (int)$_GET['id'];

    // ADICIONAR ITEM OU AUMENTAR QUANTIDADE
    if ($_GET['acao'] == 'add') {
        $qtd = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 1;
        
        // Verifica estoque antes de adicionar
        $check_stock = mysqli_query($con, "SELECT quantidade FROM produtos WHERE id = $id");
        $prod_stock = mysqli_fetch_assoc($check_stock);

        if ($prod_stock && $prod_stock['quantidade'] >= $qtd) {
            if (!isset($_SESSION['carrinho'])) $_SESSION['carrinho'] = array();
            
            if (isset($_SESSION['carrinho'][$id])) {
                $_SESSION['carrinho'][$id] += $qtd;
            } else {
                $_SESSION['carrinho'][$id] = $qtd;
            }
            // Baixa no estoque real
            mysqli_query($con, "UPDATE produtos SET quantidade = quantidade - $qtd WHERE id = $id");
        } else {
            echo "<script>alert('Quantidade indisponível no estoque!');</script>";
        }
        header("Location: carrinho.php");
        exit();
    }

    // DIMINUIR QUANTIDADE (BOTÃO -)
    if ($_GET['acao'] == 'sub') {
        if (isset($_SESSION['carrinho'][$id])) {
            if ($_SESSION['carrinho'][$id] > 1) {
                $_SESSION['carrinho'][$id]--;
                // Devolve 1 ao estoque
                mysqli_query($con, "UPDATE produtos SET quantidade = quantidade + 1 WHERE id = $id");
            } else {
                // Se for 1 e diminuir, remove o item
                unset($_SESSION['carrinho'][$id]);
                mysqli_query($con, "UPDATE produtos SET quantidade = quantidade + 1 WHERE id = $id");
            }
        }
        header("Location: carrinho.php");
        exit();
    }

    // REMOVER ITEM COMPLETAMENTE (ÍCONE LIXEIRA)
    if ($_GET['acao'] == 'del') {
        if (isset($_SESSION['carrinho'][$id])) {
            $qtd_devolver = $_SESSION['carrinho'][$id];
            unset($_SESSION['carrinho'][$id]);
            // Devolve tudo ao estoque
            mysqli_query($con, "UPDATE produtos SET quantidade = quantidade + $qtd_devolver WHERE id = $id");
        }
        header("Location: carrinho.php");
        exit();
    }
}

// 2. LÓGICA DE FRETE
$opcoes_frete = ['full' => 10.00, 'retirar' => 0.00];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['frete_tipo'])) {
    $tipo = $_POST['frete_tipo'];
    if (array_key_exists($tipo, $opcoes_frete)) {
        $_SESSION['frete_tipo'] = $tipo;
        $_SESSION['frete_valor'] = $opcoes_frete[$tipo];
    }
}

$frete_tipo = $_SESSION['frete_tipo'] ?? '';
$valor_frete = $_SESSION['frete_valor'] ?? 0;

$itens_carrinho = array();
$subtotal = 0;

if (isset($_SESSION['carrinho']) && count($_SESSION['carrinho']) > 0) {
    foreach ($_SESSION['carrinho'] as $id => $qtd) {
        $res = mysqli_query($con, "SELECT p.*, i.caminho_imagem FROM produtos p 
                                   LEFT JOIN imagens_produto i ON p.id = i.produto_id AND i.principal = 1 
                                   WHERE p.id = $id");
        $produto = mysqli_fetch_assoc($res);
        if ($produto) {
            $item_total = $produto['valor'] * $qtd;
            $subtotal += $item_total;
            $produto['qtd_carrinho'] = $qtd;
            $produto['subtotal_item'] = $item_total;
            $itens_carrinho[] = $produto;
        }
    }
} else {
    unset($_SESSION['frete_tipo'], $_SESSION['frete_valor']);
}

$total_geral = $subtotal + $valor_frete;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Carrinho - Vitrini Drink's</title>
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
        .store-header .header-actions { display: flex; align-items: center; gap: 30px; }
        .store-header .header-actions a { color: white; text-decoration: none; font-weight: 600; font-size: 1.1em; transition: color 0.3s; }
        .store-header .header-actions a:hover { color: #ffc107; }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 2.5fr 1fr; gap: 30px; }
        .cart-card, .summary-card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        .cart-table { width: 100%; border-collapse: collapse; }
        .cart-table th { text-align: left; padding-bottom: 15px; border-bottom: 2px solid #f4f4f4; color: #666; font-size: 0.9em; }
        .cart-table td { padding: 20px 0; border-bottom: 1px solid #f4f4f4; }
        
        .prod-info { display: flex; align-items: center; gap: 15px; }
        .prod-info img { width: 75px; height: 75px; object-fit: contain; border-radius: 8px; border: 1px solid #eee; }
        
        /* BOTÕES DE QUANTIDADE */
        .qty-control { display: flex; align-items: center; gap: 12px; justify-content: center; }
        .btn-qty { text-decoration: none; background: #e9ecef; color: #333; padding: 5px 12px; border-radius: 6px; font-weight: bold; transition: 0.2s; }
        .btn-qty:hover { background: #dee2e6; }
        
        .btn-del { color: #dc3545; font-size: 1.2em; transition: 0.3s; }
        .btn-del:hover { color: #a71d2a; }

        .frete-box { margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; border: 1px solid #eee; }
        .frete-option { display: block; margin-bottom: 12px; cursor: pointer; font-size: 0.95em; }
        .frete-option input { margin-right: 10px; }
        
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .total-row { border-top: 2px solid #f4f4f4; padding-top: 15px; font-size: 1.6em; font-weight: 800; color: #198754; }
        
        .btn-finish { width: 100%; padding: 20px; background: #0d6efd; color: white; border: none; border-radius: 10px; font-size: 1.2em; font-weight: bold; cursor: pointer; margin-top: 20px; transition: 0.3s; }
        .btn-finish:hover { background: #0b5ed7; transform: translateY(-2px); }

        .btn-continue { 
            display: block; 
            width: 100%; 
            padding: 15px; 
            background: #f8f9fa; 
            color: #495057; 
            text-align: center; 
            text-decoration: none; 
            border-radius: 10px; 
            font-size: 1.1em; 
            font-weight: bold; 
            margin-top: 10px; 
            border: 1px solid #ddd;
            transition: 0.3s;
        }
        .btn-continue:hover { background: #e9ecef; color: #1a1a1a; }

        @media (max-width: 1000px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<header class="store-header">
    <div class="logo"><a href="index.php">Vitrini Drink's</a></div>
    <div class="header-actions">
        <?php if (isset($_SESSION['user'])): ?>
            <a href="area_cliente.php"><i class="fas fa-user-circle"></i> Olá, <?php echo explode('@', $_SESSION['user']['email'])[0]; ?></a>
        <?php endif; ?>
        <a href="index.php"><i class="fas fa-shopping-bag"></i> Continuar Comprando</a>
    </div>
</header>

<div class="container">
    <?php if (empty($itens_carrinho)): ?>
        <div class="cart-card" style="grid-column: span 2; text-align: center; padding: 80px 20px;">
            <i class="fas fa-shopping-cart fa-5x" style="color: #eee; margin-bottom: 25px;"></i>
            <h2 style="color: #666;">O seu carrinho está vazio.</h2>
            <a href="index.php" class="btn-finish" style="display: inline-block; max-width: 280px; text-decoration: none;">Explorar Bebidas</a>
        </div>
    <?php else: ?>
        <div class="cart-card">
            <h2 style="margin-bottom: 25px;"><i class="fas fa-shopping-basket"></i> Itens Selecionados</h2>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>PRODUTO</th>
                        <th style="text-align: center;">QUANTIDADE</th>
                        <th style="text-align: right;">SUBTOTAL</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens_carrinho as $item): ?>
                        <tr>
                            <td class="prod-info">
                                <img src="<?php echo $item['caminho_imagem'] ?? 'vitrine.jpg'; ?>" onerror="this.src='vitrine.jpg'">
                                <div>
                                    <strong style="font-size: 1.1em;"><?php echo htmlspecialchars($item['nome']); ?></strong><br>
                                    <small style="color: #888;">Preço unitário: R$ <?php echo number_format($item['valor'], 2, ',', '.'); ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="qty-control">
                                    <a href="carrinho.php?acao=sub&id=<?php echo $item['id']; ?>" class="btn-qty">-</a>
                                    <span style="font-weight: bold; font-size: 1.1em; min-width: 25px; text-align: center;"><?php echo $item['qtd_carrinho']; ?></span>
                                    <a href="carrinho.php?acao=add&id=<?php echo $item['id']; ?>" class="btn-qty">+</a>
                                </div>
                            </td>
                            <td style="text-align: right; font-weight: 800; font-size: 1.1em;">
                                R$ <?php echo number_format($item['subtotal_item'], 2, ',', '.'); ?>
                            </td>
                            <td style="text-align: center; padding-left: 20px;">
                                <a href="carrinho.php?acao=del&id=<?php echo $item['id']; ?>" class="btn-del" title="Remover"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="summary-card">
            <h3 style="margin-bottom: 20px; border-bottom: 2px solid #f8f9fa; padding-bottom: 10px;">Resumo</h3>
            <div class="summary-row">
                <span>Itens:</span>
                <span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
            </div>

            <div class="frete-box">
                <p style="font-weight: 700; margin-bottom: 15px;"><i class="fas fa-truck"></i> Método de Envio</p>
                <form method="POST">
                    <label class="frete-option">
                        <input type="radio" name="frete_tipo" value="full" <?php if($frete_tipo == 'full') echo 'checked'; ?> onchange="this.form.submit()">
                        Entrega Full (R$ 10,00)
                    </label>
                    <label class="frete-option">
                        <input type="radio" name="frete_tipo" value="retirar" <?php if($frete_tipo == 'retirar') echo 'checked'; ?> onchange="this.form.submit()">
                        Retirar na Loja (Grátis)
                    </label>
                </form>
            </div>

            <div class="summary-row" style="margin-top: 20px;">
                <span>Frete:</span>
                <span style="color: #198754; font-weight: 600;">R$ <?php echo number_format($valor_frete, 2, ',', '.'); ?></span>
            </div>

            <div class="summary-row total-row">
                <span>Total:</span>
                <span>R$ <?php echo number_format($total_geral, 2, ',', '.'); ?></span>
            </div>

            <button class="btn-finish" onclick="validarFinalizacao()">
                FINALIZAR COMPRA <i class="fas fa-arrow-right"></i>
            </button>
            
            <a href="index.php" class="btn-continue">
                <i class="fas fa-undo-alt"></i> Voltar às Compras
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
function validarFinalizacao() {
    <?php if (!isset($_SESSION['user'])): ?>
        alert("Por favor, faça login para continuar!");
        window.location.href = 'login_cliente.php';
        return;
    <?php endif; ?>

    const freteEscolhido = "<?php echo $frete_tipo; ?>";
    if (freteEscolhido === "") {
        alert("Por favor, selecione uma opção de frete!");
        return;
    }

    // Redirecionamento condicional baseado na escolha do frete
    window.location.href = (freteEscolhido === 'retirar') ? 'forma_pagamento.php' : 'checkout.php';
}
</script>
</body>
</html>