<?php
session_start();
?>
<?php
if (isset($_SESSION['loggedin']))
{
	$user = json_decode($_SESSION['user']);
	//$name = $_SESSION['username'];
	$name = $user->name;
	//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	//$conexion = new mysqli($_SESSION['host_db'], $_SESSION['user_db'], $_SESSION['pass_db'], $_SESSION['db_name']);
	$con = $conexion->query("select P.id_part, P.inf_part, P.pieza, E.nombre, E.apellido1, E.apellido2, E.id, P.fecha_resolucion, P.hora_resolucion, nom_tec, not_tec
	from parte P, Empleados E
	where P.emp_crea=E.id and oculto='1' and E.dni='$name' group by P.id_part, P.inf_part, E.nombre, E.id order by P.id_part asc");
	if(mysqli_num_rows($con)>0)
	{
		$_SESSION['mensaje'] = $_SESSION['mensaje'].'
			<table>
				<tr>
					<th colspan="10">Partes ocultos</th>
				</tr>
			</table>
			<table><br />
				<tr>
					<th>Nº parte</th>
					<th>Empleado</th>
					<th>Información</th>
					<th>Pieza afectada</th>
					<th>Fecha resolución</th>
					<th>Hora resolución</th>					
					<th>Notas técnico</th>
					<th>Técnico a cargo</th>
					<th>--</th>
				</tr>';
		while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
		{
			//insercion partes (html)
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'
			<tr>
				<td>'.$fila['id_part'].'</td>
				<td>'.$fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'].'</td>
				<td>'.$fila['inf_part'].'</td>
				<td>'.$fila['pieza'].'</td>
				<td>'.$fila['fecha_resolucion'].'</td>
				<td>'.$fila['hora_resolucion'].'</td>
				<td>'.$fila['not_tec'].'</td>
				<td>'.$fila['nom_tec'].'</td>
				<td>
					<a href="funciones.php?id_part='.$fila['id_part'].'&funcion=Mostrar">Mostrar</a>
				</td>				
			</tr>';
		}
		$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table><br/>';
	}
}
mysqli_close($conexion);
header('Location: menu.php');
?>