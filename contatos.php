<?php
session_start();
require_once('conexao.php');

$is_logged_in = isset($_SESSION['user']);
$mensagem_enviada = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mensagem_enviada = true;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fale Conosco - Vitrini Drink's</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

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

        .container { max-width: 900px; margin: 50px auto; padding: 0 20px; flex: 1; }
        
        .contact-card { 
            background: #fff; 
            padding: 45px; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
        }

        .section-title { 
            font-size: 2em; 
            font-weight: bold; 
            margin-bottom: 30px; 
            color: #212529; 
            text-align: center;
        }

        .contact-methods { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 20px; 
            margin-bottom: 40px; 
        }
        .method-box { 
            background: #f8f9fa; 
            padding: 25px; 
            border-radius: 12px; 
            text-align: center; 
            border: 1px solid #eee;
        }
        .method-box i { font-size: 2em; color: #0d6efd; margin-bottom: 15px; }
        .method-box h4 { margin-bottom: 5px; color: #495057; }
        .method-box p { font-weight: bold; font-size: 1.1em; color: #212529; }

        .contact-form { border-top: 2px solid #f4f4f4; padding-top: 30px; }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #495057; }
        .input-group input, .input-group textarea { 
            width: 100%; 
            padding: 14px; 
            border: 1px solid #ddd; 
            border-radius: 10px; 
            font-size: 1em; 
            outline: none; 
        }
        .input-group textarea { height: 150px; resize: none; font-family: inherit; }
        .input-group input:focus, .input-group textarea:focus { border-color: #0d6efd; }

        .alert-success { 
            background: #d4edda; color: #155724; padding: 20px; 
            border-radius: 10px; margin-bottom: 25px; text-align: center; font-weight: bold; 
        }

        .btn-send { 
            width: 100%; 
            padding: 18px; 
            background: #0d6efd; 
            color: white; 
            border: none; 
            border-radius: 10px; 
            font-size: 1.2em; 
            font-weight: bold; 
            cursor: pointer; 
            transition: 0.3s; 
        }
        .btn-send:hover { background: #0b5ed7; transform: translateY(-2px); }

        footer { background: #1a1a1a; color: #888; padding: 40px 2%; text-align: center; margin-top: 50px; }
        footer a { color: #ccc; text-decoration: none; margin: 0 15px; font-size: 0.9em; }
        footer a:hover { color: #ffc107; }
        .footer-copy { margin-top: 20px; font-size: 0.8em; }
    </style>
</head>
<body>

<header class="store-header">
    <div class="logo"><a href="index.php">Vitrini Drink's</a></div>
    <div class="header-actions">
        <?php if ($is_logged_in): ?>
            <a href="area_cliente.php"><i class="fas fa-user-circle"></i> Minha Conta</a>
        <?php else: ?>
            <a href="login_cliente.php">LOGIN / CADASTRO</a>
        <?php endif; ?>
        <a href="carrinho.php" class="cart-icon"><i class="fas fa-shopping-cart"></i></a>
    </div>
</header>

<div class="container">
    <div class="contact-card">
        <h1 class="section-title">Fale Conosco</h1>

        <div class="contact-methods">
            <div class="method-box">
                <i class="fab fa-whatsapp"></i>
                <h4>WhatsApp / Tel</h4>
                <p>(11) 91350-4060</p>
            </div>
            <div class="method-box">
                <i class="fas fa-envelope"></i>
                <h4>E-mail</h4>
                <p>contato@vitrinidrinks.com.br</p>
            </div>
        </div>

        <?php if ($mensagem_enviada): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> Mensagem enviada com sucesso!<br>
                Responderemos o seu contato o mais breve possível.
            </div>
        <?php endif; ?>

        <form method="POST" class="contact-form">
            <div class="input-group">
                <label>Seu E-mail</label>
                <input type="email" name="email" placeholder="exemplo@email.com" required 
                       value="<?php echo $is_logged_in ? $_SESSION['user']['email'] : ''; ?>">
            </div>

            <div class="input-group">
                <label>Como podemos ajudar?</label>
                <textarea name="mensagem" placeholder="Digite sua dúvida, sugestão ou reclamação aqui..." required></textarea>
            </div>

            <button type="submit" class="btn-send">ENVIAR MENSAGEM</button>
        </form>

        <center><a href="index.php" style="display:inline-block; margin-top:25px; color:#666; text-decoration:none;"><i class="fas fa-arrow-left"></i> Voltar para a Loja</a></center>
    </div>
</div>

<footer>
    <div class="footer-links">
        <a href="contatos.php">Contatos</a>
        <a href="politicas.php">Privacidade</a>
        <a href="termos.php">Termos de Uso</a>
    </div>
    <div class="footer-copy">
        &copy; <?php echo date("Y"); ?> Vitrini Drink's. Todos os direitos reservados.
    </div>
</footer>

</body>
</html>