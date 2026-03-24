<?php
session_start();

// SEGURANÇA: Apenas Administradores podem aceder ao cadastro de produtos
if (!isset($_SESSION['user']) || $_SESSION['user']['grupo'] !== 'Administrador') {
    header("Location: principal.php");
    exit();
}

require_once('conexao.php');

// Tratamento de mensagens via sessão
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['error'], $_SESSION['success'], $_SESSION['form_data']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = trim($_POST['codigo-produto']);
    $nome = trim($_POST['nome-produto']);
    $preco = str_replace(',', '.', $_POST['preco']); 
    $estoque = (int)$_POST['estoque'];
    $descricao = trim($_POST['descricao']);
    $avaliacao = (float)$_POST['avaliacao'];

    $query_check = "SELECT id FROM produtos WHERE codigo = ?";
    $stmtCheck = mysqli_prepare($con, $query_check);
    mysqli_stmt_bind_param($stmtCheck, "s", $codigo);
    mysqli_stmt_execute($stmtCheck);
    mysqli_stmt_store_result($stmtCheck);

    if (mysqli_stmt_num_rows($stmtCheck) > 0) {
        $_SESSION['error'] = "O código do produto já existe no sistema.";
        $_SESSION['form_data'] = $_POST; 
        header("Location: cadastro_produto.php");
        exit();
    }
    mysqli_stmt_close($stmtCheck);

    $status = 'Ativo';
    $sql_insert = "INSERT INTO produtos (codigo, nome, valor, quantidade, descricao, avaliacao, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = mysqli_prepare($con, $sql_insert);
    
    if ($stmt_insert) {
        mysqli_stmt_bind_param($stmt_insert, "ssdisds", $codigo, $nome, $preco, $estoque, $descricao, $avaliacao, $status);
        
        if (mysqli_stmt_execute($stmt_insert)) {
            $produto_id = mysqli_insert_id($con);
            
            if (isset($_FILES['imagem-produto']) && !empty($_FILES['imagem-produto']['name'][0])) {
                $images = $_FILES['imagem-produto'];
                $mainImageIndex = isset($_POST['imagem-principal']) ? (int)$_POST['imagem-principal'] : 0;

                for ($i = 0; $i < count($images['name']); $i++) {
                    if ($images['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($images['name'][$i], PATHINFO_EXTENSION);
                        $imagePath = 'img/' . uniqid() . '.' . $ext;
                        
                        if (move_uploaded_file($images['tmp_name'][$i], $imagePath)) {
                            $isMain = ($i === $mainImageIndex) ? 1 : 0;
                            
                            $sql_img = "INSERT INTO imagens_produto (produto_id, caminho_imagem, principal) VALUES (?, ?, ?)";
                            $stmt_img = mysqli_prepare($con, $sql_img);
                            mysqli_stmt_bind_param($stmt_img, "isi", $produto_id, $imagePath, $isMain);
                            mysqli_stmt_execute($stmt_img);
                            mysqli_stmt_close($stmt_img);
                        }
                    }
                }
            }

            $_SESSION['success'] = "Produto cadastrado com sucesso!";
            header("Location: cadastro_produto.php");
            exit();
        } else {
            $_SESSION['error'] = "Erro ao cadastrar produto no banco de dados.";
            $_SESSION['form_data'] = $_POST;
            header("Location: cadastro_produto.php");
            exit();
        }
        mysqli_stmt_close($stmt_insert);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Produto - Vitrini Drink's</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; color: #333; }

        .admin-header { 
            background-color: #1a1a1a; color: #ffc107; padding: 25px 5%; 
            display: flex; justify-content: space-between; align-items: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.2); text-transform: uppercase; font-weight: 800;
        }

        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        
        .form-card { 
            background: #fff; padding: 50px; border-radius: 20px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.1); border-top: 6px solid #198754; 
        }

        .form-header { text-align: center; margin-bottom: 35px; }
        .form-header h2 { font-size: 2em; color: #1a1a1a; margin-bottom: 5px; }
        .form-header p { color: #6c757d; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .span-2 { grid-column: span 2; }
        .span-3 { grid-column: span 3; }

        .input-group label { display: block; margin-bottom: 10px; font-weight: 700; color: #444; }
        .input-group input, .input-group textarea, .input-group select { 
            width: 100%; padding: 15px; border: 2px solid #eee; border-radius: 10px; 
            font-size: 1em; transition: 0.3s; outline: none;
        }
        .input-group input:focus, .input-group textarea:focus { border-color: #198754; background: #fafffa; }

        .input-group textarea { height: 120px; resize: vertical; }

        .image-upload-area { 
            border: 2px dashed #ddd; padding: 30px; text-align: center; 
            border-radius: 12px; background-color: #fafafa; margin-bottom: 25px; transition: 0.3s;
        }
        .image-upload-area:hover { border-color: #198754; background-color: #f0fdf4; }
        
        .image-upload-label { 
            display: inline-block; padding: 12px 25px; background: #1a1a1a; 
            color: #ffc107; border-radius: 8px; cursor: pointer; font-weight: bold; 
        }

        #image-preview-container { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 20px; justify-content: center; }
        .preview-item { 
            border: 2px solid #eee; padding: 10px; border-radius: 10px; 
            background: #fff; text-align: center; width: 130px; 
        }

        .alert { padding: 15px; border-radius: 10px; margin-bottom: 25px; font-weight: bold; text-align: center; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        .btn-container { display: flex; gap: 20px; margin-top: 20px; }
        .btn { flex: 1; padding: 18px; border-radius: 12px; font-weight: 700; font-size: 1.1em; border: none; cursor: pointer; transition: 0.3s; text-decoration: none; text-align: center; }
        .btn-save { background: #198754; color: white; }
        .btn-save:hover { background: #157347; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(25, 135, 84, 0.2); }
        .btn-cancel { background: #6c757d; color: white; }
        .btn-cancel:hover { background: #5a6268; }
    </style>
</head>
<body>

<header class="admin-header">
    <div>VITRINI DRINK'S</div>
    <div style="font-size: 0.8em; color: #ccc;">Gestão de Estoque</div>
</header>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <h2><i class="fas fa-plus-circle"></i> Cadastrar Novo Produto</h2>
            <p>Insira as informações detalhadas para a vitrine</p>
        </div>

        <?php if ($error): ?> <div class="alert alert-error"><i class="fas fa-times-circle"></i> <?php echo $error; ?></div> <?php endif; ?>
        <?php if ($success): ?> <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div> <?php endif; ?>

        <form action="cadastro_produto.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-row">
                <div class="input-group">
                    <label>Código (SKU)</label>
                    <input type="text" name="codigo-produto" placeholder="Ex: DRK001" value="<?= htmlspecialchars($form_data['codigo-produto'] ?? '') ?>" required>
                </div>
                <div class="input-group span-2">
                    <label>Nome do Produto</label>
                    <input type="text" name="nome-produto" placeholder="Ex: Gin Tanqueray 750ml" value="<?= htmlspecialchars($form_data['nome-produto'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="input-group">
                    <label>Preço (R$)</label>
                    <input type="number" name="preco" step="0.01" min="0" placeholder="0.00" value="<?= htmlspecialchars($form_data['preco'] ?? '') ?>" required>
                </div>
                <div class="input-group">
                    <label>Estoque Inicial</label>
                    <input type="number" name="estoque" min="0" placeholder="0" value="<?= htmlspecialchars($form_data['estoque'] ?? '') ?>" required>
                </div>
                <div class="input-group">
                    <label>Avaliação</label>
                    <select name="avaliacao" required>
                        <option value="5" <?= (isset($form_data['avaliacao']) && $form_data['avaliacao'] == '5') ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ (5.0)</option>
                        <option value="4" <?= (isset($form_data['avaliacao']) && $form_data['avaliacao'] == '4') ? 'selected' : '' ?>>⭐⭐⭐⭐ (4.0)</option>
                        <option value="3" <?= (isset($form_data['avaliacao']) && $form_data['avaliacao'] == '3') ? 'selected' : '' ?>>⭐⭐⭐ (3.0)</option>
                    </select>
                </div>
            </div>

            <div class="input-group span-3" style="margin-bottom: 25px;">
                <label>Descrição Completa</label>
                <textarea name="descricao" placeholder="Detalhes técnicos, ingredientes ou história do produto..." required><?= htmlspecialchars($form_data['descricao'] ?? '') ?></textarea>
            </div>

            <div class="input-group span-3">
                <label>Galeria de Fotos</label>
                <div class="image-upload-area">
                    <p style="margin-bottom: 15px; color: #666;">Envie fotos nítidas para atrair mais clientes</p>
                    <label for="imagem-produto" class="image-upload-label"><i class="fas fa-camera"></i> ADICIONAR FOTOS</label>
                    <input type="file" name="imagem-produto[]" id="imagem-produto" multiple accept="image/*" required onchange="previewImages()" style="display: none;">
                </div>
                <div id="image-preview-container"></div>
            </div>

            <div class="btn-container">
                <a href="listar_produto.php" class="btn btn-cancel">CANCELAR</a>
                <button type="submit" class="btn btn-save"><i class="fas fa-save"></i> FINALIZAR CADASTRO</button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImages() {
        const container = document.getElementById('image-preview-container');
        container.innerHTML = '';
        const files = document.getElementById('imagem-produto').files;

        if (files.length > 0) {
            const label = document.createElement('div');
            label.className = 'span-3';
            label.innerHTML = '<p style="margin: 15px 0; font-weight: bold; color: #198754;"><i class="fas fa-check-double"></i> Escolha a Capa do Produto:</p>';
            container.appendChild(label);

            for (let i = 0; i < files.length; i++) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" style="width: 100px; height: 100px; object-fit: contain; margin-bottom: 10px;">
                        <div style="font-size: 0.8em; font-weight: bold;">
                            <input type="radio" name="imagem-principal" value="${i}" ${i === 0 ? 'checked' : ''}> CAPA
                        </div>
                    `;
                    container.appendChild(div);
                };
                reader.readAsDataURL(files[i]);
            }
        }
    }
</script>

</body>
</html>