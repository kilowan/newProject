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
	$id = $_GET['id'];
	$con = $conexion->query("select dni, nombre, apellido1, apellido2, tipo
	from Empleados
	where tipo not in ('Admin') and id=$id");
	$fila = mysqli_fetch_array($con, MYSQLI_ASSOC);
	$nombreCom = $fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'];
	$_SESSION['mensaje'] = $_SESSION['mensaje'].'
	<br /><table>
		<tr>
			<th>Editar empleado</th>
		</tr>
	</table><br />
	<form action="actuemp.php" method="post">
		<input type="hidden" name="id" value="'.$id.'" />
		<table>
			<tr>
				<th>DNI</th>
				<th>Nombre</th>
				<th>Primer apellido</th>
				<th>Segundo apellido</th>
				<th>Tipo</th>
				<th>--</th>
			</tr>
			<tr>
				<td><input type="text" name="dni" value="'.$fila['dni'].'" required /></td>
				<td><input type="text" name="nombre" value="'.$fila['nombre'].'" required /></td>
				<td><input type="text" name="apellido1" value="'.$fila['apellido1'].'" required /></td>
				<td><input type="text" name="apellido2" value="'.$fila['apellido2'].'" /></td>
				<td><input type="text" name="tipo" value="'.$fila['tipo'].'" required /></td>
				<td><input type="submit" value="Guardar" /></td>
			</tr>
		</table>
	</form>';
}
else
{
	$_SESSION['mensaje'] = '<p class="respuesta">Esta página solo esta disponible para administradores</p>';
}
mysqli_close($conexion);
header('Location: menu.php');
?>