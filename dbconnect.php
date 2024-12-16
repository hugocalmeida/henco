<?php

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    // Redireciona o usuário para a página de login se não estiver logado
    header('Location: index.php');
    exit();
}

// Conexão com o banco de dados
require_once 'config/config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Verifica a conexão com o banco de dados
if ($mysqli->connect_error) {
    die('Erro na conexão com o banco de dados (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Corrige a consulta para obter o papel do usuário
$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare('SELECT role_id FROM users WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($role_id);
$stmt->fetch();
$stmt->close();

// Verifica se o usuário é admin (assumindo que role_id == 1 é admin)
$is_admin = ($role_id == 1);

// Define o valor da sessão para is_admin, que pode ser usado em outras partes do código
$_SESSION['is_admin'] = $is_admin;
?>