<?php
session_start();
?>
<?php
if (isset($_SESSION['loggedin'])&& $_SESSION['tipo'] == 'Admin')
{
		//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	//$conexion = new mysqli($_SESSION['host_db'], $_SESSION['user_db'], $_SESSION['pass_db'], $_SESSION['db_name']);
	//extraer datos de empleados
	$con = $conexion->query("select id, dni, nombre, apellido1, apellido2, tipo
	from Empleados
	where tipo not in ('Admin')");
	$user = json_decode($_SESSION['user']);
	$name = $user->name;
	//$name = $_SESSION['username'];
	//comprobación partes existentes no cerrados
	if(mysqli_num_rows($con)>0)		
	{
		//insercion titulos tabla (html)
		$_SESSION['mensaje'] = '
		<br /><table><tr><th>Lista de empleados</th></tr></table><br />
			<table>
				<tr>
					<th>ID de empleado</th>
					<th>DNI del empleado</th>
					<th>Nombre</th>
					<th>Primer apellido</th>
					<th>Segundo apellido</th>
					<th>Tipo de empleado</th>
					<th colspan="3">--</th>
				</tr>';
		//recorrer datos de los partes
		while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
		{
			//insercion partes (html) 
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<tr>
					<td><a href="veremp.php?id='.$fila['id'].'&dni='.$fila['dni'].'&funcion=Admin&tipo=Admin">'.$fila['id'].'</a></td>
					<td>'.$fila['dni'].'</td>
					<td>'.$fila['nombre'].'</td>
					<td>'.$fila['apellido1'].'</td>
					<td>'.$fila['apellido2'].'</td>
					<td>'.$fila['tipo'].'</td>
					<td><a href="funciones.php?id='.$fila['id'].'&funcion=Delete">Borrar</a></td>
					<td><a href="editemp.php?id='.$fila['id'].'">Editar</a></td>
				</tr>';
		}
		$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<tr>
				<td colspan="8">
					<a href="agregaremp.php">Agregar nuevo</a>
				</tr>
			</table>';
	}
}
else
{
	$_SESSION['mensaje'] = '<p class="respuesta">Esta página solo esta disponible para administradores</p>';
}
mysqli_close($conexion);
header('Location: menu.php');
?>