<?php 
 $conn_string = "dbname=proyectofinal host=localhost port=5432 user=postgres password=postgres"; 
 $dbconn = pg_connect($conn_string) or die('Error de conexion: ' . pg_last_error()); 
?>

