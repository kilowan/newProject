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
	$id=$_POST['id'];
	$user = json_decode($_SESSION['user']);
	$id_emp = $user->id;
	//$id_emp = $_SESSION['id'];
	//borrar parte que cumpla condicion
	$conexion->query("delete from parte where id_part=$id and emp_crea=$id_emp and tec_res is null");
}
mysqli_close($conexion);
header('Location: partes.php');
?>