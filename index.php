<?php
session_start();
require_once('conexao.php');

// ==========================================
// SEGURANÇA: Busca de produtos blindada
// ==========================================
$produto_buscar = isset($_POST['produto']) ? trim($_POST['produto']) : '';
$search_param = "%{$produto_buscar}%";

$query_produtos = "
    SELECT p.id, p.nome, p.valor, p.descricao, i.caminho_imagem
    FROM produtos p
    LEFT JOIN imagens_produto i ON p.id = i.produto_id AND i.principal = 1
    WHERE p.status = 'Ativo' AND p.nome LIKE ?
    ORDER BY p.id DESC
";

$stmt = mysqli_prepare($con, $query_produtos);
mysqli_stmt_bind_param($stmt, "s", $search_param);
mysqli_stmt_execute($stmt);
$result_produtos = mysqli_stmt_get_result($stmt);
$products = mysqli_fetch_all($result_produtos, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Lógica de contagem corrigida para evitar Warnings
$qtd_carrinho = 0;
if (isset($_SESSION['carrinho']) && is_array($_SESSION['carrinho'])) {
    $qtd_carrinho = count($_SESSION['carrinho']);
}

$is_logged_in = isset($_SESSION['user']);
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vitrini Drink's - Sua distribuidora online</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }

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
            z-index: 1000;
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

        .store-header .header-actions a, 
        .store-header .header-actions span { 
            color: white; 
            text-decoration: none; 
            font-weight: 600; 
            font-size: 1.1em; 
            transition: color 0.3s;
        }

        .store-header .header-actions a:hover { 
            color: #ffc107; 
        }

        .cart-icon-container { position: relative; font-size: 1.4em; }
        .cart-badge {
            position: absolute; top: -10px; right: -12px;
            background-color: #dc3545; color: white; font-size: 0.6em;
            padding: 4px 8px; border-radius: 50%; font-weight: bold;
        }

        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('vitrine.jpg') center/cover;
            padding: 80px 20px; text-align: center; color: white;
        }
        .hero-section h1 { font-size: 2.5em; margin-bottom: 30px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); }
        .search-container { max-width: 700px; margin: 0 auto; display: flex; gap: 10px; }
        .search-container input { flex: 1; padding: 18px 25px; border: none; border-radius: 40px; font-size: 1.1em; outline: none; color: #333; }
        .search-container button { padding: 18px 35px; border: none; border-radius: 40px; background-color: #ffc107; color: #1a1a1a; font-size: 1.1em; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        .search-container button:hover { background-color: #e0a800; }

        .products-section {
            padding: 50px 2%; 
            max-width: 1650px; 
            margin: 0 auto;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); 
            gap: 20px; 
        }

        .product-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .product-card-img {
            height: 220px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fff;
        }

        .product-card-img img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .product-card-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
            background-color: #fafafa;
            border-top: 1px solid #eee;
        }

        .product-card-title {
            font-size: 1.1em;
            margin-bottom: 12px;
            color: #333;
            height: 44px; 
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
        }

        .product-card-price {
            font-size: 1.6em;
            font-weight: 800;
            color: #198754;
            margin-bottom: 20px;
        }

        .product-card-buttons {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-view { text-align: center; padding: 12px; background-color: #e2e6ea; color: #495057; text-decoration: none; border-radius: 8px; font-weight: bold; transition: 0.3s; }
        .btn-add-cart { text-align: center; padding: 12px; background-color: #0d6efd; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; text-decoration: none; }
        .btn-add-cart:hover { background-color: #0b5ed7; }

        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(5px); display: flex; justify-content: center; align-items: center; z-index: 9999; }
        .modal-box { background: white; padding: 40px; border-radius: 15px; max-width: 500px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.5); }
        .modal-box h2 { color: #dc3545; font-size: 2em; margin-bottom: 15px; }
        .modal-buttons { display: flex; gap: 15px; margin-top: 30px; }
        .modal-btn { flex: 1; padding: 18px; font-size: 1.1em; font-weight: bold; border: none; border-radius: 10px; cursor: pointer; }
        .btn-yes { background-color: #198754; color: white; }
        .btn-no { background-color: #dc3545; color: white; }
        .hidden { display: none; }
    </style>
</head>
<body>

<div id="modal-idade-overlay" class="modal-overlay hidden">
    <div id="modal-idade-box" class="modal-box">
        <i class="fas fa-exclamation-triangle fa-3x" style="color: #dc3545; margin-bottom: 20px;"></i>
        <h2>Acesso Restrito</h2>
        <p>Você possui 18 anos ou mais?<br>Esta loja contém bebidas alcoólicas.</p>
        <div class="modal-buttons">
            <button id="btn-nao" class="modal-btn btn-no">Não</button>
            <button id="btn-sim" class="modal-btn btn-yes">Sim, eu tenho</button>
        </div>
    </div>
</div>

<header class="store-header">
    <div class="logo">
        <a href="index.php">Vitrini Drink's</a>
    </div>
    <div class="header-actions">
        <?php if ($is_logged_in): ?>
            <a href="area_cliente.php"><i class="fas fa-user-circle"></i> Olá, <?php echo explode('@', $_SESSION['user']['email'])[0]; ?></a>
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

<div class="hero-section">
    <h1>Encontre as melhores bebidas para o seu momento</h1>
    <form method="POST" action="index.php" class="search-container">
        <input type="text" name="produto" placeholder="O que você deseja beber hoje?" value="<?php echo htmlspecialchars($produto_buscar); ?>">
        <button type="submit"><i class="fas fa-search"></i> Buscar</button>
    </form>
</div>

<div class="products-section">
    <div class="products-grid">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $produto): ?>
                <div class="product-card">
                    <div class="product-card-img">
                        <img src="<?php echo htmlspecialchars($produto['caminho_imagem'] ?? 'vitrine.jpg'); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>" onerror="this.src='vitrine.jpg'">
                    </div>
                    <div class="product-card-body">
                        <h3 class="product-card-title"><?php echo htmlspecialchars($produto['nome']); ?></h3>
                        <div class="product-card-price">
                            R$ <?php echo number_format($produto['valor'], 2, ',', '.'); ?>
                        </div>
                        <div class="product-card-buttons">
                            <a href="visualizar_produto_cliente.php?id=<?php echo $produto['id']; ?>" class="btn-view">Ver Detalhes</a>
                            <a href="carrinho.php?acao=add&id=<?php echo $produto['id']; ?>" class="btn-add-cart">
                                <i class="fas fa-cart-plus"></i> Adicionar ao Carrinho
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 80px; background: white; border-radius: 20px;">
                <i class="fas fa-search fa-4x" style="color: #eee; margin-bottom: 20px;"></i>
                <h3 style="color: #888;">Nenhum produto encontrado.</h3>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('modal-idade-overlay');
    if (sessionStorage.getItem('idadeVerificada') !== 'true') {
        overlay.classList.remove('hidden');
    }

    document.getElementById('btn-sim').addEventListener('click', function() {
        sessionStorage.setItem('idadeVerificada', 'true');
        overlay.classList.add('hidden');
    });

    document.getElementById('btn-nao').addEventListener('click', function() {
        alert("Você precisa ser maior de 18 anos para acessar esta loja.");
        window.location.href = "https://www.google.com";
    });
});
</script>

</body>
</html>