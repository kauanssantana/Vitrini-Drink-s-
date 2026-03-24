<?php

if(isset($_POST['cadastrar'])){
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $email = $_POST['email'];
    $grupo = $_POST['grupo'];
    $senha = $_POST['senha'];
    $check_senha = $_POST['check-senha'];

    if($senha != $check_senha){
        die("As senhas não correspondem");
    }

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $host = "localhost";
    $banco = "cadastroteste";
    $user = "root";
    $senha_user = "";

    $con = mysqli_connect($host, $user, $senha_user, $banco);
    mysqli_options($con, MYSQLI_OPT_CONNECT_TIMEOUT, 10); 

    if(!$con) {
        die("Conexão falhou: " . mysqli_connect_error());
    }

    $sql = "INSERT INTO usuarios (nome, cpf, email, grupo, senha) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);

    mysqli_stmt_bind_param($stmt, 'sssss', $nome, $cpf, $email, $grupo, $senha_hash);
    $rs = mysqli_stmt_execute($stmt);

    if($rs){
        header("Location: sucesso.php"); 
        exit(); 
    } else {
       
        die("Erro ao cadastrar usuário: " . mysqli_error($con));
    }

    mysqli_close($con);
}
?>
