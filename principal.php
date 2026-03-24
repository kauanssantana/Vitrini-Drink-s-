<?php
session_start();

// SEGURANÇA: Verifica se há usuário logado
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Lógica de permissões baseada no grupo
$grupo = $_SESSION['user']['grupo'] ?? '';
$mostrar_lista_usuarios = ($grupo === 'Administrador');
$mostrar_lista_produtos = ($grupo === 'Administrador' || $grupo === 'Estoquista');
$mostrar_lista_pedidos  = ($grupo === 'Administrador' || $grupo === 'Estoquista');

if (empty($grupo)) {
    echo "Erro: Grupo não definido na sessão.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Principal - Vitrini Drink's</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
        }

        .admin-header { 
            background-color: #1a1a1a; 
            color: #ffc107; 
            padding: 25px 5%; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            text-transform: uppercase;
            font-weight: 800;
        }

        .main-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        
        .main-container {
            width: 100%;
            max-width: 800px; 
            background: #fff;
            padding: 60px;
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-top: 6px solid #ffc107;
        }

        .logo-img {
            max-width: 150px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        h2 { color: #1a1a1a; margin-bottom: 15px; font-size: 2.2em; letter-spacing: -1px; }

        .user-info {
            color: #6c757d;
            margin-bottom: 45px;
            font-size: 1.1em;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            border: 1px solid #eee;
            display: inline-block;
            width: 100%;
        }

        .user-info small { 
            display: inline-block;
            margin-top: 5px;
            background: #0d6efd; 
            color: white; 
            padding: 4px 12px; 
            border-radius: 20px; 
            text-transform: uppercase;
            font-size: 0.7em;
            letter-spacing: 1px;
        }

        .buttons-grid { 
            display: grid; 
            grid-template-columns: 1fr; 
            gap: 20px; 
        }

        .btn-menu {
            width: 100%;
            padding: 22px;
            font-size: 1.2em;
            font-weight: 700;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            border-radius: 15px;
            transition: all 0.4s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-blue { background-color: #0d6efd; color: white; }
        .btn-blue:hover { background-color: #0b5ed7; transform: scale(1.02); box-shadow: 0 10px 20px rgba(13, 110, 253, 0.2); }

        .btn-logout { background-color: #1a1a1a; color: #ffc107; margin-top: 20px; border: 2px solid transparent; }
        .btn-logout:hover { background-color: #000; border-color: #ffc107; transform: translateY(2px); }

        i { font-size: 1.3em; }
    </style>
</head>
<body>

    <header class="admin-header">
        <div>VITRINI DRINK'S</div>
        <div style="font-size: 0.8em; color: #ccc;">SISTEMA DE GESTÃO V3.0</div>
    </header>

    <div class="main-wrapper">
        <div class="main-container">
            <img src="vitrine.jpg" alt="Vitrini Drink's" class="logo-img">
            
            <h2>Painel de Controle</h2>
            
            <div class="user-info">
                <i class="fas fa-user-shield"></i> <strong><?php echo htmlspecialchars($_SESSION['user']['email']); ?></strong><br>
                <small><?php echo htmlspecialchars($grupo); ?></small>
            </div>

            <div class="buttons-grid">
                <?php if ($mostrar_lista_usuarios): ?>
                    <a href="listar_usuario.php" class="btn-menu btn-blue">
                        <i class="fas fa-users-cog"></i> Gerenciar Usuários
                    </a>
                <?php endif; ?>
                
                <?php if ($mostrar_lista_produtos): ?>
                    <a href="listar_produto.php" class="btn-menu btn-blue">
                        <i class="fas fa-wine-bottle"></i> Gerenciar Produtos
                    </a>
                <?php endif; ?>
                
                <?php if ($mostrar_lista_pedidos): ?>
                    <a href="listar_pedidos.php" class="btn-menu btn-blue">
                        <i class="fas fa-clipboard-list"></i> Gerenciar Pedidos
                    </a>
                <?php endif; ?>
                
                <a href="javascript:void(0);" onclick="confirmarLogout()" class="btn-menu btn-logout">
                    <i class="fas fa-power-off"></i> Sair do Sistema
                </a>
            </div>
        </div>
    </div>

    <script>
        function confirmarLogout() {
            if (confirm("Deseja realmente encerrar sua sessão administrativa?")) {
                window.location.href = "logout.php";
            }
        }
    </script>
</body>
</html>