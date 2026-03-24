<?php
session_start();

// SEGURANÇA: Apenas Administradores podem editar utilizadores
if (!isset($_SESSION['user']) || $_SESSION['user']['grupo'] !== 'Administrador') {
    header("Location: principal.php");
    exit();
}

require_once('conexao.php');

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

$error = "";
$success = "";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listar_usuario.php");
    exit();
}

$id = (int)$_GET['id'];

// Busca os dados do utilizador
$query = "SELECT * FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    header("Location: listar_usuario.php");
    exit();
}

// Processa o formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
    $grupo = $_POST['grupo'];
    $status = $_POST['status']; 
    $senha = $_POST['senha'];
    $senha_confirmar = $_POST['check-senha'];

    if (!validarCPF($cpf)) {
        $error = "O CPF introduzido é inválido.";
    } elseif (!empty($senha) && $senha !== $senha_confirmar) {
        $error = "As senhas não coincidem.";
    } else {
        $check_cpf = "SELECT id FROM usuarios WHERE cpf = ? AND id != ?";
        $stmt_check = mysqli_prepare($con, $check_cpf);
        mysqli_stmt_bind_param($stmt_check, "si", $cpf, $id);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = "Este CPF já está a ser utilizado por outro funcionário.";
        } else {
            if (!empty($senha)) {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $update_query = "UPDATE usuarios SET nome = ?, cpf = ?, grupo = ?, status = ?, senha = ? WHERE id = ?";
                $stmt_up = mysqli_prepare($con, $update_query);
                mysqli_stmt_bind_param($stmt_up, "sssssi", $nome, $cpf, $grupo, $status, $senha_hash, $id);
            } else {
                $update_query = "UPDATE usuarios SET nome = ?, cpf = ?, grupo = ?, status = ? WHERE id = ?";
                $stmt_up = mysqli_prepare($con, $update_query);
                mysqli_stmt_bind_param($stmt_up, "ssssi", $nome, $cpf, $grupo, $status, $id);
            }

            if (mysqli_stmt_execute($stmt_up)) {
                $success = "Dados atualizados com sucesso!";
                $row['nome'] = $nome;
                $row['cpf'] = $cpf;
                $row['grupo'] = $grupo;
                $row['status'] = $status;
            } else {
                $error = "Erro ao atualizar os dados no sistema.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário - Vitrini Drink's</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; display: flex; flex-direction: column; min-height: 100vh; }

        .admin-header { 
            background-color: #1a1a1a; color: #ffc107; padding: 25px 5%; 
            display: flex; justify-content: space-between; align-items: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.2); text-transform: uppercase; font-weight: 800;
        }

        .main-wrapper { flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px 20px; }

        .form-card { 
            background: #fff; width: 100%; max-width: 800px; padding: 50px; 
            border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border-top: 6px solid #0d6efd;
        }

        .form-header { text-align: center; margin-bottom: 35px; }
        .form-header h2 { font-size: 2em; color: #1a1a1a; margin-bottom: 10px; }

        .input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }

        .input-group { margin-bottom: 25px; position: relative; }
        .input-group label { display: block; margin-bottom: 10px; font-weight: 700; color: #444; }
        
        .input-group input, .input-group select { 
            width: 100%; padding: 15px; border: 2px solid #eee; border-radius: 10px; font-size: 1em; transition: 0.3s; outline: none;
        }
        .input-group input:focus { border-color: #0d6efd; background: #f0f7ff; }

        .input-readonly { background-color: #f8f9fa; cursor: not-allowed; color: #888; border-color: #ddd !important; }

        .toggle-password { position: absolute; right: 15px; top: 48px; cursor: pointer; color: #aaa; font-size: 1.2em; }

        .alert { padding: 15px; border-radius: 10px; margin-bottom: 25px; font-weight: bold; text-align: center; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        .btn-container { display: flex; gap: 20px; margin-top: 20px; }
        .btn { flex: 1; padding: 18px; border-radius: 12px; font-weight: 700; font-size: 1.1em; cursor: pointer; border: none; transition: 0.3s; text-decoration: none; text-align: center; }
        .btn-save { background: #0d6efd; color: white; }
        .btn-save:hover { background: #0b5ed7; transform: translateY(-2px); }
        .btn-cancel { background: #6c757d; color: white; }
        .btn-cancel:hover { background: #5a6268; }
    </style>

    <script>
        function aplicarMascaraCPF(input) {
            let v = input.value.replace(/\D/g, "");
            v = v.substring(0, 11);
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            input.value = v;
        }

        function mostrarSenhas() {
            const senha = document.getElementById("senha");
            const check = document.getElementById("check-senha");
            const icons = document.querySelectorAll(".toggle-password");
            const type = senha.type === "password" ? "text" : "password";
            senha.type = type;
            check.type = type;
            icons.forEach(i => i.classList.toggle("fa-eye-slash"));
        }

        window.onload = function() { aplicarMascaraCPF(document.getElementById('cpf')); };
    </script>
</head>
<body>

<header class="admin-header">
    <div>VITRINI DRINK'S</div>
    <div style="font-size: 0.8em;">Modo Edição de Usuário</div>
</header>

<div class="main-wrapper">
    <div class="form-card">
        <div class="form-header">
            <h2><i class="fas fa-user-edit"></i> Editar Usuário</h2>
            <p>Alterando dados do registro ID: #<?php echo $id; ?></p>
        </div>

        <?php if ($error): ?> <div class="alert alert-error"><i class="fas fa-times-circle"></i> <?php echo $error; ?></div> <?php endif; ?>
        <?php if ($success): ?> <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div> <?php endif; ?>

        <form method="POST">
            <div class="input-grid">
                <div class="input-group full-width">
                    <label>E-mail (Login)</label>
                    <input type="text" value="<?php echo htmlspecialchars($row['email']); ?>" class="input-readonly" readonly>
                </div>

                <div class="input-group full-width">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" value="<?php echo htmlspecialchars($row['nome']); ?>" required>
                </div>

                <div class="input-group">
                    <label>CPF</label>
                    <input type="text" name="cpf" id="cpf" value="<?php echo htmlspecialchars($row['cpf']); ?>" oninput="aplicarMascaraCPF(this)" maxlength="14" required>
                </div>

                <div class="input-grid" style="grid-column: span 1; gap: 10px;">
                    <div class="input-group">
                        <label>Grupo</label>
                        <select name="grupo" required>
                            <option value="Estoquista" <?php if($row['grupo']=='Estoquista') echo 'selected'; ?>>Estoquista</option>
                            <option value="Administrador" <?php if($row['grupo']=='Administrador') echo 'selected'; ?>>Administrador</option>
                        </select>
                    </div>
                </div>

                <div class="input-group full-width">
                    <label>Status da Conta</label>
                    <select name="status" required>
                        <option value="Ativo" <?php if($row['status']=='Ativo') echo 'selected'; ?>>Ativo</option>
                        <option value="Desativado" <?php if($row['status']=='Desativado') echo 'selected'; ?>>Desativado</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Nova Senha</label>
                    <input type="password" name="senha" id="senha" placeholder="Deixe em branco para não mudar">
                    <i class="fas fa-eye toggle-password" onclick="mostrarSenhas()"></i>
                </div>

                <div class="input-group">
                    <label>Confirmar Senha</label>
                    <input type="password" name="check-senha" id="check-senha" placeholder="Repita a nova senha">
                    <i class="fas fa-eye toggle-password" onclick="mostrarSenhas()"></i>
                </div>
            </div>

            <div class="btn-container">
                <a href="listar_usuario.php" class="btn btn-cancel">CANCELAR</a>
                <button type="submit" class="btn btn-save">SALVAR ALTERAÇÕES</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>