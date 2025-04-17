<?php
// logout.php

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Destrói a sessão
session_destroy();

// Redireciona para a página de login com a mensagem de sucesso
header('Location: index.php?logout=success');
exit();
?>
