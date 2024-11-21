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
$queryDocentes = pg_query("SELECT nomb_doc FROM docentes");
$queryDocentes2 = pg_query("SELECT nomb_doc FROM docentes");
$queryDocentesToCursos = pg_query("SELECT nomb_doc FROM docentes WHERE nomb_doc <> 'root'");
$queryDocentesToCursos2 = pg_query("SELECT nomb_doc FROM docentes WHERE nomb_doc <> 'root'");
$queryCursos = pg_query("SELECT nomb_cur FROM cursos");
$queryCursos2 = pg_query("SELECT nomb_cur FROM cursos");
$queryEstudiantes = pg_query('SELECT nomb_est FROM estudiantes');
$queryEstudiantesDelete = pg_query('SELECT cod_est FROM estudiantes');
$queryEstudiantesUpdate = pg_query('SELECT cod_est FROM estudiantes');

$newNombEst = $_POST["newNombEst"];
$codEstActual = "";
$nombEstActual="";
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
    $newDocClave = $_POST['newDocClave'];
    $updateDocente = pg_query("UPDATE docentes SET nomb_doc = '$newNombDocAct', clave = '$newDocClave' WHERE nomb_doc = '$docenteActual'");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();}


//CRUD ESTUDIANTES
//Crear
if (isset($_POST['addEstudiante'])) {
    $newCodEst = $_POST['newCodEst'];
    $newNombEst = $_POST['newNombEst'];
    $queryValidarEstudiantee = pg_num_rows(pg_query("SELECT cod_est FROM estudiantes WHERE cod_est='$newCodEst'"));
    
    if ($queryValidarEstudiante > 0){
        echo "<script>alert('El codigo \"$newCodEst\" ya existe. Por favor, ingrese un nombre diferente.');</script>";
    }else{ 
        $insertEstudiante = pg_query("INSERT INTO estudiantes(cod_est,nomb_est) VALUES ($newCodEst, '$newNombEst')");
        echo "<script> alert('Estudiante ingresado con exito');</script>";
        echo pg_last_error();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
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
        $codEst = $_POST['codEst'];
        $newCodEst = $_POST['newCodEst'];
        $newNombEst = $_POST['newNombEst'];
        $updateEst = pg_query("UPDATE estudiantes SET cod_est = '$newCodEst', nomb_est = '$newNombEst' WHERE cod_est = '$codEst'");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

        if (isset($_POST['cargarEstudiante'])) {
            $codEstActual = $_POST['codEstActual'];
            $newNombEstAct= pg_fetch_assoc(pg_query("SELECT nomb_est FROM estudiantes where cod_est = '$codEstActual'"))['nomb_est'];
        }
    
    if (isset($_POST['codEstActual'])) {
        $codEstActual = $_POST['codEstActual'];
        $nombEstActual = pg_fetch_assoc(pg_query("SELECT nomb_est FROM estudiantes WHERE cod_est = '$codEstActual'"))['nomb_est'];
    }

//CRUD CURSOS
//Crear
    if (isset($_POST['addCurso'])) {
        $newNombCur = $_POST["newNombCur"];
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
    if (isset($_POST['updateCurso'])) {
        $nombCurActual = $_POST['nombCurActual']; // con el codigo mostrar el nombre
        $newNombCurAct = $_POST['newNombCurAct'];
        $CUnewNombDoc = $_POST['CUnewNombDoc'];

        $CUnewCodDoc = pg_fetch_assoc(pg_query("SELECT cod_doc FROM docentes WHERE nomb_doc = '$CUnewNombDoc'"))["cod_doc"];
        
        $updateCurso = pg_query("UPDATE cursos SET nomb_cur = '$newNombCurAct', cod_doc= '$CUnewCodDoc' WHERE nomb_cur = '$nombCurActual'");
        if($updateCurso){
            echo "se hizo";
        } else {
            echo "no se hizo". pg_last_error();
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

if(isset($_POST["cargarCurso"])){
    $CUNombDocActual= pg_fetch_assoc(pg_query("SELECT nomb_doc FROM cursos where cod_doc = '$codDocActual'"))['nomb_est'];
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
    <input type="text" name="year" id="year" placeholder="2024" pattern="\d+"
     oninput="validateNumber(this);" requied title="Ingrese un año de 4 dígitos entre 1944 y 2104">

    <label for="estudiantes" style="font-weight: bold; display: block; margin-top: 10px;">Estudiantes:</label>
    <button type="submit" name="verListado" class="btn">Ver listado</button>

    <label for="notas" style="font-weight: bold; display: block; margin-top: 10px;">Notas:</label>
    <button type="submit" name="verNotas" class="btn">Ver y editar notas</button>
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
                <input type="password" name="newDocClave" id="newDocClave" required>
                
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
                    <input type="text" name="newNombDocAct" id="newNombDocAct" required placeholder="Escribe aquí el nombre">
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
                <input type="text" name="newNombCur" id="newNombCur" required>
                
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
                <div id="seleccionCurso">
                    <select name="nombCurActual" id="nombCurActual" required>
                        <option value="">Selecciona un curso</option>
                        <?php while ($row = pg_fetch_assoc($queryCursos2)) { 
                            echo '<option value="' . $row['nomb_cur'] . '">' . $row['nomb_cur'] . '</option>';
                        } ?>
                    </select>
                    <button type="submit" name="cargarCurso" class="btn">Cargar Datos</button>
                </div>
            <!--INGRESO DATOS DE ACTUALIZACION -->
                <div id="actualizarCurso" style=<?php if($flag){echo 'display : "" ;';}else{echo 'display: "none";';}; ?>>
                    <label for="newNombCurAct">Nombre del Nuevo Curso:</label>
                    <input type="text" name="newNombCurAct" id="newNombCurAct" required value="<?php echo htmlspecialchars($nombCurActual);?>">
                    
                    <label for="CUnewNombDoc">Nombre del Docente</label>
                    <select name="CUnewNombDoc" id="CUnewNombDoc" required value="<?php echo htmlspecialchars($CUNombDocActual);?>">
                        <option value="">Selecciona un docente</option>
                        <?php
                        while ($row = pg_fetch_assoc($queryDocentesToCursos2)) {
                            echo '<option value="' . $row['nomb_doc'] . '">' . $row['nomb_doc'] . '</option>';
                        }
                        ?>
                    </select>
                    <button type="submit" name="updateCurso" class="btn">Actualizar Curso</button>
                </div>
            </form>
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
                <input type="text" name="newNombEst" id="newNombEst" required>
                
                <button type="submit" name="addEstudiante" class="btn">Añadir Estudiante</button>
            </form>

<!--ELIMINAR ESTUDIANTES -->

            <form method="POST" class="selectors-container">
                <h4>Eliminar Estudiante</h4><br>
                <label for="codEst">Código del estudiante:</label>
                <select name="codEst" id="codEstAct" value="<?php echo isset($codEst) ? $codEst : ""; ?>" required>
                    <option value="">Selecciona un estudiante</option>
                    <?php while ($row = pg_fetch_assoc($queryEstudiantesDelete)) { 
                        $selected = isset($_POST['codEst']) && $_POST['codEst'] == $row['cod_est'] ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row['cod_est']) . '" ' . $selected . '>' . htmlspecialchars($row['cod_est']) . '</option>';
                    } ?>
                </select>
                <button name="saveEstudiante" type="submit" class="btn">Buscar Estudiante</button><br><br>
                <?php if ($nombEst) {
                    echo '<label for="nombEst">Nombre del Estudiante:</label> <div class="info" id="mensaje"> ' . $nombEst . '</div>';
                } ?>

                <!-- Eliminar estudiante -->
                <button onclick="confirmarEliminacion(event)" type="button" class="btn">Eliminar Estudiante</button>
            </form>

<!--ACTUALIZAR ESTUDIANTES -->

            <form method="POST" class="selectors-container">
                <h4>Actualizar Estudiante</h4>

                <label for="codEstActual">Codigo del estudiante:</label>
                <select name="codEstActual" id="codEstActual" value="<?php echo $codEstActual;?>" required>
                    <option value="">Selecciona un estudiante</option>
                        <?php while ($row = pg_fetch_assoc($queryEstudiantesUpdate)) { 
                        $selected = isset($_POST['codEstActual']) && $_POST['codEstActual'] == $row['cod_est'] ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row['cod_est']) . '" ' . $selected . '>' . htmlspecialchars($row['cod_est']) . '</option>';} ?>
                </select> 
                <button type="submit" id="cargarEstudiante" class="btn">Buscar Estudiante</button><br><br>
                <label for="newCodEstAct">Nuevo Codigo del Estudiante:</label>
                <input type="text" name="newCodEstAct" id="newCodEstAct"  value="<?php echo htmlspecialchars($codEstActual);?>" pattern="[0-9]+" inputmode="numeric" maxlength="8" minlength="8" title="los codigos son unicamente numericos">
                <label for="newNombEstAct">Nuevo Nombre del Estudiante:</label>
                <input type="text" name="newNombEstAct" id="newNombEstAct" value="<?php echo htmlspecialchars($nombEstActual); ?>">

                <button type="submit" name="updateEstudiante" class="btn">Actualizar Estudiante</button>
            </form>
        </div></div>
        

        </div>
    </div>

    <script>
        function confirmarEliminacion(event) {
            // Obtener el nombre del estudiante desde PHP
            var nomb = <?php echo json_encode($nombEst); ?>;

            if (confirm("¿Eliminar " + nomb + "?")) {
                // Redirigir con confirmación de eliminación
                alert("Eliminando " + nomb);
                window.location.href = "main.php?confirmDelete=1";
            } else {
                // Redirigir con cancelación
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
                // Verifica el estado inicial
                toggleEmptyClass(input);

                // Escucha eventos de entrada para verificar cambios
                input.addEventListener("input", () => {
                    toggleEmptyClass(input);
                });
            });

            function toggleEmptyClass(input) {
                if (input.value.trim() === "") {
                    input.classList.add("empty"); // Campo vacío
                } else {
                    input.classList.remove("empty"); // Campo con valor
                }
            }
        });
</script>


</body>
</html>