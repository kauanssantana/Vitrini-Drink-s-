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

?>