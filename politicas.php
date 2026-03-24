<?php
session_start();
require_once('conexao.php');
$is_logged_in = isset($_SESSION['user']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade - Vitrini Drink's</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; color: #333; line-height: 1.6; }

        .store-header { 
            background-color: #1a1a1a; color: white; padding: 25px 2%; 
            display: flex; justify-content: space-between; align-items: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .store-header .logo a { color: #ffc107; text-decoration: none; font-size: 1.8em; font-weight: 800; text-transform: uppercase; }

        .container { max-width: 1000px; margin: 50px auto; padding: 0 20px; }
        
        .content-card { 
            background: #fff; padding: 50px; border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
        }

        h1 { font-size: 2.2em; color: #212529; margin-bottom: 30px; text-align: center; }

        .policy-section { margin-bottom: 35px; }
        .policy-section h2 { color: #0d6efd; font-size: 1.4em; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .policy-section h3 { font-size: 1.1em; margin: 15px 0 10px; color: #444; }
        
        .policy-list { margin-left: 25px; margin-bottom: 20px; }
        .policy-list li { margin-bottom: 10px; }

        .btn-footer { display: flex; justify-content: center; margin-top: 40px; }
        .btn-back { padding: 15px 35px; background: #198754; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; transition: 0.3s; }
        
        footer { background: #1a1a1a; color: #888; padding: 40px 2%; text-align: center; margin-top: 50px; }
    </style>
</head>
<body>

<header class="store-header">
    <div class="logo"><a href="index.php">Vitrini Drink's</a></div>
</header>

<div class="container">
    <div class="content-card">
        <h1>Política de Privacidade</h1>
        
        <div class="policy-section">
            <p>A <strong>Vitrini Drink's</strong> preza pela sua segurança. Esta política explica como tratamos os seus dados pessoais em conformidade com a <strong>LGPD</strong>.</p>
        </div>

        <div class="policy-section">
            <h2><i class="fas fa-database"></i> 1. Coleta de Dados</h2>
            <h3>Dados Coletados:</h3>
            <ul class="policy-list">
                <li><strong>Cadastro:</strong> Nome, CPF, e-mail, nascimento e gênero.</li>
                <li><strong>Transação:</strong> Endereços de entrega e histórico de pedidos.</li>
                <li><strong>Segurança:</strong> Senhas armazenadas apenas em formato de hash (criptografadas).</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2><i class="fas fa-user-shield"></i> 2. Uso e Proteção</h2>
            <p>Os dados são utilizados exclusivamente para processar pedidos, verificar a maioridade legal e cumprir obrigações fiscais. Não partilhamos os seus dados com terceiros para fins publicitários.</p>
            <p><strong>Segurança:</strong> O site utiliza protocolos <strong>SSL/HTTPS</strong> para que a sua navegação seja 100% encriptada e segura.</p>
        </div>

        <div class="policy-section">
            <h2><i class="fas fa-balance-scale"></i> 3. Seus Direitos</h2>
            <p>Pode solicitar a qualquer momento a correção ou exclusão dos seus dados através do nosso canal de suporte.</p>
        </div>

        <div class="btn-footer">
            <a href="index.php" class="btn-back">VOLTAR À LOJA</a>
        </div>
    </div>
</div>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Vitrini Drink's. Proteção de Dados Conforme a LGPD.</p>
</footer>

</body>
</html>