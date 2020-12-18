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
	$user = json_decode($_SESSION['user']);
	//$username = $_SESSION['username'];
	$username = $user->username;
	$num_id = $user->id;
	$pieza = $_POST['pieza'];
	$descripcion = $_POST['descripcion'];
	//$id = $conexion->query("SELECT id FROM Empleados WHERE dni = '$username'");
	//$fila = mysqli_fetch_assoc($id);
	//$num_id = $fila['id'];
	if($pieza != "--")
	{	
		$consulta = "insert into parte (emp_crea, resuelto, inf_part , pieza)
		values ($num_id, 0, '$descripcion', '$pieza')";
		$result2 = $conexion->query($consulta);
	}
	else
	{
		$consulta = "insert into parte (emp_crea, resuelto, inf_part , pieza)
		values ($num_id, 0, '$descripcion', 'nose')";
		$result2 = $conexion->query($consulta);
	}
	$_SESSION['mensaje'] = '<p class="respuesta">Gracias por notificarnos el error, se le asignará un técnico lo antes posible</p>';
	header('Location: menu.php');
}
mysqli_close($conexion);
header('Location: partes.php');
?>
