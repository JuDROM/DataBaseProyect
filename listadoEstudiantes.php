<?php
session_start();
include('conexionDB.php');

// Guarda los valores en la sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['nomb_cur'] = $_POST['nomb_cur'] ?? $_SESSION['nomb_cur'] ?? '';
    $_SESSION['periodo'] = $_POST['periodo'] ?? $_SESSION['periodo'] ?? '';
    $_SESSION['year'] = $_POST['year'] ?? $_SESSION['year'] ?? '';
}
$user = $_SESSION['username'] ?? '';
$nomb_cur = $_SESSION['nomb_cur'] ?? '';
$cod_cur = pg_fetch_assoc(pg_query("SELECT cod_cur FROM cursos WHERE nomb_cur ='$nombCur'"))['cod_cur'];
$periodo = $_POST['periodo'] ?? '';
$year = $_SESSION['year'] ?? '';

echo "Curso: $nomb_cur, Periodo: $periodo, Año: $year <br>";

// Eliminar la inscripción si se presiona el botón eliminar
if (isset($_POST['eliminar'])) {
    $cod_est_eliminar = $_POST['cod_est_eliminar'];
    $cod_cur = pg_fetch_result(pg_query("SELECT cod_cur FROM cursos WHERE nomb_cur = '$nomb_cur'"), 0, 0);
    
    $deleteQuery = pg_query("DELETE FROM inscripciones WHERE cod_cur = '$cod_cur' AND cod_est = '$cod_est_eliminar' AND year = $year AND periodo = $periodo");
    
    if (!$deleteQuery) {
        echo "<p>Error al eliminar la inscripción.</p>";
    } else {
        echo "<p>Inscripción eliminada correctamente.</p>";
    }
    $queryConfirm = pg_query("SELECT * FROM notas WHERE cod_cur ='$cod_cur' AND year=$year AND periodo=$periodo");
    if(pg_num_rows($queryConfirm) < 0){
        $queryDelete = pg_query("DELETE FROM cursosemestres WHERE year= $year AND periodo=$periodo AND nomb_cur='$nomb_cur'");
    }
}

// Añadir la inscripción si se presiona el botón añadir
if (isset($_POST['addEstudiante'])) {
    $cod_est_nuevo = $_POST['cod_est_nuevo'];
    $cod_cur = pg_fetch_result(pg_query("SELECT cod_cur FROM cursos WHERE nomb_cur = '$nomb_cur'"), 0, 0);
    $queryConfirm = pg_query("SELECT * FROM cursosemestres WHERE year= $year AND periodo=$periodo AND cod_cur='$cod_cur'");
    if(pg_num_rows($queryConfirm) == 0){
        $queryCreate = pg_query("INSERT INTO cursosemestres(cod_cur,periodo,year) VALUES('$cod_cur', $periodo,$year)");
    }
    $insertQuery = pg_query("INSERT INTO inscripciones (cod_cur, cod_est, year, periodo) VALUES ('$cod_cur', '$cod_est_nuevo', $year, $periodo)");
    echo pg_last_error();
    if (!$insertQuery) {
        echo "<p>Error al añadir la inscripción.</p>";
    } else {
        echo "<p>Inscripción añadida correctamente.</p>";
    }
    
}

// Consulta para obtener los estudiantes inscritos
$studentQuery = pg_query("
    SELECT e.cod_est, e.nomb_est 
    FROM inscripciones i 
    JOIN estudiantes e ON i.cod_est = e.cod_est 
    WHERE i.cod_cur IN (
        SELECT cod_cur 
        FROM cursos 
        WHERE nomb_cur = '$nomb_cur'
    ) 
    AND i.year = $year 
    AND i.periodo = $periodo
");

if (!$studentQuery) {
    echo "<p>Error en la consulta de estudiantes.</p>";
    
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Estudiantes</title>
    <link rel="stylesheet" href="styles/styleLista.css">
    <script>
        function toggleAddStudentForm() {
            var form = document.getElementById("addStudentForm");
            form.style.display = form.style.display === "none" ? "block" : "none";
        }
    </script>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div>Gestión de Inscripciones</div>
        <?php
        $href = ($user === 'root') ? 'main.php' : 'courses.php';
        ?>
        <a href=<?php echo $href?> style="color: white; text-decoration: none;">Inicio</a>
    </div>

    <h2>Estudiantes inscritos en <?php echo htmlspecialchars($nomb_cur) . " (" . ($periodo == 1 ? "Periodo I" : "Periodo II") . " - " . htmlspecialchars($year) . " )"; ?> </h2>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <input type="hidden" name="nomb_cur" value="<?php echo htmlspecialchars($nomb_cur); ?>">
        <input type="hidden" name="periodo" value="<?php echo htmlspecialchars($periodo); ?>">
        <input type="hidden" name="year" value="<?php echo htmlspecialchars($year); ?>">

        <table>
            <tr>
                <th colspan="3"><button type="button" onclick="toggleAddStudentForm()" class="btn"><img src="img/imgSave.png" width="50" height="50"></button></th>
            </tr>
            <tr>
                <th>Código de Estudiante</th>
                <th>Nombre del Estudiante</th>
                <th>Eliminar Estudiante</th>
            </tr>
            <?php
            while ($row = pg_fetch_assoc($studentQuery)) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['cod_est']) . '</td>';
                echo '<td>' . htmlspecialchars($row['nomb_est']) . '</td>';
                echo '<td>';
                echo '<form method="POST" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" style="display:inline">';
                echo '<input type="hidden" name="cod_est_eliminar" value="' . htmlspecialchars($row['cod_est']) . '">';
                echo '<input type="hidden" name="nomb_cur" value="' . htmlspecialchars($nomb_cur) . '">';
                echo '<input type="hidden" name="periodo" value="' . htmlspecialchars($periodo) . '">';
                echo '<input type="hidden" name="year" value="' . htmlspecialchars($year) . '">';
                echo '<button type="submit" name="eliminar" class="btnDelete"><img src="img/delete.png" width="25" height="25"></button>';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
            ?>
        </table>
    </form>

    <!-- Formulario para añadir estudiante -->
    <div id="addStudentForm" class="add-student-form">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <label for="cod_est_nuevo">Código del Estudiante:</label>
            <input type="text" name="cod_est_nuevo" id="cod_est_nuevo" required> <!-- NECESITO HACER LO DE CONFIRMAR INGRESO-->
            <input type="hidden" name="nomb_cur" value="<?php echo htmlspecialchars($nomb_cur); ?>">
            <input type="hidden" name="periodo" value="<?php echo htmlspecialchars($periodo); ?>">
            <input type="hidden" name="year" value="<?php echo htmlspecialchars($year); ?>">
            <button type="submit" name="addEstudiante" class="btn">Añadir</button>
        </form>
    </div>

    <!-- Botón para ir a notas.php -->
    <form method="GET" action="notas.php">
        <button type="submit" class="btn" style="margin-top: 20px;">Ver Notas</button>
    </form>

</body>
</html>
