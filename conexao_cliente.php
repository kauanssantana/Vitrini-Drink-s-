<?php

$host = "localhost";
$banco = "cadastroteste";
$user = "root";
$senha_user = "";

$con = mysqli_connect($host, $user, $senha_user, $banco);
mysqli_options($con, MYSQLI_OPT_CONNECT_TIMEOUT, 10);

if (!$con) {
    die("Falha na conexão com o banco de dados: " . mysqli_connect_error());
}

mysqli_set_charset($con, 'utf8');

function cadastrarCliente($nome, $email, $cpf, $data_nascimento, $genero, $endereco_faturamento, $endereco_entrega, $senha) {
    global $con;

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $query = "INSERT INTO clientes (nome, email, cpf, data_nascimento, genero, endereco_faturamento, endereco_entrega, senha, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Ativo')";

    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ssssssss', $nome, $email, $cpf, $data_nascimento, $genero, $endereco_faturamento, $endereco_entrega, $senha_hash);
    $execute = mysqli_stmt_execute($stmt);

    if ($execute) {
        return true;
    } else {
        return false;
    }
}

function emailExistente($email) {
    global $con;

    $query = "SELECT * FROM clientes WHERE email = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        return true;
    }
    return false;
}

function cpfExistente($cpf) {
    global $con;

    $query = "SELECT * FROM clientes WHERE cpf = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 's', $cpf);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        return true;
    }
    return false;
}

function loginCliente($email, $senha) {
    global $con;

    $query = "SELECT * FROM clientes WHERE email = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $cliente = mysqli_fetch_assoc($result);

        if (password_verify($senha, $cliente['senha'])) {
            return $cliente;
        }
    }

    return false;
}

?>