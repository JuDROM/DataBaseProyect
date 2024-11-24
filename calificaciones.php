<?php
session_start();
include('conexionDB.php');

// Obtener variables de sesión y GET
$nota_id = $_POST['nota_id'] ?? '';
$nomb_cur = $_POST['nomb_cur'] ?? '';
$periodo = $_POST['periodo'] ?? '';
$year = $_POST['year'] ?? '';
$posicion = pg_fetch_assoc(pg_query("SELECT posicion FROM notas WHERE nota=$nota_id"))['posicion'];
// Obtener el código del curso
$cod_cur_result = pg_query("SELECT cod_cur FROM cursos WHERE nomb_cur = '$nomb_cur'");
$cod_cur = pg_fetch_result($cod_cur_result, 0, 0);

// Proceso para añadir una calificación
if (isset($_POST['addCalificacion'])) {
    $cod_est = $_POST['cod_est'];
    $valor = $_POST['valor'];
    $fecha = $_POST['fecha'];

    $insertCalificacionQuery = pg_query("INSERT INTO calificaciones (nota, valor, fecha, cod_cur, cod_est, year, periodo) VALUES ('$nota_id', '$valor', '$fecha', '$cod_cur', '$cod_est', '$year', '$periodo')");
    echo $insertCalificacionQuery ? "<p>Calificación añadida correctamente.</p>" : "<p>Error al añadir la calificación.</p>";
    echo pg_last_error();
}

// Proceso para eliminar una calificación
if (isset($_POST['deleteCalificacion'])) {
    $cod_est = $_POST['cod_est'];

    $deleteCalificacionQuery = pg_query("DELETE FROM calificaciones WHERE nota = '$nota_id' AND cod_est = '$cod_est' AND year = '$year' AND cod_cur = '$cod_cur' AND periodo = '$periodo'");
    echo $deleteCalificacionQuery ? "<p>Calificación eliminada correctamente.</p>" : "<p>Error al eliminar la calificación.</p>";
}

// Consulta para obtener las calificaciones con los filtros solicitados
$calificacionesQuery = pg_query("
    SELECT c.cod_est, e.nomb_est, c.valor, c.fecha 
    FROM calificaciones c 
    JOIN estudiantes e ON c.cod_est = e.cod_est 
    WHERE c.nota = '$nota_id' 
    AND c.year = '$year' 
    AND c.cod_cur = '$cod_cur' 
    AND c.periodo = '$periodo'
");

if (!$calificacionesQuery) {
    echo "<p>Error en la consulta de calificaciones.</p>";
    exit();
}

// Consulta para obtener el listado de estudiantes inscritos en el curso
$estudiantesQuery = pg_query("SELECT cod_est FROM inscripciones WHERE cod_cur = '$cod_cur' AND periodo = $periodo AND year=$year");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calificaciones</title>
    <link rel="stylesheet" href="styles/styleLista.css">
    <script>
        function toggleAddCalificacionForm() {
            var form = document.getElementById("addCalificacionForm");
            form.style.display = form.style.display === "none" ? "block" : "none";
        }
    </script>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div>REGISTRO Y ACTUALIZACION DE CALIFICACIONES</div>
        <a href="notas.php" style="color: white; text-decoration: none;">Volver a Notas</a>
    </div>

    <h3><?php echo " CURSO: ".  htmlspecialchars($nomb_cur). " (" . ($periodo == 1 ? "PERIODO I" : "PERIODO II") . " - " . htmlspecialchars($year) . " )". "<br>Calificaciones del corte " . htmlspecialchars($posicion) ;?></h3>

    <table>
        <tr>
            <th>Código de Estudiante</th>
            <th>Nombre del Estudiante</th>
            <th>Valor de la Nota</th>
            <th>Fecha</th>
            <th>Acciones</th>
        </tr>
        <?php
        while ($row = pg_fetch_assoc($calificacionesQuery)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['cod_est']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nomb_est']) . '</td>';
            echo '<td>' . htmlspecialchars($row['valor']) . '</td>';
            echo '<td>' . htmlspecialchars($row['fecha']) . '</td>';
            echo '<td>
                    <form method="POST" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" style="display:inline;">
                        <input type="hidden" name="cod_est" value="' . htmlspecialchars($row['cod_est']) . '">
                        <input type="hidden" name="nota_id" value="' . htmlspecialchars($nota_id) . '">
                        <input type="hidden" name="nomb_cur" value="' . htmlspecialchars($nomb_cur) . '">
                        <input type="hidden" name="periodo" value="' . htmlspecialchars($periodo) . '">
                        <input type="hidden" name="year" value="' . htmlspecialchars($year) . '">
                        <button type="submit" name="deleteCalificacion" class="btn">Eliminar</button>
                    </form>
                  </td>';
            echo '</tr>';
        }
        ?>
    </table>

    <!-- Botón para mostrar el formulario de añadir calificación -->
    <button type="button" onclick="toggleAddCalificacionForm()" class="btn" style="margin-top: 20px;">Añadir Calificación</button>

    <!-- Formulario para añadir calificación -->
    <div id="addCalificacionForm" class="add-calificacion-form" style="display:none; margin-top: 20px;">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="nota_id" value="<?php echo htmlspecialchars($nota_id); ?>">
            <input type="hidden" name="nomb_cur" value="<?php echo htmlspecialchars($nomb_cur); ?>">
            <input type="hidden" name="periodo" value="<?php echo htmlspecialchars($periodo); ?>">
            <input type="hidden" name="year" value="<?php echo htmlspecialchars($year); ?>">

            <label for="cod_est">Código del Estudiante:</label>
            <select name="cod_est" id="cod_est" required>
                <?php
                while ($estudiante = pg_fetch_assoc($estudiantesQuery)) {
                    echo '<option value="' . htmlspecialchars($estudiante['cod_est']) . '">' . htmlspecialchars($estudiante['cod_est']) . '</option>';
                }
                ?>
            </select>
            
            <label for="valor">Valor de la Nota (0-5):</label>
            <input type="number" step="0.1" name="valor" id="valor" min="0" max="5" required>

            <label for="fecha">Fecha (DD/MM/AAAA):</label>
            <input type="date" name="fecha" id="fecha" required>
            
            <button type="submit" name="addCalificacion" class="btn">Añadir Calificación</button>
        </form>
    </div>
    <form method="POST" action="tablaCalificaciones.php">
        <input type="hidden" name="nota_id" value="<?php echo htmlspecialchars($nota_id); ?>">
        <input type="hidden" name="nomb_cur" value="<?php echo htmlspecialchars($nomb_cur); ?>">
        <input type="hidden" name="periodo" value="<?php echo htmlspecialchars($periodo); ?>">
        <input type="hidden" name="year" value="<?php echo htmlspecialchars($year); ?>">
        <button type="submit" class="btn">Ver Tabla de Calificaciones</button>
    </form>

</body>
</html>
