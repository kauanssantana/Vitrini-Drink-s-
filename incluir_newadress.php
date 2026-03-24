<?php
session_start();
require_once('conexao_cliente.php');

if (!isset($_SESSION['user'])) {
    header('Location: login_cliente.php');
    exit();
}

$cliente_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cep_entrega = $_POST['cep_entrega'];
    $logradouro_entrega = $_POST['logradouro_entrega'];
    $numero_entrega = $_POST['numero_entrega'];
    $complemento_entrega = $_POST['complemento_entrega'];
    $bairro_entrega = $_POST['bairro_entrega'];
    $cidade_entrega = $_POST['cidade_entrega'];
    $uf_entrega = $_POST['uf_entrega'];

    $endereco_adicionado = adicionarEnderecoEntrega($cliente_id, $cep_entrega, $logradouro_entrega, $numero_entrega, $complemento_entrega, $bairro_entrega, $cidade_entrega, $uf_entrega);

    if ($endereco_adicionado) {
        echo "<script>alert('Endereço de entrega adicionado com sucesso!'); window.location.href = 'area_cliente.php';</script>";
    } else {
        echo "<script>alert('Erro ao adicionar o endereço de entrega.');</script>";
    }
}

function adicionarEnderecoEntrega($cliente_id, $cep, $logradouro, $numero, $complemento, $bairro, $cidade, $uf) {
    global $con;

    $query = "INSERT INTO enderecos_entrega (cliente_id, cep, endereco, numero, complemento, bairro, cidade, uf, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Ativo')";
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ssssssss', $cliente_id, $cep, $logradouro, $numero, $complemento, $bairro, $cidade, $uf);

    return mysqli_stmt_execute($stmt);
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incluir Novo Endereço de Entrega</title>
</head>
<body>

    <header>
        <div class="logo">
        <img src="vitrine.jpg" alt="Logo da Loja">
        </div>
        <div class="header-actions">
            <p>Bem-vindo, <?php echo $_SESSION['user']['nome']; ?>!</p>
            <a href="sair.php" class="btn-logout">Sair</a>
        </div>
    </header>

    <div class="form-container">
        <h2>Incluir Novo Endereço de Entrega</h2>

        <form action="incluir_newadress.php" method="POST">
            <div class="input-group">
                <label for="cep_entrega">CEP:</label>
                <input type="text" name="cep_entrega" id="cep_entrega" placeholder="Digite o CEP de entrega" required>
            </div>

            <div class="input-group">
                <label for="logradouro_entrega">Endereço/Logradouro:</label>
                <input type="text" name="logradouro_entrega" id="logradouro_entrega" placeholder="Digite o logradouro de entrega" required>
            </div>

            <div class="input-group">
                <label for="numero_entrega">Número:</label>
                <input type="text" name="numero_entrega" id="numero_entrega" placeholder="Digite o número de entrega" required>
            </div>

            <div class="input-group">
                <label for="complemento_entrega">Complemento:</label>
                <input type="text" name="complemento_entrega" id="complemento_entrega" placeholder="Digite o complemento de entrega (opcional)">
            </div>

            <div class="input-group">
                <label for="bairro_entrega">Bairro:</label>
                <input type="text" name="bairro_entrega" id="bairro_entrega" placeholder="Digite o bairro de entrega" required>
            </div>

            <div class="input-group">
                <label for="cidade_entrega">Cidade:</label>
                <input type="text" name="cidade_entrega" id="cidade_entrega" placeholder="Digite a cidade de entrega" required>
            </div>

            <div class="input-group">
                <label for="uf_entrega">UF:</label>
                <input type="text" name="uf_entrega" id="uf_entrega" placeholder="Digite a UF de entrega" required>
            </div>

            <button type="submit" class="btn-primary">Adicionar Endereço</button>
        </form>

        <div class="buttons">
            <a href="area_cliente.php" class="btn-secondary">Voltar para a Área do Cliente</a>
        </div>
    </div>

</body>
</html>