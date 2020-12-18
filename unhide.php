<?php
session_start();
?>
<?php
if (isset($_SESSION['loggedin']))
{
		//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	//$conexion = new mysqli($_SESSION['host_db'], $_SESSION['user_db'], $_SESSION['pass_db'], $_SESSION['db_name']);
	$id = $_POST['id'];
	//ocultar parte que cumpla condicion
	$conexion->query("update parte set oculto=0 where id_part=$id");
}
mysqli_close($conexion);
header('Location: partes_ocultos.php');
?>