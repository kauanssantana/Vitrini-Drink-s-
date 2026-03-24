<?php
session_start();
require_once('conexao.php');

// Verificações de segurança
if (!isset($_SESSION['user'])) {
    header("Location: login_cliente.php");
    exit();
}

if (!isset($_SESSION['frete_tipo'])) {
    header("Location: carrinho.php");
    exit();
}

$forma_pagamento = $_SESSION['forma_pagamento'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $forma_pagamento = $_POST['forma_pagamento'] ?? '';
    $_SESSION['forma_pagamento'] = $forma_pagamento;

    if (empty($forma_pagamento)) {
        echo "<script>alert('Selecione uma forma de pagamento.');</script>";
    } elseif ($forma_pagamento == 'cartao' && (empty($_POST['numero_cartao']) || empty($_POST['codigo_cartao']))) {
        echo "<script>alert('Preencha os dados do cartão corretamente.');</script>";
    } else {
        header("Location: resumo.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - Vitrini Drink's</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
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
            letter-spacing: -1px;
        }

        .store-header .header-actions { 
            display: flex; 
            align-items: center; 
            gap: 30px; 
        }

        .store-header .header-actions span, 
        .store-header .header-actions a { 
            color: white; 
            text-decoration: none; 
            font-weight: 600; 
            font-size: 1.1em; 
        }

        .cart-icon { font-size: 1.4em; }

        .container { max-width: 700px; margin: 50px auto; padding: 0 20px; }
        .payment-card { background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }

        h2 { text-align: center; margin-bottom: 30px; color: #212529; font-size: 1.8em; }

        .payment-methods { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        
        .method-option { 
            border: 2px solid #eee; padding: 25px; border-radius: 12px; cursor: pointer; 
            text-align: center; transition: all 0.3s ease; display: flex; flex-direction: column; align-items: center; gap: 10px;
        }
        .method-option i { font-size: 2.2em; color: #6c757d; }
        .method-option input { display: none; }
        .method-option:hover { border-color: #0d6efd; background: #f0f7ff; }
        .method-option.active { border-color: #0d6efd; background: #f0f7ff; color: #0d6efd; box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15); }
        .method-option.active i { color: #0d6efd; }

        #cartao-form { background: #f8f9fa; padding: 25px; border-radius: 12px; border: 1px solid #eee; margin-bottom: 25px; display: none; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: bold; font-size: 0.9em; color: #495057; }
        .input-group input, .input-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1em; }

        .form-row { display: flex; gap: 15px; }

        .btn-confirm { 
            width: 100%; padding: 20px; background: #198754; color: white; border: none; 
            border-radius: 10px; font-size: 1.2em; font-weight: bold; cursor: pointer; transition: 0.3s; 
        }
        .btn-confirm:hover { background: #157347; transform: translateY(-2px); }
        
        .btn-back { display: block; text-align: center; margin-top: 20px; color: #6c757d; text-decoration: none; }
    </style>
</head>
<body>

<header class="store-header">
    <div class="logo">
        <a href="index.php">Vitrini Drink's</a>
    </div>
    <div class="header-actions">
        <span><i class="fas fa-user-circle"></i> Olá, <?php echo explode('@', $_SESSION['user']['email'])[0]; ?></span>
        <a href="carrinho.php" class="cart-icon">
            <i class="fas fa-shopping-cart"></i>
        </a>
    </div>
</header>

<div class="container">
    <div class="payment-card">
        <h2>Como deseja pagar?</h2>

        <form action="forma_pagamento.php" method="POST" id="formPagamento">
            <div class="payment-methods">
                <label class="method-option <?php echo ($forma_pagamento == 'pix') ? 'active' : ''; ?>">
                    <input type="radio" name="forma_pagamento" value="pix" required <?php echo ($forma_pagamento == 'pix') ? 'checked' : ''; ?>>
                    <i class="fas fa-qrcode"></i>
                    <strong>PIX</strong>
                </label>
                
                <label class="method-option <?php echo ($forma_pagamento == 'cartao') ? 'active' : ''; ?>">
                    <input type="radio" name="forma_pagamento" value="cartao" required <?php echo ($forma_pagamento == 'cartao') ? 'checked' : ''; ?>>
                    <i class="fas fa-credit-card"></i>
                    <strong>Cartão</strong>
                </label>
            </div>

            <div id="cartao-form">
                <div class="input-group">
                    <label>Nome no Cartão</label>
                    <input type="text" name="nome_completo" placeholder="Nome do titular">
                </div>
                <div class="input-group">
                    <label>Número do Cartão</label>
                    <input type="text" name="numero_cartao" placeholder="0000 0000 0000 0000" maxlength="16">
                </div>
                <div class="form-row">
                    <div class="input-group" style="flex: 2;">
                        <label>Validade</label>
                        <input type="month" name="data_vencimento">
                    </div>
                    <div class="input-group" style="flex: 1;">
                        <label>CVV</label>
                        <input type="text" name="codigo_cartao" placeholder="123" maxlength="3">
                    </div>
                </div>
                <div class="input-group">
                    <label>Parcelas</label>
                    <select name="parcelas">
                        <option value="1">1x sem juros</option>
                        <option value="2">2x sem juros</option>
                        <option value="3">3x sem juros</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-confirm">CONFIRMAR E REVISAR</button>
        </form>

        <a href="carrinho.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar ao Carrinho</a>
    </div>
</div>

<script>
    $(document).ready(function() {
        function toggleCartao() {
            if ($('input[name="forma_pagamento"]:checked').val() === 'cartao') {
                $('#cartao-form').slideDown();
                $('#cartao-form input').prop('required', true);
            } else {
                $('#cartao-form').slideUp();
                $('#cartao-form input').prop('required', false);
            }
        }

        $('input[name="forma_pagamento"]').change(function() {
            $('.method-option').removeClass('active');
            $(this).parent().addClass('active');
            toggleCartao();
        });

        // Executa ao carregar para manter estado da sessão
        toggleCartao();
    });
</script>

</body>
</html>