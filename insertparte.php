<?php
session_start();
?>
<?php
if (isset($_SESSION['loggedin']))
{
	//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	$user = json_decode($_SESSION['user']);
	$dni = $user->dni;
	$id_emp = $user->id;
	$id_part = $_POST['id_part'];
	$not_tec = $_POST['not_tec'];
	$pieza = $_POST['pieza'];
	$nombre_tecnico = $user->name.' '.$user->surname1.' '.$user->surname2;
	if($pieza == '--')
	{
		$conexion->query("UPDATE parte  set tec_res = $id_emp, nom_tec='$nombre_tecnico' 
		WHERE id_part = $id_part and (tec_res='$id_emp' or tec_res is null) and resuelto=0");
	}
	else
	{
		$conexion->query("UPDATE parte SET pieza = '$pieza', not_tec = '$not_ant', tec_res = $id_emp, nom_tec='$nombre_tecnico' 
		WHERE id_part = $id_part and (tec_res='$id_emp' or tec_res is null) and resuelto=0");
	}
	$conexion->query("INSERT INTO notes (employee, incidence, noteType, noteStr) VALUES ($id_emp, $id_part, $user->tipo, $not_tec)");
	$_SESSION['funcion'] = 'Partes';
	header('Location: veremp.php');	
}
else
{
	header('Location: menu.php');
}
mysqli_close($conexion);
?>
