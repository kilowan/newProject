<?php
session_start();
?>
<?php
if (isset($_SESSION['loggedin'])&& $_SESSION['tipo'] == 'Admin')
{
		//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	//$conexion = new mysqli($_SESSION['host_db'], $_SESSION['user_db'], $_SESSION['pass_db'], $_SESSION['db_name']);
	$id = $_POST['id'];
	$dni = $_POST['dni'];
	$nombre = $_POST['nombre'];
	$apellido1 = $_POST['apellido1'];
	$apellido2 = $_POST['apellido2'];
	$tipo = $_POST['tipo'];
	if($_POST['dni'] == "" || $_POST['nombre'] == "" || $_POST['apellido1'] == "" || $_POST['tipo'] == "")
	{
	}
	else
	{
		$consulta = "UPDATE Empleados set dni = '$dni', nombre = '$nombre', apellido1 = '$apellido1', apellido2 = '$apellido2', tipo = '$tipo' WHERE id = $id";
		$result2 = $conexion->query($consulta);
	}
	header('Location: listaemp.php');
}
else
{
	$_SESSION['mensaje'] = '<p class="respuesta">Esta página solo esta disponible para administradores</p>';
	header('Location: menu.php');
}
mysqli_close($conexion);
?>