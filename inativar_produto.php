<?php
require_once('conexao.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "UPDATE produtos SET status = 'Desativado' WHERE id = $id";

    if (mysqli_query($con, $sql)) {
        header('Location: listar_produto.php');
    } else {
        echo "Erro ao inativar o produto: " . mysqli_error($con);
    }
}

mysqli_close($con);
?>