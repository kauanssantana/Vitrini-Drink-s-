<?php
session_start();

// SEGURANÇA: Apenas Estoquista deve acessar esta página (Admin tem a sua própria)
if (!isset($_SESSION['user']) || $_SESSION['user']['grupo'] !== 'Estoquista') {
    header("Location: principal.php");
    exit();
}

require_once('conexao.php');

$error = "";
$success = "";

// SEGURANÇA: Verifica se o ID é válido e é um número
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listar_produto.php");
    exit();
}

$id_produto = (int)$_GET['id'];

// SEGURANÇA: Busca os dados do produto com Prepared Statement
$query = "SELECT * FROM produtos WHERE id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $id_produto);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: listar_produto.php");
    exit();
}

// Processa a atualização do estoque
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nova_quantidade = (int)$_POST['quantidade'];

    if ($nova_quantidade < 0) {
        $error = "A quantidade em estoque não pode ser negativa.";
    } else {
        // SEGURANÇA: Atualiza apenas a quantidade com Prepared Statement
        $update_query = "UPDATE produtos SET quantidade = ? WHERE id = ?";
        $stmt_up = mysqli_prepare($con, $update_query);
        
        if ($stmt_up) {
            mysqli_stmt_bind_param($stmt_up, "ii", $nova_quantidade, $id_produto);
            if (mysqli_stmt_execute($stmt_up)) {
                $success = "Estoque atualizado com sucesso!";
                // Atualiza a variável local para refletir o novo valor na tela
                $product['quantidade'] = $nova_quantidade;
            } else {
                $error = "Erro ao atualizar o estoque no banco de dados.";
            }
            mysqli_stmt_close($stmt_up);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Estoque - Vitrini Drink's</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f4f6f9;
            padding: 40px 20px;
        }

        .admin-card {
            background: #fff;
            max-width: 600px; /* Cartão menor por ter menos campos */
            width: 100%;
            margin: 0 auto; /* Centraliza na tela */
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .admin-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .admin-header h2 {
            color: #333;
            margin: 0;
            font-size: 1.8em;
        }

        .admin-header p {
            color: #6c757d;
            margin-top: 5px;
        }

        .product-info-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .product-info-box p {
            margin: 8px 0;
            font-size: 1.05em;
            color: #495057;
        }

        .product-info-box strong {
            color: #212529;
        }

        .input-group {
            margin-bottom: 25px;
        }

        .input-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #495057;
            font-size: 1.1em;
        }

        .input-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1.3em; 
            box-sizing: border-box;
            transition: border-color 0.3s;
            text-align: center; 
        }

        .input-group input:focus {
            border-color: #198754;
            outline: none;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }

        .buttons {
            display: flex;
            gap: 15px;
        }

        .btn-primary, .btn-secondary {
            flex: 1;
            padding: 15px;
            font-size: 1.1em;
            text-align: center;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-weight: bold;
        }

        .btn-success {
            background-color: #198754;
            color: white;
        }
        .btn-success:hover {
            background-color: #157347;
        }
    </style>
</head>
<body>

<div class="admin-card">
    <div class="admin-header">
        <h2><i class="fas fa-boxes"></i> Controle de Estoque</h2>
        <p>Atualização de quantidades (Acesso Estoquista)</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
    <?php endif; ?>

    <div class="product-info-box">
        <p><strong><i class="fas fa-tag"></i> Produto:</strong> <?php echo htmlspecialchars($product['nome']); ?></p>
        <p><strong><i class="fas fa-barcode"></i> Código (SKU):</strong> <?php echo htmlspecialchars($product['codigo'] ?? 'N/A'); ?></p>
        <p><strong><i class="fas fa-dollar-sign"></i> Valor Unitário:</strong> R$ <?php echo number_format($product['valor'], 2, ',', '.'); ?></p>
    </div>

    <form action="alterar_produto.php?id=<?php echo $id_produto; ?>" method="POST">
        <div class="input-group">
            <label for="quantidade">Quantidade Física Disponível:</label>
            <input type="number" name="quantidade" id="quantidade" value="<?php echo $product['quantidade']; ?>" min="0" required>
        </div>

        <div class="buttons">
            <a href="listar_produto.php" class="btn-secondary" style="background-color: #6c757d; color: white;">Voltar à Lista</a>
            <button type="submit" class="btn-primary btn-success"><i class="fas fa-save"></i> Salvar Estoque</button>
        </div>
    </form>
</div>

</body>
</html>
<?php mysqli_close($con); ?>