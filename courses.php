<?php
session_start();
include('conexionDB.php');

// Verifica si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: courses.php"); // Redirige si no está autenticado
    exit();
}

$username = $_SESSION['username'];
$query = pg_query("
    SELECT c.nomb_cur 
    FROM cursos c 
    JOIN docentes d ON d.cod_doc = c.cod_doc 
    WHERE d.nomb_doc = '$username'
");
if (!$query) {
    echo "Error en la consulta.";
    exit();
}

// Establece la fecha actual
$fechaActual = date("d/m/Y");

?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Notas</title>
    <link rel="stylesheet" href="/styles/coursesStyle.css">
</head>
<body>

<div class="container">
    <!-- Encabezado principal -->
    <div class="header">
        <span>REGISTRO DE NOTAS</span>
        <span class="date"><?php echo $fechaActual; ?></span>
        <a href="index.php" style="color: white; text-decoration: none;">Inicio</a>
    
    </div>

    <!-- Sub-encabezado -->
    <div class="sub-header">INFORMACION DE DOCENTES</div>

    <!-- Contenedor del formulario -->
    <div class="form-container">
        <h3>CURSOS DE DOCENTE</h3>
        <form method="POST" action="listadoEstudiantes.php" class="selectors-container">
            <label for="cursos">Cursos:</label>
            <select name="nomb_cur" id="cursos" required>
                <option value="">Selecciona un curso</option>
                <?php
                while ($row = pg_fetch_assoc($query)) {
                    echo '<option value="' . htmlspecialchars($row['nomb_cur']) . '">' . htmlspecialchars($row['nomb_cur']) . '</option>';
                }
                ?>
            </select>
            <label for="periodo">Período:</label>
            <select name="periodo" id="periodo" required>
                <option value="1">Periodo I</option>
                <option value="2">Periodo II</option>
            </select>
            
            <label for="year">Año:</label>
            <input type="text" name="year" id="year" placeholder="2024"  title="Ingrese un año de 4 dígitos entre 1944 y 2104">
            <label for="estudiantes" style="font-weight: bold; display: block; margin-top: 10px;">Estudiantes:</label>
            <button type="submit" name="verListado" class="btn">Ver listado</button>
                
            <label for="notas" style="font-weight: bold; display: block; margin-top: 10px;">Notas:</label>
            <button type="submit" name="verListado" class="btn">Ver y editar notas</button>
        
        </form>
    </div>
</div>


</body>
</html>