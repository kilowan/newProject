<?php
session_start();
?>
<?php
if ($conexion->connect_error)
{
	$_SESSION['mensaje'] = die("La conexión falló: " . $conexion->connect_error);
	header('Location: login.html');
}
else
{
	//Declare objects
	class sql
	{
		public $host_db = "";
		public $user_db = "";
		public $pass_db = "";
		public $db_name = "";
	}

	class user
	{
		public $comName = "";
		public $name = "";
		public $surname1 = "";
		public $surname2 = "";
		public $dni = "";
		public $tipo = "";
		public $id = "";
		public $username = "";
		public $password = "";
	}
	$sql_data = new sql;
	$sql_data->host_db = "localhost";
	$sql_data->user_db = "Ad";
	$sql_data->pass_db = "1234";
	$sql_data->db_name = "Fabrica";
	$_SESSION['sql'] = json_encode($sql_data);
	$conexion = new mysqli($sql_data->host_db, $sql_data->user_db, $sql_data->pass_db, $sql_data->db_name);
	$user_info = new user;
	$user_info->dni = $_POST['username'];
	$user_info->name = $_POST['username'];
	$user_info->username = $_POST['username'];
	$user_info->password = MD5($_POST['password']);
	$_SESSION['dni'] = $_POST['username'];
	$_SESSION['password'] = MD5($_POST['password']);
	$username = $_POST['username'];
	$password = $_SESSION['password'];
	$con = $conexion->query("SELECT * 
	FROM Empleados 
	WHERE dni = '$username' and password = '$password'");
	if (mysqli_num_rows($con) > 0)
	{
		$_SESSION['loggedin'] = true;
		$_SESSION['start'] = time();
		$_SESSION['expire'] = $_SESSION['start'] + (5 * 60);
		//extrae datos personales
		$fila = mysqli_fetch_assoc($con);
		$_SESSION['tipo'] = $fila['tipo'];
		$_SESSION['nombreCom'] = $fila['nombre']." ".$fila['apellido1']." ".$fila['apellido2'];
		$_SESSION['id_emp'] = $fila['id'];
		$_SESSION['nombre'] = $fila['nombre'];
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