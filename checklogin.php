<?php
include 'classes.php';
include 'sql.php';
session_start();
?>
<?php
	$sql_data = new sql;
	$sql_data->host_db = "localhost";
	$sql_data->user_db = "Ad";
	$sql_data->pass_db = "1234";
	$sql_data->db_name = "Fabrica";
	$conexion = new mysqli($sql_data->host_db, $sql_data->user_db, $sql_data->pass_db, $sql_data->db_name);
if ($conexion->connect_error)
{
	$_SESSION['mensaje'] = die("La conexión falló: " . $conexion->connect_error);
	header('Location: login.html');
}
else
{
	$_SESSION['sql'] = json_encode($sql_data);
	
	$credentials = new credentials($_POST['username'], $_POST['password']);
	$_SESSION['credentials'] = json_encode($credentials);
	$user_info = new user;
	$user_info->dni = $credentials->username;
	$user_info->name = $credentials->username;
	$_SESSION['dni'] = $credentials->username;
	$_SESSION['password'] = $credentials->password;
	$con = checkCredentialsData($credentials, $conexion);
	if ($con->num_rows > 0)
	{
		$_SESSION['loggedin'] = true;
		$_SESSION['start'] = time();
		$_SESSION['expire'] = $_SESSION['start'] + (5 * 60);
		$con = selectEmployeeData($conexion, $credentials);
		//extrae datos personales
		$fila = $con->fetch_array(MYSQLI_ASSOC);
		$user_info->tipo = $fila['tipo'];
		$user_info->comName = $fila['nombre']." ".$fila['apellido1']." ".$fila['apellido2'];
		$user_info->id = $fila['id'];
		$user_info->name = $fila['nombre'];
		$user_info->surname1 = $fila['apellido1'];
		$user_info->surname2 = $fila['apellido2'];
		$_SESSION['user'] =  json_encode($user_info);
		header('Location: menu.php');
	}
	else
	{
		$_SESSION['mensaje'] = '<p class="respuesta">Username o Password estan incorrectos.</p>';
		header('Location: login.php');
	}
}
mysqli_close($conexion);
?>