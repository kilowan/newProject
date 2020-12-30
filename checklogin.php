<?php
include 'functions.php';
session_start();
?>
<?php
	login();
	mysqli_close($conexion);
?>