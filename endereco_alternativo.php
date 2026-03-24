<?php
session_start();
require_once('conexao.php'); 

if (!isset($_SESSION['user'])) {
    header('Location: login_cliente.php');
    exit();
}

$cliente_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cep = $_POST['cep_entrega'];
    $logradouro = $_POST['logradouro_entrega'];
    $numero = $_POST['numero_entrega'];
    $complemento = $_POST['complemento_entrega'];
    $bairro = $_POST['bairro_entrega'];
    $cidade = $_POST['cidade_entrega'];
    $uf = $_POST['uf_entrega'];

    // SEGURANÇA: Prepared Statement (Já incluído no seu código, mantive a proteção)
    $query = "INSERT INTO enderecos_entrega (cliente_id, cep, endereco, numero, complemento, bairro, cidade, uf, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Ativo')";
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'isssssss', $cliente_id, $cep, $logradouro, $numero, $complemento, $bairro, $cidade, $uf);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Endereço de entrega adicionado com sucesso!'); window.location.href = 'checkout.php';</script>";
    } else {
        echo "<script>alert('Erro ao adicionar o endereço de entrega.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Endereço de Entrega - Vitrini Drink's</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; color: #333; }

        .store-header {
            background-color: #1a1a1a;
            color: white;
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .store-header .logo img { max-height: 50px; border-radius: 4px; }
        .btn-logout { color: #dc3545; text-decoration: none; font-weight: bold; margin-left: 15px; }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .form-card {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .form-card h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #212529;
            font-size: 1.8em;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .input-group {
            flex: 1;
            min-width: 200px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #495057;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            outline: none;
            transition: border-color 0.3s;
        }

        .input-group input:focus {
            border-color: #0d6efd;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 20px;
        }
        .btn-submit:hover { background-color: #0b5ed7; }

        .btn-back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
            text-decoration: none;
            font-weight: 500;
        }
        .btn-back:hover { color: #333; }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function buscarCep() {
            var cep = document.getElementById("cep_entrega").value.replace(/\D/g, '');
            if (cep.length == 8) {
                $.ajax({
                    url: `https://viacep.com.br/ws/${cep}/json/`,
                    dataType: 'json',
                    success: function(data) {
                        if (!data.erro) {
                            document.getElementById("logradouro_entrega").value = data.logradouro;
                            document.getElementById("bairro_entrega").value = data.bairro;
                            document.getElementById("cidade_entrega").value = data.localidade;
                            document.getElementById("uf_entrega").value = data.uf;
                        } else {
                            alert("CEP não encontrado.");
                        }
                    }
                });
            }
        }
    </script>
</head>
<body>

    <header class="store-header">
        <div class="logo">
            <img src="vitrine.jpg" alt="Vitrini Drink's" onerror="this.style.display='none'">
        </div>
        <div class="header-actions">
            <span>Bem-vindo, <strong><?php echo htmlspecialchars($_SESSION['user']['nome']); ?></strong></span>
            <a href="sair.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </header>

    <div class="container">
        <div class="form-card">
            <h2><i class="fas fa-truck"></i> Novo Endereço de Entrega</h2>

            <form action="endereco_alternativo.php" method="POST">
                
                <div class="form-row">
                    <div class="input-group">
                        <label for="cep_entrega">CEP:</label>
                        <input type="text" name="cep_entrega" id="cep_entrega" placeholder="00000-000" required onblur="buscarCep()">
                    </div>
                    <div class="input-group" style="flex: 2;">
                        <label for="logradouro_entrega">Endereço / Logradouro:</label>
                        <input type="text" name="logradouro_entrega" id="logradouro_entrega" placeholder="Ex: Av. das Palmeiras" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-group">
                        <label for="numero_entrega">Número:</label>
                        <input type="text" name="numero_entrega" id="numero_entrega" placeholder="123" required>
                    </div>
                    <div class="input-group">
                        <label for="complemento_entrega">Complemento:</label>
                        <input type="text" name="complemento_entrega" id="complemento_entrega" placeholder="Apto, Bloco, etc.">
                    </div>
                </div>

                <div class="input-group" style="margin-bottom: 20px;">
                    <label for="bairro_entrega">Bairro:</label>
                    <input type="text" name="bairro_entrega" id="bairro_entrega" placeholder="Digite o bairro" required>
                </div>

                <div class="form-row">
                    <div class="input-group" style="flex: 2;">
                        <label for="cidade_entrega">Cidade:</label>
                        <input type="text" name="cidade_entrega" id="cidade_entrega" placeholder="Sua cidade" required>
                    </div>
                    <div class="input-group">
                        <label for="uf_entrega">UF (Estado):</label>
                        <input type="text" name="uf_entrega" id="uf_entrega" placeholder="SP" maxlength="2" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Salvar Endereço de Entrega</button>
            </form>

            <a href="checkout.php" class="btn-back"><i class="fas fa-arrow-left"></i> Cancelar e Voltar</a>
        </div>
    </div>

</body>
</html>