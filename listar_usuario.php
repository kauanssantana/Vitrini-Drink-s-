<?php
session_start();
require_once('conexao.php');

// SEGURANÇA: Verifica se há usuário logado
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}


if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id']; 
    $action = $_GET['action'];
    $novo_status = '';

    if ($action === 'ativar') {
        $novo_status = 'Ativo';
    } elseif ($action === 'desativar') {
        $novo_status = 'Desativado';
    }

    if (!empty($novo_status) && $id > 0) {
        $update_query = "UPDATE usuarios SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $update_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $novo_status, $id);
            if (mysqli_stmt_execute($stmt)) {
                echo "<script>window.location.href='listar_usuario.php';</script>";
                exit();
            } else {
                echo "<script>alert('Erro ao atualizar o status do usuário.');</script>";
            }
            mysqli_stmt_close($stmt);
        }
    }
}


$cpf_buscar = isset($_POST['cpf']) ? trim($_POST['cpf']) : '';

if (!empty($cpf_buscar)) {
    $query = "SELECT * FROM usuarios WHERE cpf LIKE ?";
    $stmt = mysqli_prepare($con, $query);
    $search_param = "%{$cpf_buscar}%";
    
    mysqli_stmt_bind_param($stmt, "s", $search_param);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $query = "SELECT * FROM usuarios ORDER BY id DESC";
    $result = mysqli_query($con, $query);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Vitrini Drink's</title>
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

        .container { max-width: 1400px; width: 95%; margin: 40px auto; padding: 0 20px; }
        
        .admin-card { background: #fff; padding: 35px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; gap: 20px; flex-wrap: wrap; }
        
        .search-form { display: flex; gap: 10px; flex: 1; max-width: 400px; }
        .search-form input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; }
        .btn-search { background: #1a1a1a; color: #ffc107; border: none; padding: 0 20px; border-radius: 8px; cursor: pointer; }

        .btn-add { background: #0d6efd; color: white; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: bold; }

        .user-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .user-table th { text-align: left; padding: 15px; background: #f8f9fa; color: #666; font-size: 0.85em; text-transform: uppercase; border-bottom: 2px solid #eee; }
        .user-table td { padding: 18px 15px; border-bottom: 1px solid #f0f0f0; }

        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.8em; font-weight: bold; display: inline-flex; align-items: center; gap: 5px; }
        .status-ativo { background: #d4edda; color: #155724; }
        .status-desativado { background: #f8d7da; color: #721c24; }

        .action-btns { display: flex; gap: 10px; justify-content: center; }
        .btn-action { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 8px; text-decoration: none; transition: 0.3s; color: white; }
        
        .btn-edit { background: #0d6efd; }
        .btn-on { background: #198754; } 
        .btn-off { background: #dc3545; } 
        
        .btn-back { display: inline-block; margin-top: 25px; color: #6c757d; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<header class="admin-header">
    <div class="logo"><a href="principal.php">Vitrini Drink's</a></div>
    <div style="font-size: 0.9em;">Painel Administrativo</div>
</header>

<div class="container">
    <div class="admin-card">
        <div class="header-flex">
            <h2><i class="fas fa-users-cog"></i> Gerenciar Usuários</h2>
            
            <form method="POST" class="search-form">
                <input type="text" name="cpf" placeholder="Buscar por CPF..." value="<?php echo htmlspecialchars($cpf_buscar); ?>">
                <button type="submit" class="btn-search"><i class="fas fa-search"></i></button>
            </form>

            <a href="cadastro_usuario.php" class="btn-add"><i class="fas fa-plus"></i> NOVO USUÁRIO</a>
        </div>

        <div style="overflow-x: auto;">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Grupo</th>
                        <th>Status</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['nome']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['grupo']); ?></td>
                                <td>
                                    <?php if ($row['status'] === 'Ativo'): ?>
                                        <span class="status-badge status-ativo"><i class="fas fa-check-circle"></i> Ativo</span>
                                    <?php else: ?>
                                        <span class="status-badge status-desativado"><i class="fas fa-times-circle"></i> Desativado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="editar_usuario.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <?php if ($row['status'] === 'Ativo'): ?>
                                            <a href="listar_usuario.php?action=desativar&id=<?php echo $row['id']; ?>" 
                                               class="btn-action btn-off" title="Desativar" 
                                               onclick="return confirm('Deseja realmente DESATIVAR este usuário?');">
                                                <i class="fas fa-power-off"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="listar_usuario.php?action=ativar&id=<?php echo $row['id']; ?>" 
                                               class="btn-action btn-on" title="Ativar">
                                                <i class="fas fa-power-off"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #666;">Nenhum usuário encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <a href="principal.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar ao Painel</a>
    </div>
</div>

</body>
</html>