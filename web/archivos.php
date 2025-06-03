<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$directorio = "archivos/";
$papelera = $directorio . '._papelera/';

// Crear papelera si no existe
if (!is_dir($papelera)) mkdir($papelera, 0777, true);

$mensaje = "";
$ultimo_eliminado = "";

// Eliminar archivo (mover a papelera)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    $archivo = basename($_POST['eliminar']);
    $ruta = $directorio . $archivo;
    if (file_exists($ruta)) {
        rename($ruta, $papelera . $archivo); // Mueve a papelera
        $mensaje = "Archivo eliminado: $archivo";
        $ultimo_eliminado = $archivo;
        $_SESSION['ultimo_eliminado'] = $archivo;
    } else {
        $mensaje = "El archivo no existe.";
    }
}

// Restaurar archivo desde papelera
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deshacer'])) {
    $archivo = basename($_POST['deshacer']);
    if (file_exists($papelera . $archivo)) {
        rename($papelera . $archivo, $directorio . $archivo);
        $mensaje = "Archivo restaurado: $archivo";
        unset($_SESSION['ultimo_eliminado']);
    }
}

// AJAX: Borrar definitivamente tras timeout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrar_definitivo'])) {
    $archivo = basename($_POST['borrar_definitivo']);
    $papelera_archivo = $papelera . $archivo;
    if (file_exists($papelera_archivo)) {
        unlink($papelera_archivo);
        unset($_SESSION['ultimo_eliminado']);
        echo "ok";
    } else {
        echo "no";
    }
    exit;
}

// Mantener disponible el botón deshacer si corresponde
if (isset($_SESSION['ultimo_eliminado'])) {
    $ultimo_eliminado = $_SESSION['ultimo_eliminado'];
}

// Comprobación robusta del directorio principal
if (!is_dir($directorio)) {
    die("ERROR: El directorio $directorio no existe o no es accesible.");
}
$archivos = array_diff(scandir($directorio), array('.', '..', '._papelera'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestor de archivos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background: #f6f8fc;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0; padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 3rem auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(70, 70, 140, 0.08);
            padding: 2rem;
            position: relative;
        }
        h2 {
            text-align: center;
            color: #4e54c8;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }
        th, td {
            padding: 0.7rem;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        th {
            color: #6d6d9e;
            background: #f2f2fa;
            font-size: 1.2rem;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .actions {
            display: flex;
            gap: 0.9rem;
        }
        .btn {
            padding: 0.4rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.15s;
            font-weight: bold;
        }
        .btn-download {
            background: #4e54c8;
            color: #fff;
        }
        .btn-download:hover {
            background: #393dc2;
        }
        .btn-delete {
            background: #d32f2f;
            color: #fff;
        }
        .btn-delete:hover {
            background: #a31515;
        }
        .btn-undo {
            background: #4caf50;
            color: #fff;
            margin-left: 1rem;
        }
        .btn-undo:hover {
            background: #388e3c;
        }
        .mensaje {
            margin: 1rem auto 0;
            background: #eafaf1;
            color: #2e7d32;
            padding: 0.7rem 1rem;
            border-radius: 6px;
            text-align: center;
            max-width: 80%;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }
        .logout {
            display: block;
            float: none;
            text-align: right;
            margin: 0 0 1rem 0;
            color: #d32f2f;
            text-decoration: none;
            font-weight: bold;
            background: #fee;
            padding: 0.3rem 0.8rem;
            border-radius: 8px;
            transition: background 0.15s;
            position: absolute;
            right: 2.5rem;
            top: 2rem;
            box-shadow: 0 2px 8px rgba(211, 47, 47, 0.07);
            font-size: 1.4rem;
        }
        .logout:hover {
            background: #fbb;
        }
        @media (max-width: 650px) {
            .container { padding: 1rem; }
            .logout { right: 1rem; top: 1rem; }
            h2 { font-size: 1.7rem; }
            .mensaje { font-size: 1rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="logout.php" class="logout">Cerrar sesión</a>
        <h2>Archivos disponibles</h2>
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje" id="mensaje">
                <?= htmlspecialchars($mensaje) ?>
                <?php if ($ultimo_eliminado): ?>
                    <form method="post" style="display:inline;" id="formDeshacer">
                        <input type="hidden" name="deshacer" value="<?= htmlspecialchars($ultimo_eliminado) ?>">
                        <button class="btn btn-undo" type="submit">Deshacer</button>
                    </form>
                    <span id="timer" style="color:#388e3c; font-size:1rem;"></span>
                    <script>
                        let segundos = 7;
                        let timer = document.getElementById('timer');
                        timer.textContent = `(${segundos}s para deshacer)`;
                        let countdown = setInterval(() => {
                            segundos--;
                            if(segundos>0){
                                timer.textContent = `(${segundos}s para deshacer)`;
                            } else {
                                timer.textContent = '';
                                clearInterval(countdown);
                                // AJAX para borrar definitivamente
                                fetch('', {
                                    method: 'POST',
                                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                    body: 'borrar_definitivo=<?= htmlspecialchars($ultimo_eliminado) ?>'
                                }).then(resp=>resp.text()).then(resp=>{
                                    document.getElementById('mensaje').textContent = "Archivo eliminado permanentemente";
                                    setTimeout(()=>window.location.reload(),1200);
                                });
                            }
                        }, 1000);
                        // Si se pulsa deshacer, detenemos el temporizador
                        document.getElementById('formDeshacer').addEventListener('submit', ()=>clearInterval(countdown));
                    </script>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($archivos)): ?>
            <p style="text-align:center; color: #888;">No hay archivos para mostrar.</p>
        <?php else: ?>
        <table>
            <tr>
                <th>Archivo</th>
                <th style="width: 170px;">Acciones</th>
            </tr>
            <?php foreach ($archivos as $archivo): ?>
            <tr>
                <td><?= htmlspecialchars($archivo) ?></td>
                <td>
                    <div class="actions">
                        <a class="btn btn-download" href="<?= $directorio . urlencode($archivo) ?>" download>Descargar</a>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="eliminar" value="<?= htmlspecialchars($archivo) ?>">
                            <button class="btn btn-delete" type="submit" onclick="return confirm('¿Eliminar <?= htmlspecialchars($archivo) ?>?')">Eliminar</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</body>
</html>