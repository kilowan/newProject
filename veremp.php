<?php
session_start();
include 'functions.php';
if (!isset($_SESSION['loggedin']) && isset($_POST['username']) && isset($_POST['password'])) {
	login($_POST['username'], $_POST['password']);
}

if (isset($_SESSION['loggedin']))
{	
	$user = $_SESSION['user'];
	$user = json_decode($user);
	if(isset($_GET['funcion']))
	{
		$funcion = $_GET['funcion'];
	}
	else if(isset($_POST['funcion']))
	{
		$funcion = $_POST['funcion'];
	}
	else if (isset($_SESSION['funcion']))
	{
		$funcion = $_SESSION['funcion'];
		$SESSION = null;
	}
	else {

		$json = file_get_contents('php://input');
		$obj = json_decode($json);
		$funcion = $obj->funcion;
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