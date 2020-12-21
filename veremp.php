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
	$permissions = permissions($user);
	
	//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	/*switch ($variable) {
		case 'value':
			# code...
			break;
		
		default:
			# code...
			break;
	}*/
	
	if ($funcion == 'Admin') {
		$id = $_GET['id_emp'];
		$dni = $_GET['dni'];
		$con = selectEmployee($conexion);
		$result = $con->fetch_array(MYSQLI_ASSOC);
		$userA = new user();
		$userA->tipo = $result['tipo'];
		$userA->name = $result['nombre'];
		$userA->surname1 = $result['apellido1'];
		$userA->surname2 = $result['apellido2'];
		$userA->dni = $dni;
		$userA->id = $id;
		$_SESSION['mensaje'] = $_SESSION['mensaje'].personalData($userA);
		$_SESSION['mensaje'] = $_SESSION['mensaje'].showPartes($conexion, $userA);
		$_SESSION['mensaje'] = $_SESSION['mensaje'].showStadistics($conexion, $userA);
		$_SESSION['mensaje'] = $_SESSION['mensaje'].showGlobalStatistics($userA, $conexion);
		$_SESSION['mensaje'] = $_SESSION['mensaje'].reportedPieces($conexion, $userA);
	}	
	//Vista Datos personales
	if($funcion == 'Datos_personales')
	{
		//Mostrar datos personales
		$_SESSION['mensaje'] = $_SESSION['mensaje'].personalData($user);
	}
	if($funcion == 'Ver_parte')
	{
		$id_part = $_GET['id_part'];
		$_SESSION['mensaje'] = $_SESSION['mensaje'].showDetailParteView($conexion, $user, $id_part);
	}
	//Borrar parte no atendido
	if($funcion == 'Borrar_parte')
	{
		$id_part = $_GET['id_part'];
		$id_emp = $user->id;
		deleteParte($conexion, $id_part, $user);
		$funcion = 'Partes';
	}
	//Ocultar parte cerrado
	if($funcion == 'Ocultar_parte')
	{
		$id_part = $_GET['id_part'];
		hideParte($conexion, $user, $id_part);
		$funcion = 'Partes';
	}
	//Mostrar parte oculto
	if($funcion == 'Mostrar_parte')
	{
		$id_part = $_GET['id_part'];
		showHiddenParte($conexion, $id_part);
		$funcion = 'Partes';
	}
	//Vista Partes
	if($funcion == 'Partes')
	{
		if (!isset($_SESSION['mensaje']))
		{
			$_SESSION['mensaje'] = "";
		}
		$_SESSION['mensaje'] = $_SESSION['mensaje'].showPartes($conexion, $user);
	}
	//Vista Agregar parte
	if($funcion == 'Agregar_parte')
	{
		$_SESSION['mensaje'] = addParte($user);
	}
	//Vista Partes ocultos
	if($funcion == 'Ocultos')
	{
		$_SESSION['mensaje'] = $_SESSION['mensaje'].showHiddenPartes($conexion, $user);
	}
	//Vista Editar parte
	if($funcion == 'Editar_parte')
	{
		$_SESSION['mensaje'] = $_SESSION['mensaje'].editParte($conexion);
	}
	//Vista Estadísticas
	if($funcion == 'Estadisticas')
	{
		$_SESSION['mensaje'] = $_SESSION['mensaje'].showStadistics($conexion, $user);
		$_SESSION['mensaje'] = $_SESSION['mensaje'].showGlobalStatistics($user, $conexion);
		$_SESSION['mensaje'] = $_SESSION['mensaje'].reportedPieces($conexion, $user);
	}
	//Vista Lista de empleados
	if($funcion == 'Lista')
	{
		//Lista de empleados
		$_SESSION['mensaje'] = $_SESSION['mensaje'].employeeList($conexion, $user);
	}
	//Vista Agregar empleado
	if ($funcion == 'Agregar_empleado')
	{
		$_SESSION['mensaje'] = $_SESSION['mensaje'].addEmployee($user);
	}
	//Vista Editar empleado
	if($funcion == 'Editar_empleado')
	{
		$_SESSION['mensaje'] = $_SESSION['mensaje'].editEmployee($conexion, $user);
	}
	//Vista Atender parte
	if ($funcion == 'Atender_parte') {
		$_SESSION['mensaje'] = modParte($conexion, $user);
	}
	//Vista Modificar parte
	if($funcion == 'Modificar_parte')
	{
		$_SESSION['mensaje'] = modParte($conexion, $user);
	}
}
header('Location: menu.php');
mysqli_close($conexion);
?>