<?php
// Conexión a la base de datos
$connection = pg_connect("host=localhost dbname=tu_base_de_datos user=tu_usuario password=tu_contraseña");

// Inicializar variables
$codEstActual = "";
$nombEstActual = "";

// Si se selecciona un estudiante, cargar los datos
if (isset($_POST['buscarEstudiante'])) {
    $codEstActual = $_POST['codEstActual'];
    $query = "SELECT nomb_est FROM estudiantes WHERE cod_est = '$codEstActual'";
    $result = pg_query($connection, $query);

    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        $nombEstActual = $row['nomb_est'];
    } else {
        echo "<p style='color: red;'>No se encontró el estudiante seleccionado.</p>";
    }
}

// Actualizar los datos del estudiante
if (isset($_POST['updateEstudiante'])) {
    $newCodEstAct = $_POST['newCodEstAct'];
    $newNombEstAct = $_POST['newNombEstAct'];
    $codEstActual = $_POST['codEstActual'];

    $queryUpdate = "UPDATE estudiantes SET cod_est = '$newCodEstAct', nomb_est = '$newNombEstAct' WHERE cod_est = '$codEstActual'";
    $updateResult = pg_query($connection, $queryUpdate);

    if ($updateResult) {
        echo "<p style='color: green;'>Estudiante actualizado correctamente.</p>";
    } else {
        echo "<p style='color: red;'>Error al actualizar: " . pg_last_error($connection) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Estudiante</title>
</head>
<body>
    <h4>Actualizar Estudiante</h4>
    <form method="POST" class="selectors-container">
        <label for="codEstActual">Código del estudiante:</label>
        <select name="codEstActual" id="codEstActual" required>
            <option value="">Selecciona un estudiante</option>
            <?php
            // Cargar estudiantes en el dropdown
            $queryEstudiantes2 = pg_query($connection, "SELECT cod_est FROM estudiantes");
            while ($row = pg_fetch_assoc($queryEstudiantes2)) {
                $selected = ($row['cod_est'] === $codEstActual) ? "selected" : "";
                echo "<option value='{$row['cod_est']}' $selected>{$row['cod_est']}</option>";
            }
            ?>
        </select>
        <button type="submit" name="buscarEstudiante" class="btn">Buscar Estudiante</button>
        <br><br>

        <label for="newCodEstAct">Nuevo Código del Estudiante:</label>
        <input type="text" name="newCodEstAct" id="newCodEstAct" value="<?php echo htmlspecialchars($codEstActual); ?>" required>
        <label for="newNombEstAct">Nuevo Nombre del Estudiante:</label>
        <input type="text" name="newNombEstAct" id="newNombEstAct" value="<?php echo htmlspecialchars($nombEstActual); ?>" required>
        <br><br>

        <button type="submit" name="updateEstudiante" class="btn">Actualizar Estudiante</button>
    </form>
</body>
</html>
