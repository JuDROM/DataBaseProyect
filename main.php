<?php
session_start();
include('conexionDB.php');

// Autenticacion
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$query = pg_query("
    SELECT nomb_cur FROM cursos");
$queryDocentes = pg_query("SELECT nomb_doc FROM docentes WHERE nomb_doc <> 'root'");
$queryDocentes2 = pg_query("SELECT nomb_doc FROM docentes WHERE nomb_doc <> 'root'");
$queryDocentesToCursos = pg_query("SELECT nomb_doc FROM docentes WHERE nomb_doc <> 'root'");
$queryDocentesToCursos2 = pg_query("SELECT nomb_doc FROM docentes WHERE nomb_doc <> 'root'");
$queryCursos = pg_query("SELECT nomb_cur FROM cursos");
$queryCursos2 = pg_query("SELECT nomb_cur FROM cursos");
$queryEstudiantes = pg_query('SELECT nomb_est FROM estudiantes');
$queryEstudiantesDelete = pg_query('SELECT cod_est FROM estudiantes');
$queryEstudiantesUpdate = pg_query('SELECT cod_est FROM estudiantes');

$newNombEst = $_POST["newNombEst"];
$fechaActual = date("d/m/Y");

// CRUD DOCENTE 
if (isset($_POST['addDocente'])) {
    $newNombDoc = $_POST['newNombDoc'];
    $newDocClave = $_POST['newDocClave'];
    $insertDocente = pg_query("INSERT INTO docentes(nomb_doc, clave) VALUES ('$newNombDoc', '$newDocClave')");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();}
// Eliminar
if (isset($_POST['deleteDocente'])) {
    $nombreDocente = $_POST['nombreDocente'];
    $deleteDocente = pg_query("DELETE FROM docentes WHERE nomb_doc = '$nombreDocente'");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();}
// Actualizar
if (isset($_POST['updateDocente'])) {
    $docenteActual = $_POST['docenteActual'];
    $newNombDocAct = $_POST['newNombDocAct'];
    $newDocClave = pg_fetch_assoc(pg_query("SELECT clave FROM docentes WHERE nomb_doc= '$docenteActual'"))['clave'];
    $updateDocente = pg_query("UPDATE docentes SET nomb_doc = '$newNombDocAct', clave = '$newDocClave' WHERE nomb_doc = '$docenteActual'");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();}


//CRUD ESTUDIANTES
//Crear
if (isset($_POST['addEstudiante'])) {
    $newCodEst = $_POST['newCodEst'];
    $newNombEst = $_POST['newNombEst'];
    $queryValidarEstudiantee = pg_num_rows(pg_query("SELECT cod_est FROM estudiantes WHERE cod_est='$newCodEst'"));
    $claveEst = $_POST["claveEst"];
    if ($queryValidarEstudiante > 0){
        echo "<script>alert('El codigo \"$newCodEst\" ya existe. Por favor, ingrese un nombre diferente.');</script>";
    }else{ 
        $insertEstudiante = pg_query("INSERT INTO estudiantes(cod_est,nomb_est,clave) VALUES ($newCodEst, '$newNombEst','$claveEst')");//encriptar contraseñas
        echo "<script> alert('Estudiante ingresado con exito');</script>";
        echo pg_last_error();
    }
    
}

// Eliminar
    if (isset($_GET['confirmDelete'])) {
        $confirmDelete = $_GET['confirmDelete'];
        $_SESSION['confirmDelete'] = $confirmDelete;
    }
    if (isset($_SESSION['confirmDelete'])) {
        $confirmDelete = $_SESSION['confirmDelete'];
        $codEst = $_SESSION["codEst"];
        if($confirmDelete == 1){
            $deleteEstudiante = pg_query("DELETE FROM estudiantes WHERE cod_est ='$codEst'");
        }
        $codEst = "";
        unset($_SESSION['confirmDelete']);
        unset($_SESSION['codEst']);
        unset($_POST['confirmDelete']);
        unset($_POST['codEst']);
        $codEstActual = $_POST['codEstActual'];
        echo '<script>window.location.href = "main.php";</script>';
        exit();
    }
        
    if (isset($_POST['saveEstudiante'])) {
        $codEst = $_POST['codEst'];
        $_SESSION["codEst"] = $codEst;
        $nombEst = "";
        $nombEst = pg_fetch_assoc(pg_query("SELECT nomb_est FROM estudiantes WHERE cod_est='$codEst'"))['nomb_est'];
    }
// Actualizar Estudiante
    if (isset($_POST['updateEstudiante'])) {
        $codEstAct = $_POST['codEstActual'];
        $newCodEstAct = $_POST['newCodEstAct'];
        $newNombEstAct = $_POST['newNombEstAct'];
        $updateEst = pg_query("UPDATE estudiantes SET cod_est = '$newCodEstAct', nomb_est = '$newNombEstAct' WHERE cod_est = '$codEstAct'");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

        if (isset($_POST['cargarEstudiante'])) {
            $codEstActual = $_POST['codEstActual'];
            $newNombEstAct= pg_fetch_assoc(pg_query("SELECT nomb_est FROM estudiantes where cod_est = '$codEstActual'"))['nomb_est'];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    
    if (isset($_POST['codEstActual'])) {
        $codEstActual = $_POST['codEstActual'];
        $nombEstActual = pg_fetch_assoc(pg_query("SELECT nomb_est FROM estudiantes WHERE cod_est = '$codEstActual'"))['nomb_est'];
    }

//CRUD CURSOS
//Crear
    if (isset($_POST['addCurso'])) {
        $newNombCur = $_POST["newNombCur"];
        $CnewNombDoc = $_POST["CnewNombDoc"];
        $cursoExistente = pg_query("SELECT nomb_cur FROM cursos WHERE nomb_cur = '$newNombCur'");
        echo pg_num_rows($cursoExistente);
        if (pg_num_rows($cursoExistente) > 0) {
            echo "<script>alert('El curso \"$newNombCur\" ya existe. Por favor, ingrese un nombre diferente.');</script>";
            
        } else {
            $result = pg_query("SELECT cod_doc FROM docentes WHERE nomb_doc = '$CnewNombDoc'");
            if ($row = pg_fetch_assoc($result)) {
                $CnewCodDoc = $row['cod_doc'];
                $insertCurso = pg_query("INSERT INTO cursos(nomb_cur, cod_doc) VALUES ('$newNombCur', '$CnewCodDoc')");
            }
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
// Eliminar
    if (isset($_POST['deleteCurso'])) {
        $nombCur= $_POST['nombCur'];
        $deleteCurso = pg_query("DELETE FROM cursos WHERE nomb_cur = '$nombCur'");
        if($deleteCurso){  
            echo "Actualización exitosa.";
        } else {
        exit();
    }
    }
// Actualizar
if (isset($_POST["cargarCurso"])) {
    $nombCurActual = $_POST['nombCurActual'];
    $CUNombDocActual = pg_fetch_assoc(pg_query(
        "SELECT d.nomb_doc 
         FROM docentes d 
         JOIN cursos c ON c.cod_doc = d.cod_doc 
         WHERE c.nomb_cur = '$nombCurActual'"
    ))['nomb_doc'];
}

// Procesar la actualización del curso
if (isset($_POST['updateCurso'])) {
    $nombCurActual = $_POST['nombCurActual'];
    $newNombCurAct = $_POST['newNombCurAct'];
    $CUnewNombDoc = $_POST['CUnewNombDoc'];

    if (!empty($nombCurActual) && !empty($newNombCurAct) && !empty($CUnewNombDoc)) {
        $CUnewCodDoc = pg_fetch_assoc(pg_query(
            "SELECT cod_doc FROM docentes WHERE nomb_doc = '$CUnewNombDoc'"
        ))['cod_doc'];

        if ($CUnewCodDoc) {
            $updateCurso = pg_query(
                "UPDATE cursos 
                 SET nomb_cur = '$newNombCurAct', cod_doc = '$CUnewCodDoc' 
                 WHERE nomb_cur = '$nombCurActual'"
            );
            if ($updateCurso) {
                echo "Curso actualizado correctamente.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "Error al actualizar curso: " . pg_last_error();
            }
        } else {
            echo "Error: No se encontró el código del docente.";
        }
    } else {
        echo "Por favor, completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro y control</title>
    <link rel="stylesheet" href="styles/coursesStyle.css?ver=<?php echo time(); ?>">
    <script>
        function toggleAddStudentForm() {
            var form = document.getElementById("addStudentForm");
            form.style.display = form.style.display === "none" ? "block" : "none";
        }
    </script>
</head>
<body>

<!-- CODIGO PRINCIPAL-->
<div class="container">
    <!-- Encabezado principal y fecha -->
    <div class="header">  <label for="cod_est_nuevo">Código del Estudiante:</label>
        <span>CONTROL DE </span>
        <span class="date"><?php echo $fechaActual; ?></span>
    </div>
    <div class="base-container">

<!-- MANEJO Y BUSQUEDA DE CURSOS (ARREGLAR)-->

    <div class="form-container">
        <h3 class="form-header">CURSOS EXISTENTES</h3>
        <div class="form-content">
        <form method="POST" action="listadoEstudiantes.php" class="selectors-container" id="form">
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
    <select name="periodo" id="periodo">
        <option value="1">Periodo I</option>
        <option value="2">Periodo II</option>
    </select>

    <label for="year">Año:</label>
    <input type="text" name="year" id="year" placeholder="2024" pattern="[0-9]+" inputmode="numeric" maxlength="4" minlength="4" title="los codigos son unicamente numericos">

    <label for="estudiantes" style="font-weight: bold; display: block; margin-top: 10px;">Estudiantes:</label>
    <button type="submit" name="verListado" class="btn">Ver listado</button>

</form>

<script>
    document.getElementById('form').addEventListener('submit', function (event) {
        const yearField = document.getElementById('year');
        const yearValue = yearField.value.trim();
        const yearRegex = /^\d{4}$/;

        if (!yearRegex.test(yearValue) || yearValue < 1900 || yearValue > new Date().getFullYear() + 10) {
            alert('Por favor, ingrese un año válido (4 dígitos y mayor que 1900).');
            event.preventDefault();
            yearField.focus();
            return;
        }
    });
</script>
        </div></div>

        
<!--DOCENTES -->
        <div class="form-container">
        <h3 class="form-header">GESTIÓN DE DOCENTES</h3>
        <div class="form-content">

<!-- CREAR DOCENTES -->

            <form method="POST" class="selectors-container">
                <h4>Añadir Docente</h4>
                <label for="newNombDoc">Nombre del Docente:</label>
                <input type="text" id="newNombDoc" name="newNombDoc" required pattern="^[A-Za-zÀ-ÿ\u00f1\u00d1\s]+$" title="Ingrese solo letras.">

                <label for="newDocClave">Clave (Contraseña):</label>
                <input type="password" name="newDocClave" id="newDocClave" required >
                
                <button type="submit" name="addDocente" class="btn">Añadir Docente</button>
            </form>

<!-- ELIMINAR DOCENTES -->

            <form method="POST" class="selectors-container">
                <h4>Eliminar Docente</h4>
                <label for="nombreDocente">Nombre del Docente:</label>
                <select name="nombreDocente" id="nombreDocente" required>
                    <option value="">Selecciona un docente</option>
                    <?php
                    while ($row = pg_fetch_assoc($queryDocentes)) {
                        echo '<option value="' . htmlspecialchars($row['nomb_doc']) . '">' . htmlspecialchars($row['nomb_doc']) . '</option>';
                    }
                    ?>
                </select>
                <button type="submit" name="deleteDocente" class="btn">Eliminar Docente</button>
            </form>
            

<!-- ACTUALIZAR DOCENTES -->

            <form method="POST" class="selectors-container">
                <h4>Actualizar Docente</h4>
                <div id="seleccionDocente">
                    <label for="docenteActual">Seleccione Docente:</label>
                    <select name="docenteActual" id="docenteActual" required>
                        <option value="">Selecciona un docente</option>
                        <?php
                        while ($row = pg_fetch_assoc($queryDocentes2)) {
                            echo '<option value="' . htmlspecialchars($row['nomb_doc']) . '">' . htmlspecialchars($row['nomb_doc']) . '</option>';
                        }?>
                    </select>
                    <button type="button" id="cargarDocente" class="btn">Cargar Datos</button>
                </div>
                <div id="actualizarDocente" style="display: none;">
                    <label for="newNombDocAct">Nuevo Nombre del Docente:</label>
                    <input type="text" name="newNombDocAct" id="newNombDocAct" required placeholder="Escribe aquí el nombre" pattern="^[A-Za-zÀ-ÿ\u00f1\u00d1\s]+$">
                    <button type="submit" name="updateDocente" class="btn">Actualizar Docente</button>
                </div>
            </form>
            </div>
    <script>
    document.getElementById('cargarDocente').addEventListener('click', function () {
        const docenteSeleccionado = document.getElementById('docenteActual').value;
        if (docenteSeleccionado) {
            document.getElementById('seleccionDocente').style.display = 'none';
            document.getElementById('actualizarDocente').style.display = 'block';
            document.getElementById('newNombDocAct').value = docenteSeleccionado;
        } else {
            alert('Por favor, selecciona un docente primero.');
        }
    });
    </script>
        </div>



<!--CURSOS -->
        <div class="form-container">
        <h3 class="form-header">CREACIÓN DE CURSOS</h3>
        <div class="form-content">  

<!--CREAR CURSO -->

            <form method="POST" class="selectors-container">
                <h4>Añadir curso</h4>
                <label for="newNombCur">Nombre del Nuevo Curso:</label>
                <input type="text" name="newNombCur" id="newNombCur" required pattern="^[A-Za-zÀ-ÿ\u00f1\u00d1\s]+$">
                
                <label for="CnewNombDoc">Nombre del Docente</label>
                <select name="CnewNombDoc" id="CnewNombDoc" required>
                    <option value="">Selecciona un docente</option>
                    <?php 
                    while ($row = pg_fetch_assoc($queryDocentesToCursos)) {
                        echo '<option value="' . $row['nomb_doc'] . '">' . $row['nomb_doc'] . '</option>';
                    }
                    ?>
                </select>
                <button type="submit" name="addCurso" class="btn">Añadir Curso</button>
            </form>


<!--ELIMINAR CURSO -->

            <form method="POST" class="selectors-container">
                <h4>Eliminar Curso</h4>
                <label for="nombCur">Nombre del Curso:</label>
                <select name="nombCur" id="nombCur" required>
                    <option value="">Selecciona un curso</option>
                    <?php 
                    while ($row = pg_fetch_assoc($queryCursos)) {
                        echo '<option value="' . $row['nomb_cur'] . '">' . $row['nomb_cur'] . '</option>';
                    }
                    ?>
                    
                </select>
                <button type="submit" name="deleteCurso" class="btn">Eliminar Curso</button>
            </form>

<!--ACTUALIZAR CURSO -->

                <form method="POST" class="selectors-container">
                    <h4>Actualizar Curso</h4>
                    <?php 
                    if(!isset($nombCurActual)){
                    echo '<div id="seleccionCurso">';
                        echo '<select name="nombCurActual" id="nombCurActual" required>';
                            echo '<option value="">Selecciona un curso</option>';
                            while ($row = pg_fetch_assoc($queryCursos2)) { 
                                $selected = (isset($nombCurActual) && $nombCurActual == $row['nomb_cur']) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($row['nomb_cur']) . '" ' . $selected . '>' . htmlspecialchars($row['nomb_cur']) . '</option>';
                            }
                        echo '</select>';
                        echo '<button type="submit" name="cargarCurso" class="btn">Cargar Datos</button>';
                    echo '</div>';}
//<!-- INGRESO DE DATOS PARA ACTUALIZACIÓN -->
                    else {
                    echo '<label for="newNombCurAct">Nombre del Nuevo Curso:</label>';
                    echo '<input type="text" name="newNombCurAct" id="newNombCurAct" value="'. htmlspecialchars($nombCurActual ?? '') .'" pattern="^[A-Za-zÀ-ÿ\u00f1\u00d1\s]+$">';
                    
                    echo '<label for="CUnewNombDoc">Nombre del Docente:</label>';
                    echo '<select name="CUnewNombDoc" id="CUnewNombDoc">';
                        echo '<option value="">Selecciona un docente</option>';
                        
                        while ($row = pg_fetch_assoc($queryDocentesToCursos2)) {
                            $selected = (isset($CUNombDocActual) && $CUNombDocActual == $row['nomb_doc']) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($row['nomb_doc']) . '" ' . $selected . '>' . htmlspecialchars($row['nomb_doc']) . '</option>';
                        }
                        
                    echo '</select>';
                    echo '<button type="submit" name="updateCurso" class="btn">Actualizar Curso</button>';
                    }?></form>
        </div></div>

<!-- ESTUDIANTES -->
        <div class="form-container">
        <h3 class="form-header">REGISTRO DE ESTUDIANTES</h3>
        <div class="form-content">

<!--AÑADIR ESTUDIANTES -->

            <form method="POST" class="selectors-container">
                <h4>Añadir Estudiante</h4>
                <label for="newCodEst">Codigo del estudiante:</label>
                <input type="text" name="newCodEst" id="newCodEst" required pattern="[0-9]+" inputmode="numeric" maxlength="8" minlength="8" title="los codigos son unicamente numericos">
                <label for="newNombEst">Nombre del Estudiante:</label>
                <input type="text" name="newNombEst" id="newNombEst" required pattern="^[A-Za-zÀ-ÿ\u00f1\u00d1\s]+$">
                <input type="password" name="claveEst" id="claveEst" required >
                <button type="submit" name="addEstudiante" class="btn">Añadir Estudiante</button>
            </form>

<!--ELIMINAR ESTUDIANTES -->

            <form method="POST" class="selectors-container">
                <h4>Eliminar Estudiante</h4><?php
                if(!isset($nombEst)){
                echo '<label for="codEst">Código del estudiante:</label>';
                echo '<select name="codEst" id="codEst" value="'. (isset($codEst) ? $codEst : "" ).'" required>';
                    echo '<option value="">Selecciona un estudiante</option>';
                    while ($row = pg_fetch_assoc($queryEstudiantesDelete)) { 
                        $selected = isset($_POST['codEst']) && $_POST['codEst'] == $row['cod_est'] ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row['cod_est']) . '" ' . $selected . '>' . htmlspecialchars($row['cod_est']) . '</option>';
                    }
                echo '</select>';
                echo '<button name="saveEstudiante" type="submit" class="btn">Buscar Estudiante</button><br><br>';
                }if ($nombEst) {
                    echo '<label for="nombEst">Nombre del Estudiante:</label> <div class="info" id="mensaje"> ' . $nombEst . '</div>';
                echo '<button onclick="confirmarEliminacion(event)" type="button" class="btn">Eliminar Estudiante</button>';
                }
                ?>
            </form>

<!--ACTUALIZAR ESTUDIANTES -->

            <form method="POST" class="selectors-container">
                <h4>Actualizar Estudiante</h4><?php
                if(!isset($codEstActual)){
                echo '<label for="codEstActual">Codigo del estudiante:</label>';
                echo '<select name="codEstActual" id="codEstActual" value="'.$codEstActual.'" required>';
                   echo '<option value="">Selecciona un estudiante</option>';
                        while ($row = pg_fetch_assoc($queryEstudiantesUpdate)) { 
                        $selected = isset($_POST['codEstActual']) && $_POST['codEstActual'] == $row['cod_est'] ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row['cod_est']) . '" ' . $selected . '>' . htmlspecialchars($row['cod_est']) . '</option>';}
                echo '</select>'; 
                echo '<button type="submit" id="cargarEstudiante" class="btn">Buscar Estudiante</button><br>';
                } if ($codEstActual){
                    echo '<label for="newCodEstAct">Nuevo Codigo del Estudiante:</label>';
                    echo '<input type="text" name="newCodEstAct" id="newCodEstAct"  value="' . htmlspecialchars($codEstActual) . '" pattern="[0-9]+" inputmode="numeric" maxlength="8" minlength="8" title="los codigos son unicamente numericos">';
                    echo '<label for="newNombEstAct">Nuevo Nombre del Estudiante:</label>';
                    echo '<input type="text" name="newNombEstAct" id="newNombEstAct" value="' . htmlspecialchars($nombEstActual) . '" pattern="^[A-Za-zÀ-ÿ\u00f1\u00d1\s]+$"> ';

                    echo '<button type="submit" name="updateEstudiante" class="btn">Actualizar Estudiante</button>';
                    }?>
            </form>
        </div></div>
        

        </div>
    </div>

    <script>
        function confirmarEliminacion(event) {
            var nomb = <?php echo json_encode($nombEst); ?>;

            if (confirm("¿Eliminar " + nomb + "?")) {
                alert("Eliminando " + nomb);
                window.location.href = "main.php?confirmDelete=1";
            } else {
                alert("Operación cancelada");
                window.location.href = "main.php?confirmDelete=0";
            }
        }
        document.addEventListener("DOMContentLoaded", function() {
            const headers = document.querySelectorAll(".form-header");

            headers.forEach((header, index) => {
                const content = header.nextElementSibling;
                content.style.transition = "none";
                content.style.maxHeight = "0";
                content.style.overflow = "hidden";

                const isOpen = localStorage.getItem(`form-header-${index}`) === "true";
                if (isOpen) {
                    content.style.maxHeight = 3000 + "px";
                }
                content.offsetHeight;
                content.style.transition = "max-height 0.3s ease";
                header.addEventListener("click", () => {
                    if (content.style.maxHeight === "0px") {
                        content.style.maxHeight = 3000 + "px";
                        localStorage.setItem(`form-header-${index}`, "true");
                    } else {
                        content.style.maxHeight = "0";
                        localStorage.setItem(`form-header-${index}`, "false");
                    }
                });
            });
        });


        document.addEventListener("DOMContentLoaded", function () {
            const inputs = document.querySelectorAll("input");

            inputs.forEach((input) => {
                toggleEmptyClass(input);
                input.addEventListener("input", () => {
                    toggleEmptyClass(input);
                });
            });

            function toggleEmptyClass(input) {
                if (input.value.trim() === "") {
                    input.classList.add("empty");
                } else {
                    input.classList.remove("empty");
                }
            }
        });
</script>


</body>
</html>