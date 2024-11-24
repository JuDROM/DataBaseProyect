<?php
session_start();
include('conexionDB.php');

// Obtener las variables de sesión
$nomb_cur = $_SESSION['nomb_cur'] ?? '';
$periodo = $_SESSION['periodo'] ?? '';
$year = $_SESSION['year'] ?? '';

// Obtener el código del curso
$cod_cur_result = pg_query("SELECT cod_cur FROM cursos WHERE nomb_cur = '$nomb_cur'");
$cod_cur = pg_fetch_result($cod_cur_result, 0, 0);
echo $nomb_cur . "  ". $periodo . "  " . $year;
// Validación y proceso de añadir una nueva nota
if (isset($_POST['addNota'])) {
    $posicion = $_POST['posicion'];
    $desc_nota = $_POST['desc_nota'];
    $porcentaje = $_POST['porcentaje'];
    
    // Obtener la suma actual de los porcentajes
    $sumaPorcentajesQuery = pg_query("SELECT COALESCE(SUM(porcentaje), 0) as total_porcentaje FROM notas WHERE cod_cur = '$cod_cur'");
    $sumaPorcentajes = pg_fetch_result($sumaPorcentajesQuery, 0, 'total_porcentaje');
    
    // Verificar que la suma no exceda el 100%
    if (($sumaPorcentajes + $porcentaje) > 100) {
        echo "<p>Error: La suma de los porcentajes no puede exceder el 100%. Suma actual: $sumaPorcentajes%</p>";
    } else {
        $queryConfirm = pg_query("SELECT * FROM cursosemestres WHERE year= $year AND periodo=$periodo AND cod_cur='$cod_cur'");
        if(pg_num_rows($queryConfirm) == 0){
            $queryCreate = pg_query("INSERT INTO cursosemestres(cod_cur,periodo,year) VALUES('$cod_cur', $periodo,$year)");
        }
        $insertNotaQuery = pg_query("INSERT INTO notas (cod_cur,year,periodo, posicion, desc_nota, porcentaje) VALUES ('$cod_cur',$year ,$periodo,'$posicion', '$desc_nota', '$porcentaje')");
        echo $insertNotaQuery ? "Nota añadida correctamente." : "Error al añadir la nota.";
        echo pg_last_error();
    }
    
}

// Proceso para eliminar una nota
if (isset($_POST['eliminarNota'])) {
    $nota_id = $_POST['nota_id'];
    $deleteNotaQuery = pg_query("DELETE FROM notas WHERE nota = '$nota_id'");
    echo $deleteNotaQuery ? "<p>Nota eliminada correctamente.</p>" : "<p>Error al eliminar la nota.</p>";
    $queryConfirm = pg_query("SELECT * FROM notas WHERE cod_cur ='$cod_cur' AND year=$year AND periodo=$periodo");
    if(pg_num_rows($queryConfirm) <= 0){
        $queryDelete = pg_query("DELETE FROM cursosemestres WHERE year= $year AND periodo=$periodo AND nomb_cur='$nomb_cur'");
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Proceso para actualizar una nota
if (isset($_POST['updateNota'])) {
    $nota_id = $_POST['nota_id'];
    $nueva_posicion = $_POST['nueva_posicion'];
    $nueva_desc_nota = $_POST['nueva_desc_nota'];
    $nuevo_porcentaje = $_POST['nuevo_porcentaje'];

    // Obtener la suma de porcentajes excluyendo el porcentaje actual de la nota que se está editando
    $sumaPorcentajesQuery = pg_query("SELECT COALESCE(SUM(porcentaje), 0) as total_porcentaje FROM notas WHERE cod_cur = '$cod_cur' AND nota != '$nota_id'");
    $sumaPorcentajes = pg_fetch_result($sumaPorcentajesQuery, 0, 'total_porcentaje');

    // Validar que la nueva suma no exceda el 100%
    if (($sumaPorcentajes + $nuevo_porcentaje) > 100) {
        echo "<p>Error: La suma de los porcentajes no puede exceder el 100%. Suma actual sin esta nota: $sumaPorcentajes%</p>";
    } else {
        $updateNotaQuery = pg_query("UPDATE notas SET posicion = '$nueva_posicion', desc_nota = '$nueva_desc_nota', porcentaje = '$nuevo_porcentaje' WHERE nota = '$nota_id'");
        echo $updateNotaQuery ? "<p>Nota actualizada correctamente.</p>" : "<p>Error al actualizar la nota.</p>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Consulta para obtener y ordenar las notas por posicion
$notasQuery = pg_query("SELECT nota, posicion, desc_nota, porcentaje FROM notas WHERE cod_cur = '$cod_cur' AND periodo = $periodo AND year = $year ORDER BY posicion ASC");
if (!$notasQuery) {
    echo "<p>Error en la consulta de notas.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notas del Curso</title>
    <link rel="stylesheet" href="styles/styleLista.css">
    <script>
        function toggleEditForm(notaId) {
            var form = document.getElementById("editForm_" + notaId);
            form.style.display = form.style.display === "none" ? "block" : "none";
        }
        function toggleAddNoteForm() {
            var form = document.getElementById("addNoteForm");
            form.style.display = form.style.display === "none" ? "block" : "none";
        }
    </script>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div>Notas del Curso <?php echo htmlspecialchars($nomb_cur); ?></div>
        <a href="listadoEstudiantes.php" style="color: white; text-decoration: none;">Volver a Estudiantes</a>
    </div>

    <h2>Notas de <?php echo htmlspecialchars($nomb_cur) . " (" . ($periodo == 1 ? "Periodo I" : "Periodo II") . " - " . htmlspecialchars($year) . " )"; ?></h2>

    <table>
        <tr>
            <th>Posición</th>
            <th>Descripción de la Nota</th>
            <th>Porcentaje</th>
            <th>Editar</th>
            <th>Eliminar</th>
            <th>Registro</th>
        </tr>
        <?php
        while ($row = pg_fetch_assoc($notasQuery)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['posicion']) . '</td>';
            echo '<td>' . htmlspecialchars($row['desc_nota']) . '</td>';
            echo '<td>' . htmlspecialchars($row['porcentaje']) . '%</td>';
            
            // Botón de editar
            echo '<td>';
            echo '<button type="button" onclick="toggleEditForm(' . htmlspecialchars($row['nota']) . ')" class="btn">Editar</button>';
            echo '<div id="editForm_' . htmlspecialchars($row['nota']) . '" style="display:none; margin-top:10px;">';
            echo '<form method="POST" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">';
            echo '<input type="hidden" name="nota_id" value="' . htmlspecialchars($row['nota']) . '">';
            echo '<label>Nueva Posición:</label>';
            echo '<input type="text" name="nueva_posicion" required>';
            echo '<label>Nueva Descripción:</label>';
            echo '<input type="text" name="nueva_desc_nota" required>';
            echo '<label>Nuevo Porcentaje:</label>';
            echo '<input type="number" name="nuevo_porcentaje" min="1" max="100" required>';
            echo '<button type="submit" name="updateNota" class="btn">Actualizar</button>';
            echo '</form>';
            echo '</div>';
            echo '</td>';

            // Botón de eliminar
            echo '<td>';
            echo '<form method="POST" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" style="display:inline">';
            echo '<input type="hidden" name="nota_id" value="' . htmlspecialchars($row['nota']) . '">';
            echo '<button type="submit" name="eliminarNota" class="btnDelete">Eliminar</button>';
            echo '</form>';
            echo '</td>';

            // Botón de registro
            echo '<td>';
            echo '<form method="POST" action="calificaciones.php" style="display:inline">';
            echo '<input type="hidden" name="nota_id" value="' . htmlspecialchars($row['nota']) . '">';
            echo '<input type="hidden" name="nomb_cur" value="' . htmlspecialchars($nomb_cur) . '">';
            echo '<input type="hidden" name="periodo" value="' . htmlspecialchars($periodo) . '">';
            echo '<input type="hidden" name="year" value="' . htmlspecialchars($year) . '">';
            echo '<button type="submit" class="btn">Registrar</button>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        ?>
    </table>

    <!-- Botón para mostrar el formulario de añadir nota -->
    <button type="button" onclick="toggleAddNoteForm()" class="btn" style="margin-top: 20px;">Añadir Nota</button>

    <!-- Formulario para añadir nota -->
    <div id="addNoteForm" class="add-note-form" style="display:none; margin-top: 20px;">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <label for="posicion">Posición:</label>
            <input type="text" name="posicion" id="posicion" required>
            
            <label for="desc_nota">Descripción de la Nota:</label>
            <input type="text" name="desc_nota" id="desc_nota" required>
            
            <label for="porcentaje">Porcentaje (1-100):</label>
            <input type="number" name="porcentaje" id="porcentaje" min="1" max="100" required>
            
            <button type="submit" name="addNota" class="btn">Añadir Nota</button>
        </form>
    </div>
</body>
</html>