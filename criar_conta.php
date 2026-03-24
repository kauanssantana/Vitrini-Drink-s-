<?php
session_start();
require_once('conexao.php');

function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) { $d += $cpf[$c] * (($t + 1) - $c); }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$t] != $d) return false;
    }
    return true;
}

function validarNome($nome) {
    $nomes = explode(' ', trim($nome));
    return count($nomes) >= 2 && strlen($nomes[0]) >= 3;
}

function validarIdade($data_nascimento) {
    $nascimento = new DateTime($data_nascimento);
    $hoje = new DateTime('now');
    return $hoje->diff($nascimento)->y;
}

$sucesso = false;
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $cpf = preg_replace('/\D/', '', $_POST['cpf']);
    $data_nascimento = $_POST['data_nascimento'];
    $genero = $_POST['genero'];
    $senha = $_POST['senha'];
    $check_senha = $_POST['check-senha'];

    $cep_f = $_POST['cep_faturamento'];
    $rua_f = $_POST['endereco_faturamento'];
    $num_f = $_POST['numero_faturamento'];
    $comp_f = $_POST['complemento_faturamento'];
    $bairro_f = $_POST['bairro_faturamento'];
    $cid_f = $_POST['cidade_faturamento'];
    $uf_f = $_POST['uf_faturamento'];

    if ($senha !== $check_senha) {
        $error_msg = "As senhas não coincidem.";
    } elseif (!validarCPF($cpf)) {
        $error_msg = "CPF inválido.";
    } elseif (!validarNome($nome)) {
        $error_msg = "Informe seu nome e sobrenome.";
    } elseif (validarIdade($data_nascimento) < 18) {
        $error_msg = "Você deve ter pelo menos 18 anos.";
    } else {
        $stmt = $con->prepare("SELECT id FROM clientes WHERE email = ? OR cpf = ?");
        $stmt->bind_param("ss", $email, $cpf);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error_msg = "E-mail ou CPF já cadastrado.";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO clientes (nome, email, cpf, data_nascimento, genero, endereco_faturamento, cep_faturamento, numero_faturamento, complemento_faturamento, bairro_faturamento, cidade_faturamento, uf_faturamento, senha, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Ativo')";
            
            $stmt_ins = $con->prepare($sql);
            $stmt_ins->bind_param("sssssssssssss", $nome, $email, $cpf, $data_nascimento, $genero, $rua_f, $cep_f, $num_f, $comp_f, $bairro_f, $cid_f, $uf_f, $senha_hash);

            if ($stmt_ins->execute()) {
                $cliente_id = $con->insert_id;
                if (!empty($_POST['cep_entrega'])) {
                    $stmt_end = $con->prepare("INSERT INTO enderecos_entrega (cliente_id, cep, endereco, numero, complemento, bairro, cidade, uf, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Ativo')");
                    $stmt_end->bind_param("isssssss", $cliente_id, $_POST['cep_entrega'], $_POST['endereco_entrega'], $_POST['numero_entrega'], $_POST['complemento_entrega'], $_POST['bairro_entrega'], $_POST['cidade_entrega'], $_POST['uf_entrega']);
                    $stmt_end->execute();
                }
                $sucesso = true;
            } else {
                $error_msg = "Erro ao processar cadastro.";
            }
        }
    }
}
$dataMaximaFormatada = (new DateTime('-18 years'))->format('Y-m-d');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Vitrini Drink's</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; padding: 30px 2%; }
        
        .form-card { 
            background: #fff; 
            max-width: 1600px; 
            width: 95%; 
            margin: 0 auto; 
            padding: 50px; 
            border-radius: 15px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.1); 
        }

        .header-box { text-align: center; margin-bottom: 40px; }
        .header-box h2 { font-size: 2.2em; color: #333; }

        .section-title { 
            border-bottom: 2px solid #0d6efd; 
            padding-bottom: 10px; 
            margin: 40px 0 25px; 
            color: #0d6efd; 
            font-size: 1.3em; 
            font-weight: bold;
            display: flex; 
            align-items: center; 
            gap: 12px; 
        }

        .form-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 25px; 
        }

        .input-group { margin-bottom: 5px; }
        .input-group label { display: block; margin-bottom: 10px; font-weight: 600; color: #555; }
        .input-group input, .input-group select { 
            width: 100%; 
            padding: 14px; 
            border: 1px solid #ccc; 
            border-radius: 10px; 
            font-size: 1em; 
            transition: all 0.3s;
        }
        .input-group input:focus { border-color: #0d6efd; box-shadow: 0 0 8px rgba(13, 110, 253, 0.15); outline: none; }

        .btn-copy { 
            background: #f8f9fa; 
            border: 2px dashed #0d6efd; 
            color: #0d6efd;
            padding: 15px; 
            border-radius: 10px; 
            cursor: pointer; 
            margin: 20px 0; 
            width: 100%; 
            font-weight: bold; 
            font-size: 1.1em;
            transition: 0.3s;
        }
        .btn-copy:hover { background: #eef4ff; }

        .btn-submit { 
            background: #198754; 
            color: white; 
            border: none; 
            padding: 20px; 
            border-radius: 10px; 
            font-size: 1.4em; 
            font-weight: bold; 
            cursor: pointer; 
            width: 100%; 
            margin-top: 40px; 
            box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
        }
        .btn-submit:hover { background: #157347; transform: translateY(-2px); }

        .alert { padding: 20px; border-radius: 10px; margin-bottom: 30px; text-align: center; font-weight: bold; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .footer-nav { text-align: center; margin-top: 30px; font-size: 1.1em; }
        .footer-nav a { color: #0d6efd; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="form-card">
    <div class="header-box">
        <h2><i class="fas fa-user-plus"></i> Cadastro de Novo Cliente</h2>
        <p>Preencha os dados abaixo com atenção para criar sua conta.</p>
    </div>

    <?php if ($sucesso): ?>
        <script>alert('Cadastro realizado com sucesso!'); window.location.href='login_cliente.php';</script>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= $error_msg ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="section-title"><i class="fas fa-id-card"></i> 1. Identificação</div>
        <div class="form-grid">
            <div class="input-group">
                <label>Nome Completo:</label>
                <input type="text" name="nome" placeholder="Ex: João Silva" required value="<?= htmlspecialchars($nome ?? '') ?>">
            </div>
            <div class="input-group">
                <label>E-mail (Login):</label>
                <input type="email" name="email" placeholder="email@exemplo.com" required value="<?= htmlspecialchars($email ?? '') ?>">
            </div>
            <div class="input-group">
                <label>CPF:</label>
                <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" required maxlength="14" value="<?= htmlspecialchars($cpf ?? '') ?>">
            </div>
            <div class="input-group">
                <label>Data de Nascimento:</label>
                <input type="date" name="data_nascimento" max="<?= $dataMaximaFormatada ?>" required value="<?= $data_nascimento ?? '' ?>">
            </div>
            <div class="input-group">
                <label>Gênero:</label>
                <select name="genero">
                    <option value="Masculino">Masculino</option>
                    <option value="Feminino">Feminino</option>
                    <option value="Outro">Outro</option>
                </select>
            </div>
        </div>

        <div class="section-title"><i class="fas fa-map-marked-alt"></i> 2. Endereço de Faturamento</div>
        <div class="form-grid">
            <div class="input-group">
                <label>CEP:</label>
                <input type="text" name="cep_faturamento" id="cep_f" placeholder="00000-000" onblur="buscarCep('f')" required>
            </div>
            <div class="input-group" style="grid-column: span 2;">
                <label>Rua / Logradouro:</label>
                <input type="text" name="endereco_faturamento" id="rua_f" required>
            </div>
            <div class="input-group">
                <label>Número:</label>
                <input type="text" name="numero_faturamento" required>
            </div>
            <div class="input-group">
                <label>Complemento:</label>
                <input type="text" name="complemento_faturamento" placeholder="Apto, Bloco, etc.">
            </div>
            <div class="input-group">
                <label>Bairro:</label>
                <input type="text" name="bairro_faturamento" id="bairro_f" required>
            </div>
            <div class="input-group">
                <label>Cidade:</label>
                <input type="text" name="cidade_faturamento" id="cid_f" required>
            </div>
            <div class="input-group">
                <label>Estado (UF):</label>
                <input type="text" name="uf_faturamento" id="uf_f" maxlength="2" required>
            </div>
        </div>

        <button type="button" class="btn-copy" onclick="copiarEndereco()">
            <i class="fas fa-clone"></i> Clique aqui se o endereço de ENTREGA for o mesmo do FATURAMENTO
        </button>

        <div class="section-title"><i class="fas fa-truck-loading"></i> 3. Endereço de Entrega (Opcional)</div>
        <div class="form-grid">
            <div class="input-group">
                <label>CEP Entrega:</label>
                <input type="text" name="cep_entrega" id="cep_e" onblur="buscarCep('e')">
            </div>
            <div class="input-group" style="grid-column: span 2;">
                <label>Rua / Logradouro:</label>
                <input type="text" name="endereco_entrega" id="rua_e">
            </div>
            <div class="input-group">
                <label>Número:</label>
                <input type="text" name="numero_entrega" id="num_e">
            </div>
            <div class="input-group">
                <label>Complemento:</label>
                <input type="text" name="complemento_entrega" id="comp_e">
            </div>
            <div class="input-group">
                <label>Bairro:</label>
                <input type="text" name="bairro_entrega" id="bairro_e">
            </div>
            <div class="input-group">
                <label>Cidade:</label>
                <input type="text" name="cidade_entrega" id="cid_e">
            </div>
            <div class="input-group">
                <label>Estado (UF):</label>
                <input type="text" name="uf_entrega" id="uf_e" maxlength="2">
            </div>
        </div>

        <div class="section-title"><i class="fas fa-key"></i> 4. Segurança da Conta</div>
        <div class="form-grid">
            <div class="input-group">
                <label>Senha:</label>
                <input type="password" name="senha" placeholder="Mínimo 8 caracteres" required>
            </div>
            <div class="input-group">
                <label>Confirmar Senha:</label>
                <input type="password" name="check-senha" placeholder="Repita a senha" required>
            </div>
        </div>

        <button type="submit" class="btn-submit">CONCLUIR MEU CADASTRO</button>
        
        <div class="footer-nav">
            Já tem uma conta? <a href="login_cliente.php">Faça login aqui</a><br><br>
            <a href="index.php" style="color: #666; font-weight: normal;"><i class="fas fa-arrow-left"></i> Voltar à Loja</a>
        </div>
    </form>
</div>

<script>
    function buscarCep(t) {
        let cep = document.getElementById(`cep_${t}`).value.replace(/\D/g, '');
        if (cep.length === 8) {
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(r => r.json())
                .then(d => {
                    if(!d.erro) {
                        document.getElementById(`rua_${t}`).value = d.logradouro;
                        document.getElementById(`bairro_${t}`).value = d.bairro;
                        document.getElementById(`cid_${t}`).value = d.localidade;
                        document.getElementById(`uf_${t}`).value = d.uf;
                    } else { alert("CEP não encontrado!"); }
                });
        }
    }

    function copiarEndereco() {
        document.getElementById('cep_e').value = document.getElementById('cep_f').value;
        document.getElementById('rua_e').value = document.getElementById('rua_f').value;
        document.getElementById('bairro_e').value = document.getElementById('bairro_f').value;
        document.getElementById('cid_e').value = document.getElementById('cid_f').value;
        document.getElementById('uf_e').value = document.getElementById('uf_f').value;
        document.getElementById('num_e').value = document.getElementsByName('numero_faturamento')[0].value;
        document.getElementById('comp_e').value = document.getElementsByName('complemento_faturamento')[0].value;
    }
</script>

</body>
</html>