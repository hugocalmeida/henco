<?php
// setup.php

// Verifica se o setup já foi realizado
if (file_exists(__DIR__ . '/config/config.php')) {
    die("O sistema já está configurado. Apague o ficheiro config.php se deseja configurar novamente.");
}

// Tratamento do envio do formulário se foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['db_host'];
    $dbname = $_POST['db_name'];
    $user = $_POST['db_user'];
    $password = $_POST['db_password'];

    // Criação do ficheiro config.php
    $configContent = "<?php\n"
        . "define('DB_HOST', '$host');\n"
        . "define('DB_NAME', '$dbname');\n"
        . "define('DB_USER', '$user');\n"
        . "define('DB_PASSWORD', '$password');\n";

    if (!file_exists(__DIR__ . '/config')) {
        mkdir(__DIR__ . '/config', 0755, true);
    }

    file_put_contents(__DIR__ . '/config/config.php', $configContent);

    // Finaliza o setup
    echo "<div class='success-message'>
            <h2>Configuração Concluída <i class='fas fa-check-circle' style='color: #28a745;'></i></h2>
            <p>O sistema foi configurado com sucesso!</p>
            <p>Por motivos de segurança, recomendamos que apague o ficheiro <code>setup.php</code> agora.</p>
            <a href='index.php' class='btn'>Ir para a Página Inicial</a>
          </div>
          <style>
            body {
                font-family: 'Roboto', sans-serif;
                background-color: #f0f2f5;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .success-message {
                background: #fff;
                padding: 2rem;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                text-align: center;
                width: 100%;
                max-width: 400px;
            }
            .success-message h2 {
                color: #28a745;
                margin-bottom: 1rem;
            }
            .success-message p {
                color: #333;
                margin-bottom: 1.5rem;
            }
            .success-message .btn {
                display: inline-block;
                padding: 0.75rem 1.5rem;
                background-color: #007bff;
                color: #fff;
                text-decoration: none;
                border-radius: 4px;
                transition: background-color 0.3s ease;
            }
            .success-message .btn:hover {
                background-color: #0056b3;
            }
          </style>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup do Sistema</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .setup-container {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
        }
        label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 0.5rem;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button[type="submit"] {
            width: 100%;
            padding: 1rem;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #0056b3;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h2><i class="fas fa-cogs"></i> Configuração Inicial do Sistema</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="db_host">Servidor da Base de Dados:</label>
                <input type="text" id="db_host" name="db_host" required>
            </div>
            <div class="form-group">
                <label for="db_name">Nome da Base de Dados:</label>
                <input type="text" id="db_name" name="db_name" required>
            </div>
            <div class="form-group">
                <label for="db_user">Utilizador da Base de Dados:</label>
                <input type="text" id="db_user" name="db_user" required>
            </div>
            <div class="form-group">
                <label for="db_password">Senha da Base de Dados:</label>
                <input type="password" id="db_password" name="db_password" required>
            </div>
            <button type="submit">Configurar</button>
        </form>
    </div>
</body>
</html>
