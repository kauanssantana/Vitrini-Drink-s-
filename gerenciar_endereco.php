<?php
session_start();
require_once('conexao.php'); 
if (!isset($_SESSION['user'])) {
    header('Location: login_cliente.php');
    exit();
}

$cliente_id = $_SESSION['user']['id'];

// 1. DEFINIR ENDEREÇO COMO PADRÃO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_default'])) {
    $endereco_id = $_POST['endereco_id'];

    // Remove padrão de todos os outros
    $query = "UPDATE enderecos_entrega SET endereco_padrao = 'Não' WHERE cliente_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'i', $cliente_id);
    mysqli_stmt_execute($stmt);

    // Define o selecionado como padrão
    $query = "UPDATE enderecos_entrega SET endereco_padrao = 'Sim' WHERE id = ? AND cliente_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $endereco_id, $cliente_id);
    mysqli_stmt_execute($stmt);

    echo "<script>alert('Endereço padrão atualizado!'); window.location.href = 'gerenciar_endereco.php';</script>";
}

// 2. REMOVER ENDEREÇO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $endereco_id = $_POST['endereco_id'];

    $query = "DELETE FROM enderecos_entrega WHERE id = ? AND cliente_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $endereco_id, $cliente_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Endereço removido com sucesso!'); window.location.href = 'gerenciar_endereco.php';</script>";
    } else {
        echo "<script>alert('Erro ao remover o endereço.');</script>";
    }
}

// 3. BUSCAR LISTA DE ENDEREÇOS
$query = "SELECT * FROM enderecos_entrega WHERE cliente_id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $cliente_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Endereços - Vitrini Drink's</title>
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

        .container { max-width: 1200px; margin: 50px auto; padding: 0 20px; }
        
        .header-box { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-add { background: #198754; color: white; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: bold; transition: 0.3s; }
        .btn-add:hover { background: #157347; transform: translateY(-2px); }

        .address-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        
        .address-card { 
            background: #fff; 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
            border: 1px solid #eee;
            position: relative;
            transition: 0.3s;
        }
        .address-card.default { border: 2px solid #ffc107; background: #fffdf5; }

        .badge-default { 
            position: absolute; top: 15px; right: 15px; 
            background: #ffc107; color: #000; font-size: 0.75em; 
            padding: 5px 12px; border-radius: 20px; font-weight: bold; 
        }

        .address-info { margin-bottom: 25px; line-height: 1.6; }
        .address-info strong { display: block; font-size: 1.1em; color: #212529; margin-bottom: 5px; }
        .address-info p { color: #6c757d; font-size: 0.95em; }

        .card-actions { display: flex; gap: 10px; border-top: 1px solid #eee; padding-top: 20px; }
        
        .btn-action { flex: 1; padding: 10px; border-radius: 6px; border: none; font-weight: bold; cursor: pointer; transition: 0.2s; font-size: 0.85em; }
        .btn-default { background: #e9ecef; color: #495057; }
        .btn-default:hover { background: #dee2e6; }
        .btn-delete { background: #fff; color: #dc3545; border: 1px solid #f5c6cb; }
        .btn-delete:hover { background: #f8d7da; }

        .btn-back { display: block; text-align: center; margin-top: 40px; color: #6c757d; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<header class="store-header">
    <div class="logo"><a href="index.php">Vitrini Drink's</a></div>
    <div class="header-actions">
        <span>Olá, <?php echo explode(' ', $_SESSION['user']['nome'])[0]; ?></span>
    </div>
</header>

<div class="container">
    <div class="header-box">
        <h2><i class="fas fa-map-marked-alt"></i> Meus Endereços</h2>
        <a href="endereco_alternativo.php" class="btn-add"><i class="fas fa-plus"></i> Novo Endereço</a>
    </div>

    <div class="address-grid">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($endereco = mysqli_fetch_assoc($result)): ?>
                <div class="address-card <?php echo $endereco['endereco_padrao'] === 'Sim' ? 'default' : ''; ?>">
                    
                    <?php if ($endereco['endereco_padrao'] === 'Sim'): ?>
                        <span class="badge-default"><i class="fas fa-star"></i> PADRÃO</span>
                    <?php endif; ?>

                    <div class="address-info">
                        <strong><?php echo htmlspecialchars($endereco['endereco']); ?>, nº <?php echo $endereco['numero']; ?></strong>
                        <p><?php echo htmlspecialchars($endereco['bairro']); ?></p>
                        <p><?php echo htmlspecialchars($endereco['cidade']); ?> - <?php echo $endereco['uf']; ?></p>
                        <p>CEP: <?php echo htmlspecialchars($endereco['cep']); ?></p>
                        <?php if($endereco['complemento']): ?>
                            <p><small>Complemento: <?php echo htmlspecialchars($endereco['complemento']); ?></small></p>
                        <?php endif; ?>
                    </div>

                    <div class="card-actions">
                        <?php if ($endereco['endereco_padrao'] !== 'Sim'): ?>
                            <form method="POST" style="flex: 1;">
                                <input type="hidden" name="endereco_id" value="<?php echo $endereco['id']; ?>">
                                <button type="submit" name="set_default" class="btn-action btn-default">Usar como Padrão</button>
                            </form>
                        <?php endif; ?>

                        <form method="POST" style="flex: 1;" onsubmit="return confirm('Excluir este endereço?');">
                            <input type="hidden" name="endereco_id" value="<?php echo $endereco['id']; ?>">
                            <button type="submit" name="delete" class="btn-action btn-delete">Remover</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 50px; background: #fff; border-radius: 15px;">
                <p>Nenhum endereço alternativo cadastrado.</p>
            </div>
        <?php endif; ?>
    </div>

    <a href="area_cliente.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar ao Painel da Conta</a>
</div>

</body>
</html>
<?php mysqli_close($con); ?>