<?php
session_start();
require_once('conexao.php');

// Verifica se o ID foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('Produto não encontrado!'); window.location.href='index.php';</script>";
    exit();
}

$id_produto = (int)$_GET['id'];

$query = "SELECT * FROM produtos WHERE id = ? AND status = 'Ativo'";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $id_produto);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "<script>alert('Este produto não está mais disponível.'); window.location.href='index.php';</script>";
    exit();
}

$query_images = "SELECT * FROM imagens_produto WHERE produto_id = ? ORDER BY principal DESC";
$stmt_img = mysqli_prepare($con, $query_images);
mysqli_stmt_bind_param($stmt_img, "i", $id_produto);
mysqli_stmt_execute($stmt_img);
$images_result = mysqli_stmt_get_result($stmt_img);
$images = mysqli_fetch_all($images_result, MYSQLI_ASSOC);

mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_img);

// Correção do erro no cálculo do carrinho
$qtd_carrinho = isset($_SESSION['carrinho']) ? count($_SESSION['carrinho']) : 0;
$is_logged_in = isset($_SESSION['user']);

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['nome']); ?> - Vitrini Drink's</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.6; }

        .store-header { 
            background-color: #1a1a1a; 
            color: white; 
            padding: 25px 2%; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            position: sticky; 
            top: 0; 
            z-index: 100;
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
        
        .cart-icon-container { position: relative; font-size: 1.5em; }
        .cart-badge { position: absolute; top: -8px; right: -10px; background-color: #dc3545; color: white; font-size: 0.55em; padding: 4px 8px; border-radius: 50%; font-weight: bold; }

        .product-page-container { max-width: 1200px; margin: 50px auto; padding: 0 20px; display: flex; gap: 50px; align-items: flex-start; }

        .product-gallery { flex: 1; min-width: 300px; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .carousel img { width: 100%; height: 400px; object-fit: contain; border-radius: 8px; }
        .slick-prev:before, .slick-next:before { color: #0d6efd; font-size: 24px; }

        .product-info { flex: 1.2; min-width: 300px; display: flex; flex-direction: column; }
        .product-title { font-size: 2.5em; color: #212529; margin-bottom: 15px; line-height: 1.2; }
        .product-rating { color: #ffc107; font-size: 1.2em; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .product-rating span { color: #6c757d; font-size: 0.9em; font-weight: bold; }
        .product-price { font-size: 3em; color: #198754; font-weight: bold; margin-bottom: 20px; }
        .product-description { font-size: 1.1em; color: #555; margin-bottom: 30px; background: #fff; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .product-stock { font-size: 1.1em; color: #495057; margin-bottom: 25px; }
        .product-stock strong { color: #212529; }
        .out-of-stock { color: #dc3545; font-weight: bold; }

        .purchase-area { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; flex-wrap: wrap; }
        .quantity-selector { display: flex; align-items: center; border: 2px solid #ddd; border-radius: 8px; overflow: hidden; background: #fff; }
        .quantity-btn { background: #f8f9fa; border: none; padding: 15px 20px; font-size: 1.2em; font-weight: bold; cursor: pointer; color: #495057; transition: background 0.2s; }
        .quantity-btn:hover { background: #e9ecef; }
        .quantity-input { width: 60px; text-align: center; font-size: 1.2em; font-weight: bold; border: none; border-left: 2px solid #ddd; border-right: 2px solid #ddd; padding: 15px 0; outline: none; }
        .quantity-input::-webkit-outer-spin-button, .quantity-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

        .btn-buy { flex: 1; padding: 18px 30px; background-color: #0d6efd; color: white; border: none; border-radius: 8px; font-size: 1.2em; font-weight: bold; cursor: pointer; transition: background 0.3s, transform 0.1s; display: flex; justify-content: center; align-items: center; gap: 10px; min-width: 250px; }
        .btn-buy:hover { background-color: #0b5ed7; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3); }
        .btn-buy:active { transform: translateY(0); }
        .btn-buy:disabled { background-color: #6c757d; cursor: not-allowed; box-shadow: none; transform: none; }

        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: #6c757d; text-decoration: none; font-weight: bold; transition: color 0.3s; margin-top: 10px; }
        .btn-back:hover { color: #333; text-decoration: underline; }

        @media (max-width: 800px) {
            .product-page-container { flex-direction: column; }
            .product-gallery, .product-info { width: 100%; }
        }
    </style>
</head>
<body>

<header class="store-header">
    <div class="logo">
        <a href="index.php">Vitrini Drink's</a>
    </div>
    <div class="header-actions">
        <?php if ($is_logged_in): ?>
            <a href="area_cliente.php"><i class="fas fa-user-circle"></i> Olá, <?php echo htmlspecialchars($_SESSION['user']['nome'] ?? 'Cliente'); ?></a>
        <?php else: ?>
            <a href="login_cliente.php"><i class="fas fa-sign-in-alt"></i> Entrar / Cadastrar</a>
        <?php endif; ?>
        
        <a href="carrinho.php" class="cart-icon-container">
            <i class="fas fa-shopping-cart"></i>
            <?php if ($qtd_carrinho > 0): ?>
                <span class="cart-badge"><?php echo $qtd_carrinho; ?></span>
            <?php endif; ?>
        </a>
    </div>
</header>

<div class="product-page-container">
    
    <div class="product-gallery">
        <?php if (count($images) > 0): ?>
            <div class="carousel">
                <?php foreach ($images as $img): ?>
                    <div>
                        <img src="<?php echo htmlspecialchars($img['caminho_imagem']); ?>" alt="Imagem do Produto" onerror="this.src='vitrine.jpg'">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 50px 0; color: #999;">
                <img src="vitrine.jpg" alt="Sem Imagem" style="max-width: 100%; opacity: 0.5;">
            </div>
        <?php endif; ?>
    </div>

    <div class="product-info">
        <h1 class="product-title"><?php echo htmlspecialchars($product['nome']); ?></h1>
        
        <div class="product-rating">
            <?php
                $avaliacao = (float)$product['avaliacao'];
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $avaliacao) echo '<i class="fas fa-star"></i>';
                    else echo '<i class="far fa-star"></i>';
                }
            ?>
            <span>(<?php echo number_format($avaliacao, 1, ',', '.'); ?> / 5.0)</span>
        </div>

        <div class="product-price">
            R$ <?php echo number_format($product['valor'], 2, ',', '.'); ?>
        </div>

        <div class="product-description">
            <?php echo nl2br(htmlspecialchars($product['descricao'])); ?>
        </div>

        <div class="product-stock">
            <i class="fas fa-boxes"></i> Estoque: 
            <?php if ($product['quantidade'] > 0): ?>
                <strong><?php echo $product['quantidade']; ?> unidades disponíveis</strong>
            <?php else: ?>
                <span class="out-of-stock">Produto Esgotado</span>
            <?php endif; ?>
        </div>

        <form action="carrinho.php?acao=add&id=<?php echo $product['id']; ?>" method="POST">
            <div class="purchase-area">
                <div class="quantity-selector">
                    <button type="button" class="quantity-btn" onclick="diminuirQtd()">-</button>
                    <input type="number" id="quantidade" name="quantidade" class="quantity-input" value="1" min="1" max="<?php echo $product['quantidade']; ?>" readonly>
                    <button type="button" class="quantity-btn" onclick="aumentarQtd(<?php echo $product['quantidade']; ?>)">+</button>
                </div>

                <?php if ($product['quantidade'] > 0): ?>
                    <button type="submit" class="btn-buy"><i class="fas fa-cart-plus"></i> Adicionar ao Carrinho</button>
                <?php else: ?>
                    <button type="button" class="btn-buy" disabled><i class="fas fa-times-circle"></i> Indisponível</button>
                <?php endif; ?>
            </div>
        </form>

        <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Continuar Comprando</a>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<script>
    $(document).ready(function() {
        if ($('.carousel > div').length > 1) {
            $('.carousel').slick({
                infinite: true,
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: true,
                dots: true,
                autoplay: true,
                autoplaySpeed: 3000
            });
        }
    });

    function aumentarQtd(maxEstoque) {
        var input = document.getElementById('quantidade');
        var atual = parseInt(input.value);
        if (atual < maxEstoque) {
            input.value = atual + 1;
        } else {
            alert('Atenção: A quantidade desejada ultrapassa o nosso estoque disponível (' + maxEstoque + ' unidades).');
        }
    }

    function diminuirQtd() {
        var input = document.getElementById('quantidade');
        var atual = parseInt(input.value);
        if (atual > 1) {
            input.value = atual - 1;
        }
    }
</script>

</body>
</html>