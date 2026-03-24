<?php
session_start();

// SEGURANÇA: Apenas Administrador
if (!isset($_SESSION['user']) || $_SESSION['user']['grupo'] !== 'Administrador') {
    header("Location: login.php");
    exit();
}

require_once('conexao.php');

$error = "";
$success = "";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listar_produto.php");
    exit();
}

$id_produto = (int)$_GET['id'];

// 1. PROCESSA A ATUALIZAÇÃO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = trim($_POST['codigo-produto']);
    $nome = trim($_POST['nome-produto']);
    $preco = str_replace(',', '.', $_POST['preco']); 
    $estoque = (int)$_POST['estoque'];
    $descricao = trim($_POST['descricao']);
    $avaliacao = (float)$_POST['avaliacao'];

    $update_query = "UPDATE produtos SET codigo = ?, nome = ?, valor = ?, quantidade = ?, descricao = ?, avaliacao = ? WHERE id = ?";
    $stmt_up = mysqli_prepare($con, $update_query);
    
    if ($stmt_up) {
        mysqli_stmt_bind_param($stmt_up, "ssdisdi", $codigo, $nome, $preco, $estoque, $descricao, $avaliacao, $id_produto);
        
        if (mysqli_stmt_execute($stmt_up)) {
            $success = "Dados do produto atualizados com sucesso!";

            if (isset($_POST['imagem-principal'])) {
                $id_img_capa = (int)$_POST['imagem-principal'];
                mysqli_query($con, "UPDATE imagens_produto SET principal = 0 WHERE produto_id = $id_produto");
                mysqli_query($con, "UPDATE imagens_produto SET principal = 1 WHERE id = $id_img_capa");
            }

            if (isset($_FILES['imagem-produto']) && !empty($_FILES['imagem-produto']['name'][0])) {
                $images = $_FILES['imagem-produto'];
                for ($i = 0; $i < count($images['name']); $i++) {
                    if ($images['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($images['name'][$i], PATHINFO_EXTENSION);
                        $path = 'img/' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($images['tmp_name'][$i], $path)) {
                            mysqli_query($con, "INSERT INTO imagens_produto (produto_id, caminho_imagem, principal) VALUES ($id_produto, '$path', 0)");
                        }
                    }
                }
            }
        } else {
            $error = "Erro ao salvar no banco de dados.";
        }
        mysqli_stmt_close($stmt_up);
    }
}

// 2. BUSCA DADOS PARA EXIBIÇÃO
$res = mysqli_query($con, "SELECT * FROM produtos WHERE id = $id_produto");
$product = mysqli_fetch_assoc($res);

if (!$product) { header("Location: listar_produto.php"); exit(); }

$res_img = mysqli_query($con, "SELECT * FROM imagens_produto WHERE produto_id = $id_produto ORDER BY principal DESC");
$imagens = mysqli_fetch_all($res_img, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto - Vitrini Drink's</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; color: #333; padding-bottom: 50px; }

        .admin-header { background: #1a1a1a; color: #ffc107; padding: 25px 5%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }

        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .form-card { background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border-top: 6px solid #0d6efd; }

        .form-header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
        .input-group { flex: 1; min-width: 200px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #444; }
        .input-group input, .input-group textarea { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 1em; }
        .input-group input:focus { border-color: #0d6efd; outline: none; }

        .gallery-box { background: #f9f9f9; padding: 25px; border-radius: 12px; margin: 30px 0; border: 1px solid #eee; }
        .gallery-grid { display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px; }
        .image-card { background: #fff; border: 2px solid #ddd; padding: 10px; border-radius: 10px; width: 130px; text-align: center; cursor: pointer; transition: 0.3s; }
        .image-card.is-main { border-color: #198754; background: #f0fdf4; }
        .image-card img { width: 100px; height: 100px; object-fit: contain; margin-bottom: 10px; }

        .btn-container { display: flex; gap: 15px; margin-top: 30px; }
        .btn { flex: 1; padding: 15px; border-radius: 10px; font-weight: bold; cursor: pointer; text-align: center; text-decoration: none; border: none; font-size: 1.1em; }
        .btn-save { background: #0d6efd; color: white; }
        .btn-cancel { background: #6c757d; color: white; }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .alert-success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>

<header class="admin-header">
    <div style="font-weight: 800; font-size: 1.4em;">VITRINI DRINK'S</div>
    <div>ADMINISTRAÇÃO</div>
</header>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <h2><i class="fas fa-edit"></i> Editar Produto #<?php echo $id_produto; ?></h2>
        </div>

        <?php if($error) echo "<div class='alert alert-error'>$error</div>"; ?>
        <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="input-group" style="flex: 0.5;">
                    <label>Código (SKU)</label>
                    <input type="text" name="codigo-produto" value="<?php echo htmlspecialchars($product['codigo']); ?>" required>
                </div>
                <div class="input-group" style="flex: 1.5;">
                    <label>Nome do Produto</label>
                    <input type="text" name="nome-produto" value="<?php echo htmlspecialchars($product['nome']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="input-group">
                    <label>Preço (R$)</label>
                    <input type="number" name="preco" step="0.01" value="<?php echo $product['valor']; ?>" required>
                </div>
                <div class="input-group">
                    <label>Estoque</label>
                    <input type="number" name="estoque" value="<?php echo $product['quantidade']; ?>" required>
                </div>
                <div class="input-group">
                    <label>Avaliação (1-5)</label>
                    <input type="number" name="avaliacao" step="0.1" value="<?php echo $product['avaliacao']; ?>" required>
                </div>
            </div>

            <div class="input-group">
                <label>Descrição</label>
                <textarea name="descricao" rows="4" required><?php echo htmlspecialchars($product['descricao']); ?></textarea>
            </div>

            <div class="gallery-box">
                <label><i class="fas fa-images"></i> Gerenciar Imagens (Clique na capa)</label>
                <div class="gallery-grid">
                    <?php foreach ($imagens as $img): ?>
                        <div class="image-card <?php echo $img['principal'] ? 'is-main' : ''; ?>" onclick="marcarCapa(this, <?php echo $img['id']; ?>)">
                            <img src="<?php echo $img['caminho_imagem']; ?>" onerror="this.src='vitrine.jpg'">
                            <input type="radio" name="imagem-principal" id="rad-<?php echo $img['id']; ?>" value="<?php echo $img['id']; ?>" <?php echo $img['principal'] ? 'checked' : ''; ?> style="display:none">
                            <span style="font-size: 0.8em; font-weight: bold;"><?php echo $img['principal'] ? 'CAPA' : 'SELECIONAR'; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="margin-top: 20px;">
                    <label>Adicionar Novas Fotos:</label>
                    <input type="file" name="imagem-produto[]" multiple accept="image/*">
                </div>
            </div>

            <div class="btn-container">
                <a href="listar_produto.php" class="btn btn-cancel">CANCELAR</a>
                <button type="submit" class="btn btn-save">GUARDAR ALTERAÇÕES</button>
            </div>
        </form>
    </div>
</div>

<script>
    function marcarCapa(elemento, id) {
        document.querySelectorAll('.image-card').forEach(c => {
            c.classList.remove('is-main');
            c.querySelector('span').innerText = 'SELECIONAR';
        });
        elemento.classList.add('is-main');
        elemento.querySelector('span').innerText = 'CAPA';
        document.getElementById('rad-' + id).checked = true;
    }
</script>
</body>
</html>