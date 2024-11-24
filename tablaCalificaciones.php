<?php
session_start();
include('conexionDB.php');

// Obtener datos necesarios de la sesión o POST
$nomb_cur = $_SESSION['nomb_cur'] ?? '';
$periodo = $_SESSION['periodo'] ?? '';
$year = $_SESSION['year'] ?? '';

// Consulta para obtener las notas del curso
$queryNotas = "SELECT posicion, desc_nota, porcentaje, nota FROM notas WHERE cod_cur = (SELECT cod_cur FROM cursos WHERE nomb_cur = '$nomb_cur') ORDER BY posicion";
$resultNotas = pg_query($queryNotas);
$notas = pg_fetch_all($resultNotas);

// Obtener el código del curso
$queryCodCur = "SELECT cod_cur FROM cursos WHERE nomb_cur = '$nomb_cur'";
$resultCodCur = pg_query($queryCodCur);
$cod_cur = pg_fetch_result($resultCodCur, 0, 0);

// Consulta para obtener estudiantes inscritos y sus calificaciones
$queryCalificaciones = "
    SELECT i.cod_est, e.nomb_est, c.valor, c.nota
    FROM inscripciones i
    JOIN estudiantes e ON i.cod_est = e.cod_est
    LEFT JOIN calificaciones c ON i.cod_est = c.cod_est 
        AND c.cod_cur = '$cod_cur'
        AND c.year = '$year'
        AND c.periodo = '$periodo'
    WHERE i.cod_cur = '$cod_cur'
    ORDER BY i.cod_est, c.nota
";
$resultCalificaciones = pg_query($queryCalificaciones);
$calificaciones = pg_fetch_all($resultCalificaciones);
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calificaciones - <?php echo htmlspecialchars($nomb_cur); ?></title>
    <link rel="stylesheet" href="styles/styleLista.css">
    <style>
        .table-header { background-color: #4CAF50; color: white; text-align: center; }
        .table-header th { padding: 10px; }
        .btn { margin-top: 20px; }
    </style>
    <script>
        // Generar vista imprimible en PDF
        function generatePrintableView() {
            // Clonar el contenido de la tabla y el título
            var content = document.getElementById("printContent").innerHTML;
            var printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Calificaciones - <?php echo htmlspecialchars($nomb_cur); ?></title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        h2 { text-align: center; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
                        .table-header { background-color: #4CAF50; color: white; }
                    </style>
                </head>
                <body>
                    <h2>Calificaciones - <?php echo htmlspecialchars($nomb_cur); ?></h2>
                    ` + content + `
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
    </script>
</head>
<body>
    <div class="header">
        <h1>REGISTRO DE NOTAS</h1>
        <h3><?php echo date("d/m/Y"); ?></h3>
        <h3>CURSO: <?php echo htmlspecialchars($nomb_cur); ?></h3>
        <a href="listadoEstudiantes.php" style="color: white;">Volver</a>
    </div>
    <button class="btn" onclick="generatePrintableView()">Generar PDF</button>

    <div id="printContent">
        <table id="calificacionesTable" border="1" style="width: 100%; margin-top: 20px;">
            <tr class="table-header">
                <th>Código</th>
                <th>Nombre</th>
                <?php
                foreach ($notas as $nota) {
                    echo "<th>" . htmlspecialchars($nota['desc_nota']) . " (" . htmlspecialchars($nota['porcentaje']) . "%)</th>";
                }
                ?>
                <th>Definitiva</th>
            </tr>
            <?php
            $students = [];
            
            // Organizar calificaciones por estudiante y nota
            foreach ($calificaciones as $cal) {
                $codEst = $cal['cod_est'];
                $notaId = $cal['nota'];
                $valor = $cal['valor'];

                if (!isset($students[$codEst])) {
                    $students[$codEst] = [
                        'nombre' => $cal['nomb_est'],
                        'notas' => array_fill_keys(array_column($notas, 'nota'), 0),
                        'definitiva' => 0
                    ];
                }

                if ($notaId) {
                    $students[$codEst]['notas'][$notaId] = $valor;
                }
            }

            // Mostrar cada estudiante con sus calificaciones
            foreach ($students as $codEst => $data) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($codEst) . "</td>";
                echo "<td>" . htmlspecialchars($data['nombre']) . "</td>";

                $definitiva = 0;
                foreach ($notas as $nota) {
                    $notaId = $nota['nota'];
                    $porcentaje = $nota['porcentaje'];
                    $valor = $data['notas'][$notaId];

                    echo "<td>" . htmlspecialchars($valor) . "</td>";

                    // Calcular la contribución de esta nota a la definitiva
                    $definitiva += ($valor * $porcentaje) / 100;
                }

                echo "<td>" . number_format($definitiva, 1) . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
