<?php
session_start();

// SEGURANÇA: Apenas Administrador pode acessar esta tela
if (!isset($_SESSION['user']) || $_SESSION['user']['grupo'] !== 'Administrador') {
    header("Location: login.php");
    exit();
}

require_once('conexao.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('Produto inválido ou não encontrado.'); window.location.href='listar_produto.php';</script>";
    exit();
}

$id_produto = (int)$_GET['id'];

// SEGURANÇA: Busca do produto blindada
$query = "SELECT * FROM produtos WHERE id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $id_produto);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "<script>alert('Produto não encontrado no banco de dados.'); window.location.href='listar_produto.php';</script>";
    exit();
}

// SEGURANÇA: Busca das imagens blindada
$query_images = "SELECT * FROM imagens_produto WHERE produto_id = ? ORDER BY principal DESC";
$stmt_img = mysqli_prepare($con, $query_images);
mysqli_stmt_bind_param($stmt_img, "i", $id_produto);
mysqli_stmt_execute($stmt_img);
$images_result = mysqli_stmt_get_result($stmt_img);
$images = mysqli_fetch_all($images_result, MYSQLI_ASSOC);

mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_img);
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Produto - Vitrini Drink's</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
    
    <style>
        body {
            background-color: #f4f6f9;
            padding: 40px 20px;
        }

        .admin-card {
            background: #fff;
            max-width: 1000px;
            width: 100%;
            margin: 0 auto; 
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .admin-header {
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-header h2 {
            color: #333;
            margin: 0;
            font-size: 2em;
        }

        .product-view-container {
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
        }

        .product-gallery {
            flex: 1;
            min-width: 300px;
            max-width: 400px;
            background: #fafafa;
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 20px;
        }

        .carousel img {
            width: 100%;
            height: 350px;
            object-fit: contain;
            border-radius: 8px;
        }

        .slick-prev:before, .slick-next:before {
            color: #0d6efd; 
            font-size: 24px;
        }

        .product-details {
            flex: 1.5;
            min-width: 300px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .product-title {
            font-size: 2.2em;
            color: #212529;
            margin: 0;
            line-height: 1.2;
        }

        .product-sku {
            color: #6c757d;
            font-size: 1em;
            margin-bottom: 10px;
        }

        .product-price {
            font-size: 2.5em;
            color: #198754;
            font-weight: bold;
            margin: 10px 0;
        }

        .detail-item {
            font-size: 1.1em;
            color: #495057;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #0d6efd;
        }

        .detail-item strong {
            color: #212529;
        }

        .description-box {
            background: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            margin-top: 10px;
            line-height: 1.6;
            color: #555;
        }

        .description-box h3 {
            margin-top: 0;
            color: #333;
            font-size: 1.2em;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
            display: inline-block;
        }
        .status-ativo { background-color: #d4edda; color: #155724; }
        .status-desativado { background-color: #f8d7da; color: #721c24; }

        .buttons-container {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-primary, .btn-secondary {
            padding: 12px 25px;
            font-size: 1.1em;
            border-radius: 6px;
            text-decoration: none;
            text-align: center;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: bold;
        }
        
        .btn-edit {
            background-color: #ffc107;
            color: #000;
        }
        .btn-edit:hover {
            background-color: #e0a800;
        }
    </style>
</head>
<body>

<div class="admin-card">
    <div class="admin-header">
        <h2><i class="fas fa-eye"></i> Visualizar Produto</h2>
        
        <?php if ($product['status'] === 'Ativo'): ?>
            <span class="status-badge status-ativo"><i class="fas fa-check-circle"></i> Produto Ativo no Catálogo</span>
        <?php else: ?>
            <span class="status-badge status-desativado"><i class="fas fa-times-circle"></i> Produto Inativo</span>
        <?php endif; ?>
    </div>

    <div class="product-view-container">
        
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
                    <i class="fas fa-image fa-4x" style="margin-bottom: 15px;"></i>
                    <p>Nenhuma imagem cadastrada</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="product-details">
            <p class="product-sku"><i class="fas fa-barcode"></i> Código (SKU): <strong><?php echo htmlspecialchars($product['codigo'] ?? 'N/A'); ?></strong> | ID: #<?php echo $product['id']; ?></p>
            
            <h1 class="product-title"><?php echo htmlspecialchars($product['nome']); ?></h1>
            
            <div class="product-price">
                R$ <?php echo number_format($product['valor'], 2, ',', '.'); ?>
            </div>

            <div class="detail-item" style="border-left-color: #198754;">
                <i class="fas fa-boxes"></i> <strong>Estoque Disponível:</strong> <?php echo $product['quantidade']; ?> unidades
            </div>

            <div class="detail-item" style="border-left-color: #ffc107;">
                <i class="fas fa-star" style="color: #ffc107;"></i> <strong>Avaliação dos Clientes:</strong> 
                <?php
                    $avaliacao = (float)$product['avaliacao'];
                    echo number_format($avaliacao, 1, ',', '.') . ' / 5.0 ';
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $avaliacao) echo '<i class="fas fa-star" style="color: #ffc107;"></i>';
                        else echo '<i class="far fa-star" style="color: #ffc107;"></i>';
                    }
                ?>
            </div>

            <div class="description-box">
                <h3><i class="fas fa-align-left"></i> Descrição do Produto</h3>
                <?php echo nl2br(htmlspecialchars($product['descricao'])); ?>
            </div>

            <div class="buttons-container">
                <a href="listar_produto.php" class="btn-secondary" style="background-color: #6c757d; color: white;"><i class="fas fa-arrow-left"></i> Voltar à Lista</a>
                <a href="alterar_produtoadm.php?id=<?php echo $product['id']; ?>" class="btn-primary btn-edit"><i class="fas fa-edit"></i> Editar Produto</a>
            </div>
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<script>
    $(document).ready(function() {
        $('.carousel').slick({
            infinite: true,
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            dots: true,
            autoplay: true,
            autoplaySpeed: 3000
        });
    });
</script>

</body>
</html>