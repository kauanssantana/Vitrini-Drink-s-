<?php
session_start();

// SEGURANÇA: Apenas Administradores podem aceder a esta página
if (!isset($_SESSION['user']) || $_SESSION['user']['grupo'] !== 'Administrador') {
    header("Location: principal.php");
    exit();
}

require_once('conexao.php');

$error = "";
$success = "";

// Função para validar o CPF
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$t] != $d) {
            return false;
        }
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']); // Limpa a máscara do CPF
    $grupo = $_POST['grupo'];
    $senha = $_POST['senha'];
    $senha_confirmar = $_POST['check-senha'];

    if ($senha !== $senha_confirmar) {
        $error = "As palavras-passe não coincidem.";
    } elseif (!validarCPF($cpf)) {
        $error = "O CPF introduzido é inválido.";
    } else {
        // Verifica se o E-mail ou CPF já existem na base de dados
        $check_query = "SELECT id FROM usuarios WHERE email = ? OR cpf = ?";
        $stmt_check = mysqli_prepare($con, $check_query);
        mysqli_stmt_bind_param($stmt_check, "ss", $email, $cpf);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = "Este E-mail ou CPF já está registado no sistema!";
        } else {
            // SEGURANÇA: Hash da senha e Prepared Statement para o Insert
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $status = 'Ativo';

            $sql = "INSERT INTO usuarios (nome, email, cpf, grupo, senha, status) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssssss", $nome, $email, $cpf, $grupo, $senha_hash, $status);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Utilizador registado com sucesso!";
                    // Limpa os campos após o sucesso para não reenviar os mesmos dados
                    $nome = $email = $cpf = "";
                } else {
                    $error = "Erro ao registar o utilizador no sistema.";
                }
                mysqli_stmt_close($stmt);
            }
        }
        mysqli_stmt_close($stmt_check);
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registar Novo Utilizador - Vitrini Drink's</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .form-card {
            background: #fff;
            width: 100%;
            max-width: 600px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: #333;
            margin: 0;
            font-size: 1.8em;
        }

        .form-header p {
            color: #6c757d;
            margin-top: 5px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #495057;
        }

        .input-group input, .input-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .input-group input:focus, .input-group select:focus {
            border-color: #0d6efd;
            outline: none;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 40px;
            cursor: pointer;
            color: #6c757d;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }

        .buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-primary, .btn-secondary {
            flex: 1;
            padding: 12px;
            font-size: 1.1em;
            text-align: center;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
        }
    </style>

    <script>
        function mascaraCPF(cpf) {
            cpf = cpf.replace(/\D/g, "");
            cpf = cpf.substring(0, 11);

            if (cpf.length <= 3) {
                cpf = cpf.replace(/(\d{1,3})/, "$1");
            } else if (cpf.length <= 6) {
                cpf = cpf.replace(/(\d{3})(\d{1,3})/, "$1.$2");
            } else if (cpf.length <= 9) {
                cpf = cpf.replace(/(\d{3})(\d{3})(\d{1,3})/, "$1.$2.$3");
            } else {
                cpf = cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, "$1.$2.$3-$4");
            }
            return cpf;
        }

        function aplicarMascaraCPF(input) {
            input.value = mascaraCPF(input.value);
        }

        function mostrarSenhas() {
            var inputSenha = document.getElementById("senha");
            var inputCheck = document.getElementById("check-senha");
            var icones = document.querySelectorAll(".toggle-password");
            
            if (inputSenha.type === "password") {
                inputSenha.type = "text";
                inputCheck.type = "text";
                icones.forEach(i => { i.classList.remove("fa-eye"); i.classList.add("fa-eye-slash"); });
            } else {
                inputSenha.type = "password";
                inputCheck.type = "password";
                icones.forEach(i => { i.classList.remove("fa-eye-slash"); i.classList.add("fa-eye"); });
            }
        }
    </script>
</head>
<body>

<div class="form-card">
    <div class="form-header">
        <h2><i class="fas fa-user-plus"></i> Novo Utilizador</h2>
        <p>Registo de funcionários no sistema</p>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
        <a href="listar_usuario.php" class="btn-primary" style="display: block; margin-bottom: 20px;">Ir para a Lista de Utilizadores</a>
    <?php endif; ?>

    <form action="cadastro_usuario.php" method="POST">
        <div class="input-group">
            <label for="nome">Nome Completo:</label>
            <input type="text" name="nome" id="nome" placeholder="Digite o nome completo" value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>" required>
        </div>
        
        <div class="input-group">
            <label for="email">E-mail de Acesso:</label>
            <input type="email" name="email" id="email" placeholder="exemplo@vitrinidrinks.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
        </div>

        <div class="input-group">
            <label for="cpf">CPF:</label>
            <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" required oninput="aplicarMascaraCPF(this)" maxlength="14" value="<?php echo isset($_POST['cpf']) ? htmlspecialchars($_POST['cpf']) : ''; ?>">
        </div>

        <div class="input-group">
            <label for="grupo">Nível de Acesso (Grupo):</label>
            <select name="grupo" id="grupo" required>
                <option value="Estoquista" <?php echo (isset($grupo) && $grupo == 'Estoquista') ? 'selected' : ''; ?>>Estoquista</option>
                <option value="Administrador" <?php echo (isset($grupo) && $grupo == 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
            </select>
        </div>

        <div class="input-group password-container">
            <label for="senha">Palavra-passe:</label>
            <input type="password" name="senha" id="senha" placeholder="Crie uma palavra-passe segura" required>
            <i class="fas fa-eye toggle-password" onclick="mostrarSenhas()" title="Mostrar palavra-passe"></i>
        </div>

        <div class="input-group password-container">
            <label for="check-senha">Confirmar Palavra-passe:</label>
            <input type="password" name="check-senha" id="check-senha" placeholder="Repita a palavra-passe" required>
            <i class="fas fa-eye toggle-password" onclick="mostrarSenhas()" title="Mostrar palavra-passe"></i>
        </div>

        <div class="buttons">
            <a href="listar_usuario.php" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary" name="cadastrar">Registar Utilizador</button>
        </div>
    </form>
</div>

</body>
</html>
<?php mysqli_close($con); ?>