<?php
session_start();
?>
<?php
include 'functions.php';
if (isset($_SESSION['loggedin']))
{	
	$user = $_SESSION['user'];
	$user = json_decode($user);
	$dni = $user->dni;
	$id_emp = $user->id;
	$tipo = $user->tipo;
	$nombreCom = $user->comName;
	$nombre = $user->name;
	$apellido1 = $user->surname1;
	$apellido2 = $user->surname2;
	$table = "";
	if(isset($_GET['funcion']))
	{
		$funcion = $_GET['funcion'];
	}
	else
	{
		$funcion = $_SESSION['funcion'];
		$SESSION = null;
	}
	if (!isset($_SESSION['mensaje']))
	{
		$_SESSION['mensaje'] = "";
	}
	
	//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	$_SESSION['mensaje'] = $_SESSION['mensaje'].mainStruture($funcion, $conexion, $user);
}
header('Location: menu.php');
mysqli_close($conexion);
?>