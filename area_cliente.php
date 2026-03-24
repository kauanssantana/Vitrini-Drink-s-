<?php
session_start();
require_once('conexao.php');

// Verifica se o cliente está logado
if (!isset($_SESSION['user'])) {
    header("Location: login_cliente.php");
    exit();
}

$cliente = $_SESSION['user'];

// Lógica de contagem para o badge do carrinho
$qtd_carrinho = 0;
if (isset($_SESSION['carrinho']) && is_array($_SESSION['carrinho'])) {
    $qtd_carrinho = count($_SESSION['carrinho']);
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - Vitrini Drink's</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, sans-serif; color: #333; }

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
        .btn-logout { color: #dc3545 !important; }

        .container { max-width: 1200px; margin: 50px auto; padding: 0 20px; }

        .welcome-section { margin-bottom: 40px; }
        .welcome-section h1 { font-size: 2.2em; color: #212529; margin-bottom: 10px; }
        .welcome-section p { color: #6c757d; font-size: 1.1em; }

        .dashboard-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 25px; 
        }

        .menu-card {
            background: #fff;
            padding: 35px 25px;
            border-radius: 15px;
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            border: 1px solid #eee;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .menu-card i { 
            font-size: 2.5em; 
            color: #0d6efd; 
            background: #f0f7ff; 
            width: 80px; 
            height: 80px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border-radius: 50%;
            transition: 0.3s;
        }

        .menu-card h3 { font-size: 1.25em; font-weight: 700; }
        .menu-card p { font-size: 0.9em; color: #6c757d; line-height: 1.4; }

        .menu-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.1);
            border-color: #0d6efd;
        }

        .menu-card:hover i {
            background: #0d6efd;
            color: white;
        }

        .card-orders { border-left: 5px solid #198754; }
        .card-orders i { color: #198754; background: #e8f5e9; }
        .card-orders:hover i { background: #198754; }

        .card-info i { color: #6c757d; background: #f8f9fa; }
        .card-info:hover i { background: #6c757d; }

        .btn-back-store {
            display: inline-block;
            margin-top: 40px;
            text-decoration: none;
            color: #6c757d;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-back-store:hover { color: #212529; }
    </style>
</head>
<body>

<header class="store-header">
    <div class="logo">
        <a href="index.php">Vitrini Drink's</a>
    </div>
    <div class="header-actions">
        <a href="index.php"><i class="fas fa-shopping-bag"></i> Ir para a Loja</a>
        <a href="sair.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</header>

<div class="container">
    <div class="welcome-section">
        <h1>Olá, <?php echo explode(' ', $cliente['nome'])[0]; ?>! 👋</h1>
        <p>Gerencie seus pedidos, dados e conheça nossas políticas de uso.</p>
    </div>

    <div class="dashboard-grid">
        <a href="meus_pedidos.php" class="menu-card card-orders">
            <i class="fas fa-box-open"></i>
            <h3>Meus Pedidos</h3>
            <p>Acompanhe o status e histórico de suas compras.</p>
        </a>

        <a href="alterar_dados.php" class="menu-card">
            <i class="fas fa-user-edit"></i>
            <h3>Dados Cadastrais</h3>
            <p>Atualize seu nome, e-mail e informações básicas.</p>
        </a>

        <a href="alterar_senha.php" class="menu-card">
            <i class="fas fa-shield-alt"></i>
            <h3>Alterar Senha</h3>
            <p>Mantenha sua conta segura trocando sua senha.</p>
        </a>

        <a href="gerenciar_endereco.php" class="menu-card">
            <i class="fas fa-map-marked-alt"></i>
            <h3>Meus Endereços</h3>
            <p>Adicione ou altere seus locais de entrega.</p>
        </a>

        <a href="devolucao.php" class="menu-card">
            <i class="fas fa-undo-alt"></i>
            <h3>Devoluções</h3>
            <p>Precisa trocar algo? Inicie uma solicitação aqui.</p>
        </a>

        <a href="termos.php" class="menu-card card-info">
            <i class="fas fa-file-contract"></i>
            <h3>Termos de Uso</h3>
            <p>Leia as regras e condições de uso da nossa plataforma.</p>
        </a>

        <a href="politicas.php" class="menu-card card-info">
            <i class="fas fa-user-lock"></i>
            <h3>Privacidade</h3>
            <p>Saiba como protegemos os seus dados pessoais (LGPD).</p>
        </a>

        <a href="contatos.php" class="menu-card">
            <i class="fas fa-headset"></i>
            <h3>Preciso de Ajuda</h3>
            <p>Fale conosco sobre qualquer dúvida ou problema.</p>
        </a>
    </div>

    <center>
        <a href="index.php" class="btn-back-store">
            <i class="fas fa-arrow-left"></i> Voltar para a vitrine de produtos
        </a>
    </center>
</div>

</body>
</html>