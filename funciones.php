<?php
session_start();
?>
<?php
if (isset($_SESSION['loggedin']))
{
	//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	if($_SESSION['user'] == "")
	{
		$user = json_decode($_POST['user']);
	}
	else
	{
		$user = json_decode($_SESSION['user']);
	}
	
	//$conexion = new mysqli($_SESSION['host_db'], $_SESSION['user_db'], $_SESSION['pass_db'], $_SESSION['db_name']);
	//$tipo = $_SESSION['tipo'];
	$tipo = $user->tipo;
	if(isset($_GET['funcion']))
	{
		$funcion = $_GET['funcion'];
	}
	else
	{
		$funcion = $_POST['funcion'];
	}
	//Ocultar parte cerrado
	if($funcion == 'Ocultar_parte')
	{
		$id_part = $_GET['id_part'];
		$conexion->query("update parte set oculto=1 where id_part=$id_part");
		$_SESSION['funcion'] = 'Partes';
		header('Location: veremp.php');
	}
	//Mostrar parte oculto
	if($funcion == 'Mostrar_parte')
	{
		$id_part = $_GET['id_part'];
		$conexion->query("update parte set oculto=0 where id_part=$id_part");
		$_SESSION['funcion'] = 'Ocultos';
		header('Location: veremp.php');
	}
	//Borrar parte no atendido
	if($funcion == 'Borrar_parte')
	{
		$id_part = $_GET['id_part'];
		$id_emp = $user->id;
		//$id_emp = $_SESSION['id_emp'];
		$conexion->query("delete from parte where id_part=$id_part and emp_crea=$id_emp and tec_res is null");
		$_SESSION['funcion'] = 'Partes';
		header('Location: veremp.php');
	}
	if($funcion == 'Actualizar_parte')
	{
		$id_part = $_POST['id_part'];
		$inf_part = $_POST['inf_part'];
		$id_emp = $user->id;
		//$id_emp = $_SESSION['id_emp'];
		if($inf_part != '')
		{
			$conexion->query("UPDATE parte SET inf_part='$inf_part' 
			WHERE id_part=$id_part AND emp_crea=$id_emp");
			$_SESSION['funcion'] = 'Partes';
			header('Location: veremp.php');
		}
		else
		{
			$_SESSION['mensaje'] = '<p class="respuesta">Inserción no satisfactoria</p>';
		}
		header('Location: menu.php');
	}
	//Crear parte
	if($funcion == 'Crear_parte')
	{
		$tipo = $user->tipo;
		//$tipo = $_SESSION['tipo'];
		$pieza = $_POST['pieza'];
		$descripcion = $_POST['descripcion'];
		$id_emp = $user->id;
		//$id_emp = $_SESSION['id_emp'];
		if($pieza != "--")
		{	
			$conexion->query("insert into parte (emp_crea, resuelto, inf_part , pieza)
			values ($id_emp, 0, '$descripcion', '$pieza')");
		}
		else
		{
			$conexion->query("insert into parte (emp_crea, resuelto, inf_part)
			values ($id_emp, 0, '$descripcion')");
		}
		$_SESSION['funcion'] = 'Partes';
		header('Location: veremp.php');
	}
	//Borrar empleado
	if($tipo == 'Admin')
	{
		if($funcion == 'Borrar_empleado')
		{
			$id_emp = $_GET['id_emp'];
			$conexion->query("delete from empleados where id = $id_emp");
			$_SESSION['funcion'] = 'Lista';
			header('Location: veremp.php');
		}
		if($funcion == 'Actualizar_empleado')
		{
			function check($input)
			{
				if($input == "")
				{
					return false;
				}
				else
				{
					return true;
				}
			}
			$user = json_decode($_POST['user']);
			$nombre = $user->name;
			$apellido1 = $user->surname1;
			$apellido2 = $user->surname2;
			$tipo = $user->tipo;
			$dni = $user->dni;
			$id_emp = $user->id;
			//$nombre = $_POST['nombre'];
			//$apellido1 = $_POST['apellido1'];
			//$apellido2 = $_POST['apellido2'];
			//$tipo = $_POST['tipo'];			
			//$dni = $_POST['dni'];
			//$id_emp = $_POST['id_emp'];
			$bool = check($dni);
			$bool = check($nombre);
			$bool = check($apellido1);
			$bool = check($apellido2);
			if($bool == true)
			{
				$conexion->query("UPDATE Empleados set dni = '$dni', nombre = '$nombre', apellido1 = '$apellido1', apellido2 = '$apellido2', tipo = '$tipo' 
				WHERE id = $id_emp");
			}
			$_SESSION['funcion'] = 'Lista';
			header('Location: veremp.php');
		}
		if($funcion == 'Crear_empleado')
		{
			$user = $_POST['user'];
			$nombre = $user->name;
			$apellido1 = $user->surname1;
			$apellido2 = $user->surname2;
			$tipo = $user->tipo;
			$dni = $user->dni;
			$pass = $user->password;
			//$nombre = $_POST['nombre'];
			//$apellido1 = $_POST['apellido1'];
			//$apellido2 = $_POST['apellido2'];
			//$tipo = $_POST['tipo'];		
			//$dni = $_POST['dni'];
			//$pass = $_POST['pass'];
			$conexion->query("insert into Empleados (dni, password, nombre, apellido1, apellido2, tipo)
			values ('$dni', MD5('$pass'), '$nombre', '$apellido1', '$apellido2' ,'$tipo')");
			$_SESSION['funcion'] = 'Lista';
			header('Location: veremp.php');		
		}		
	}
	//Cerrar sesión
	if($funcion == 'Logout')
	{
		$_SESSION = array();
		if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
		}
		session_destroy();
		header("Location: login.php");
	}
}
else
{
	header("Location: login.php");
}
mysqli_close($conexion);
?>