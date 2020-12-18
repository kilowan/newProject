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
	$dni = $user->dni;
	$id_emp = $user->id;
	//$dni = $_SESSION['dni'];
	$id_part = $_POST['id_part'];
	$not_tec = $_POST['not_tec'];
	$pieza = $_POST['pieza'];
	//$id_emp = $_SESSION['id_emp'];
	//extrae id y nombre de empleado
	$con = $conexion->query("SELECT nombre, apellido1, apellido2 FROM Empleados where dni='$dni'");
	$fila = mysqli_fetch_assoc($con);
	$nombre_tecnico = $fila['nombre']." ".$fila['apellido1']." ".$fila['apellido2'];	
	//extrae notas anteriores
	$con = $conexion->query("SELECT not_tec FROM parte WHERE id_part=$id_part");
	$fila = mysqli_fetch_assoc($con);
	if (mysqli_num_rows($con) > 0)
	{
		$not_ant = $fila['not_tec'].'<br />Actualización:<br />'.$not_tec;
	}
	else
	{
		$not_ant = $not_tec;
	}
	if($pieza == '--')
	{
		$con = $conexion->query("UPDATE parte  set not_tec = '$not_ant', tec_res = $id_emp, nom_tec='$nombre_tecnico' 
		WHERE id_part = $id_part and (tec_res='$id_emp' or tec_res is null) and resuelto=0");
	}
	else
	{
		$con = $conexion->query("UPDATE parte SET pieza = '$pieza', not_tec = '$not_ant', tec_res = $id_emp, nom_tec='$nombre_tecnico' 
		WHERE id_part = $id_part and (tec_res='$id_emp' or tec_res is null) and resuelto=0");
	}
	$_SESSION['funcion'] = 'Partes';
	header('Location: veremp.php');	
}
else
{
	header('Location: menu.php');
}
mysqli_close($conexion);
?>
