<?php
session_start();
?>
<?php
if (isset($_SESSION['loggedin']) && $_SESSION['tipo'] == 'Admin')
{
		//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	//$conexion = new mysqli($_SESSION['host_db'], $_SESSION['user_db'], $_SESSION['pass_db'], $_SESSION['db_name']);
	$user = json_decode($_POST['user']);
	$id = $user->id;
	//$id = $_POST['id'];
	//borrar empleado que cumpla condicion
	$conexion->query("delete from empleados where id = $id");
	header('Location: listaemp.php');
}
else
{
	$_SESSION['mensaje'] = '<p class="respuesta">Esta página sólo está disponible para administradores</p>';
	header('Location: menu.php');
}
mysqli_close($conexion);
?>