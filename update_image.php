<?php
session_start();
require_once('conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'setMainImage') {
        $imageName = $_POST['imageName'];

        $updateQuery = "UPDATE imagens_produto SET principal = 1 WHERE caminho_imagem = '$imageName'";
        if (mysqli_query($con, $updateQuery)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao definir imagem principal']);
        }
    }

    if ($action === 'removeImage') {
        $imageName = $_POST['imageName'];

        $deleteQuery = "DELETE FROM imagens_produto WHERE caminho_imagem = '$imageName'";
        if (mysqli_query($con, $deleteQuery)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao remover imagem']);
        }
    }

    mysqli_close($con);
}
?>
