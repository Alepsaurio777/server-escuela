<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db.php';
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    $stmt = $conn->prepare("SELECT password FROM usuario WHERE username=?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();
    error_log('Filas encontradas: ' . $stmt->num_rows);
    $stmt->bind_result($hash);
    $stmt->bind_result($hash);
    if ($stmt->fetch() && password_verify($password, $hash)) {
        $_SESSION['usuario'] = $usuario;
        header("Location: archivos.php");
        exit();
    } else {
        $mensaje = "Usuario o contraseña incorrectos";
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .login-container {
            background: rgba(255,255,255,0.97);
            padding: 2.5rem 2rem 2rem 2rem;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(60,60,120,0.18);
            width: 100%;
            max-width: 340px;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #4e54c8;
            letter-spacing: 1px;
        }
        .login-container form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .login-container input {
            padding: 0.8rem 1rem;
            border: 1px solid #d6d6f2;
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            transition: border 0.2s;
        }
        .login-container input:focus {
            border: 1.5px solid #4e54c8;
        }
        .login-container button {
            padding: 0.9rem 1rem;
            background: linear-gradient(90deg, #4e54c8, #8f94fb);
            color: #fff;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            letter-spacing: 1px;
            transition: background 0.2s;
        }
        .login-container button:hover {
            background: linear-gradient(90deg, #393dc2, #6e72fa);
        }
        .login-container .mensaje {
            margin-top: 0.5rem;
            text-align: center;
            color: #d32f2f;
            background: #faeaea;
            padding: 0.6rem;
            border-radius: 6px;
            font-size: 0.97rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Iniciar sesión</h2>
        <form method="post" autocomplete="off">
            <input name="usuario" placeholder="Usuario" required>
            <input name="password" placeholder="Contraseña" type="password" required>
            <button type="submit">Entrar</button>
        </form>
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>