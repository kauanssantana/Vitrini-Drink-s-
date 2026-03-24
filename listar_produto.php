<?php
session_start();
require_once('conexao.php');

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$produtos_por_pagina = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $produtos_por_pagina;

$produto_buscar = isset($_POST['produto']) ? trim($_POST['produto']) : (isset($_GET['produto']) ? trim($_GET['produto']) : '');
$search_param = "%{$produto_buscar}%";

// 1. Contagem para Paginação
$query_total = "SELECT COUNT(*) AS total FROM produtos WHERE nome LIKE ?";
$stmt_total = mysqli_prepare($con, $query_total);
mysqli_stmt_bind_param($stmt_total, "s", $search_param);
mysqli_stmt_execute($stmt_total);
$total_result = mysqli_stmt_get_result($stmt_total);
$total_row = mysqli_fetch_assoc($total_result);
$total_produtos = $total_row['total'];
$total_paginas = ceil($total_produtos / $produtos_por_pagina);
mysqli_stmt_close($stmt_total);

// 2. Busca de Produtos com Imagem Principal
$query = "SELECT p.*, pi.caminho_imagem 
          FROM produtos p 
          LEFT JOIN imagens_produto pi ON p.id = pi.produto_id AND pi.principal = 1
          WHERE p.nome LIKE ? 
          ORDER BY p.id DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "sii", $search_param, $produtos_por_pagina, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos - Vitrini Drink's</title>
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

        .container { max-width: 1600px; width: 95%; margin: 40px auto; padding: 0 20px; }
        
        .admin-card { background: #fff; padding: 35px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
        
        .search-form { display: flex; gap: 10px; width: 100%; max-width: 450px; }
        .search-form input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; font-size: 1em; }
        .btn-search { background: #1a1a1a; color: #ffc107; border: none; padding: 0 20px; border-radius: 8px; cursor: pointer; }

        .btn-add { background: #198754; color: white; text-decoration: none; padding: 12px 25px; border-radius: 10px; font-weight: bold; transition: 0.3s; }
        .btn-add:hover { background: #157347; transform: translateY(-2px); }

        .product-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .product-table th { text-align: left; padding: 15px; background: #f8f9fa; color: #666; font-size: 0.85em; text-transform: uppercase; border-bottom: 2px solid #eee; }
        .product-table td { padding: 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }

        .prod-img { width: 50px; height: 50px; object-fit: contain; border-radius: 6px; border: 1px solid #eee; background: #fff; }

        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.8em; font-weight: bold; }
        .status-ativo { background: #d4edda; color: #155724; }
        .status-desativado { background: #f8d7da; color: #721c24; }

        .action-btns { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }
        .btn-action { padding: 8px 12px; border-radius: 6px; color: white; text-decoration: none; font-size: 0.9em; transition: 0.2s; }
        .btn-view { background: #6c757d; }
        .btn-edit { background: #0d6efd; }
        .btn-on { background: #198754; }
        .btn-off { background: #dc3545; }

        .pagination { display: flex; justify-content: center; gap: 10px; margin-top: 30px; }
        .pagination a { padding: 10px 18px; background: #fff; border: 1px solid #ddd; border-radius: 8px; text-decoration: none; color: #333; font-weight: bold; }
        .pagination a.active { background: #0d6efd; color: white; border-color: #0d6efd; }

        .btn-back { display: inline-block; margin-top: 25px; color: #0d6efd; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<header class="admin-header">
    <div class="logo"><a href="principal.php">Vitrini Drink's</a></div>
    <div style="font-size: 0.9em;">Olá, <?php echo explode('@', $_SESSION['user']['email'])[0]; ?></div>
</header>

<div class="container">
    <div class="admin-card">
        <div class="header-flex">
            <h2><i class="fas fa-boxes"></i> Gerenciamento de Produtos</h2>
            
            <?php if ($_SESSION['user']['grupo'] === 'Administrador'): ?>
                <a href="cadastro_produto.php" class="btn-add"><i class="fas fa-plus-circle"></i> NOVO PRODUTO</a>
            <?php endif; ?>
        </div>

        <form method="POST" class="search-form">
            <input type="text" name="produto" placeholder="Nome do produto..." value="<?php echo htmlspecialchars($produto_buscar); ?>">
            <button type="submit" class="btn-search"><i class="fas fa-search"></i></button>
        </form>

        <div style="overflow-x: auto;">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>ID</th>
                        <th>Produto</th>
                        <th>Valor</th>
                        <th>Qtd. Estoque</th>
                        <th>Status</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $row['caminho_imagem'] ?? 'vitrine.jpg'; ?>" 
                                         onerror="this.src='vitrine.jpg'" class="prod-img">
                                </td>
                                <td>#<?php echo $row['id']; ?></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td style="color: #198754; font-weight: bold;">R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?></td>
                                <td>
                                    <span style="<?php echo ($row['quantidade'] <= 5) ? 'color: red; font-weight: bold;' : ''; ?>">
                                        <?php echo $row['quantidade']; ?> un.
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo ($row['status'] === 'Ativo') ? 'status-ativo' : 'status-desativado'; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <?php if ($_SESSION['user']['grupo'] === 'Administrador'): ?>
                                            <a href="visualizar_produto.php?id=<?php echo $row['id']; ?>" class="btn-action btn-view" title="Visualizar"><i class="fas fa-eye"></i></a>
                                            <a href="alterar_produtoadm.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                            
                                            <?php if ($row['status'] === 'Ativo'): ?>
                                                <a href="inativar_produto.php?id=<?php echo $row['id']; ?>" class="btn-action btn-off" onclick="return confirm('Inativar produto?');"><i class="fas fa-power-off"></i></a>
                                            <?php else: ?>
                                                <a href="reativar_produto.php?id=<?php echo $row['id']; ?>" class="btn-action btn-on"><i class="fas fa-power-off"></i></a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="alterar_produto.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit"><i class="fas fa-boxes"></i> Atualizar Estoque</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center; padding: 40px;">Nenhum produto cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_paginas > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_paginas; $i++) : ?>
                    <a href="?page=<?php echo $i; ?>&produto=<?php echo urlencode($produto_buscar); ?>" 
                       class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <a href="principal.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar ao Painel</a>
    </div>
</div>

</body>
</html>