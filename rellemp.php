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
	$user = json_decode($_POST['user']);
	$username = $user->username;
	$dni = $user->dni;
	$nom_emp = $user->name;
	$apellido1 = $user->surname1;
	$apellido2 = $user->surname2;
	$tipo = $user->tipo;
	//$username = $_SESSION['username'];
	//$dni = $_POST['dni'];
	//$nom_emp = $_POST['nombre'];
	//$apellido1 = $_POST['apellido1'];
	//$apellido2 = $_POST['apellido2'];
	//$pass = $_POST['pass'];
	//$tipo = $_POST['tipo'];	
	$consulta = "insert into Empleados (dni, password, nombre, apellido1, apellido2, tipo)
	values ('$dni', MD5('$pass'), '$nom_emp', '$apellido1', '$apellido2' ,'$tipo')";
	$result2 = $conexion->query($consulta);
	header('Location: listaemp.php');
}
else
{
	header('Location: menu.php');
}
mysqli_close($conexion);
?>