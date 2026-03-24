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
    <title>Termos de Uso - Vitrini Drink's</title>
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

        h1 { font-size: 2.2em; color: #212529; margin-bottom: 10px; }
        .last-update { color: #6c757d; font-size: 0.9em; font-style: italic; display: block; margin-bottom: 30px; }

        .termos-content h2 { 
            color: #0d6efd; margin-top: 35px; margin-bottom: 15px; 
            font-size: 1.4em; border-bottom: 1px solid #eee; padding-bottom: 8px; 
        }
        
        .termos-content p { margin-bottom: 20px; text-align: justify; }
        .termos-content strong { color: #111; }

        .alert-box { 
            background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; 
            padding: 20px; border-radius: 10px; margin: 30px 0; display: flex; gap: 15px; align-items: center;
        }

        .btn-footer { display: flex; justify-content: center; margin-top: 40px; }
        .btn-back { padding: 15px 35px; background: #0d6efd; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; transition: 0.3s; }
        
        footer { background: #1a1a1a; color: #888; padding: 40px 2%; text-align: center; margin-top: 50px; }
        footer a { color: #ccc; text-decoration: none; margin: 0 15px; font-size: 0.9em; }
    </style>
</head>
<body>

<header class="store-header">
    <div class="logo"><a href="index.php">Vitrini Drink's</a></div>
    <div class="header-actions">
        <a href="index.php" style="color: white; text-decoration: none; font-weight: 600;">Voltar à Loja</a>
    </div>
</header>

<div class="container">
    <div class="content-card">
        <h1>Termos de Uso</h1>
        <span class="last-update">Última atualização: 18 de novembro de 2025</span>
        
        <div class="termos-content">
            <p>Bem-vindo(a) ao <strong>Vitrini Drink's Online</strong>! Estes Termos de Uso regem o uso do nosso site e a compra dos produtos oferecidos. Ao aceder ao nosso site, você concorda com estas condições.</p>

            <h2>1. Objeto</h2>
            <p>Este site destina-se à venda e delivery de bebidas alcoólicas e não alcoólicas na região de Cotia/SP.</p>

            <h2>2. Elegibilidade (MAIORIDADE LEGAL)</h2>
            <div class="alert-box">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
                <div>
                    <strong>PROIBIDO PARA MENORES:</strong> Este site é destinado exclusivamente a usuários <strong>maiores de 18 anos</strong>. Ao realizar um pedido, você garante estar em plena capacidade legal.
                </div>
            </div>

            <h2>3. Cadastro e Segurança</h2>
            <p>O cliente é responsável pela veracidade das informações e pela confidencialidade da sua senha. Qualquer atividade realizada na conta é de sua inteira responsabilidade.</p>

            <h2>4. Entregas e Verificação</h2>
            <p>A entrega só será realizada mediante a apresentação de documento de identidade original com foto que comprove a maioridade do recebedor.</p>
        </div>

        <div class="btn-footer">
            <a href="index.php" class="btn-back">ENTENDI E QUERO COMPRAR</a>
        </div>
    </div>
</div>

<footer>
    <div class="footer-links">
        <a href="contatos.php">Contatos</a>
        <a href="politicas.php">Privacidade</a>
        <a href="termos.php">Termos de Uso</a>
    </div>
</footer>

</body>
</html>