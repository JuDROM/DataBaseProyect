<?php
session_start();
include('conexionDB.php'); 

// Procesar el inicio de sesión si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $query = pg_query_params("SELECT cod_doc, nomb_doc, clave FROM docentes WHERE nomb_doc = $1", array($username));

    if ($query && pg_num_rows($query) > 0) {
        $user = pg_fetch_assoc($query);  

        if ($user['clave'] === $password) {
            $_SESSION['username'] = $username;
            if ($username === 'root') {
                header("Location: main.php");
            } else {
                header("Location: courses.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Contraseña incorrecta.";
        }
    } else {
        $_SESSION['error'] = "Usuario no encontrado.";
    }
    pg_close($dbconn);

    // Redirigir para mostrar el mensaje de error sin reenviar el formulario
    header("Location: index.php");
    exit();
}

// Obtener el mensaje de error de la sesión si existe y luego eliminarlo
$error = "";
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Iniciar sesión</h2>

        <!-- Div para mostrar mensajes de error -->
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="index.php" method="post">
            <label for="username">Nombre:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password">
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>