<?php
session_start();
?>
<?php
$table='';
$table=$table.'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
		"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>Empleado</title>
				<link rel="stylesheet" type="text/css" href="formato.css" media="screen" />
				<meta http-equiv="content-type" content="text/html; charset=utf-8" />
			</head>
		<body>
		<div class="cabecera">
				<div class="mensaje">
					<p>Partes creados</p>
				</div>
				<div class="Logo">
					<a href="logout.php">
						<img class="cierra" src="shutdown.png" alt="Cerrar sesión" />
					</a>
				</div>
		</div>
		<div class="cuerpo">';
if (isset($_SESSION['loggedin']) && $_SESSION['tipo']=='Admin')
{
		//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	//$conexion = new mysqli($_SESSION['host_db'], $_SESSION['user_db'], $_SESSION['pass_db'], $_SESSION['db_name']);
	$user = json_decode($_SESSION['user']);
	$name = $user->name;
	//$name = $_SESSION['username'];
	$con = $conexion->query("select P.id_part, P.inf_part, P.pieza, E.nombre, E.apellido1, E.apellido2, E.id
	from parte P inner join Empleados E
	on P.emp_crea=E.id 
	where resuelto='0' and P.nom_tec is null 
	group by P.id_part, P.inf_part, E.nombre, E.id order by P.id_part asc");
	if(mysqli_num_rows($con)>0)
	{
		$table=$table.'
		<table>
			<tr>
				<th colspan="9">Partes abiertos sin atención</th>
			</tr>
			<tr>
				<th>Numero de parte</th>
				<th>Nombre de empleado</th>
				<th>Id del empleado</th>
				<th>Información del parte</th>
				<th>Pieza afectada</th>
				<th>Fecha resolución</th>
				<th>Hora resolución</th>
				<th>Técnico a cargo</th>
				<th>--</th>
			</tr>';
		
		//recorrer datos de los partes abiertos sin atender
		while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
		{
			//insercion partes (html)
			$table=$table.'
			<tr>
				<td>'.$fila['id_part'].'</td>
				<td>'.$fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'].'</td>
				<td>'.$fila['id'].'</td>
				<td>'.$fila['inf_part'].'</td>
				<td>'.$fila['pieza'].'</td>
				<td>--</td>
				<td>--</td>
				<td>--</td>
				<td>
					<form action="modparte.php" method="post">
						<input type="hidden" name="id" value="'.$fila['id_part'].'" />
						<input type="submit" value="Ver parte" />
					</form>
				</td>
			</tr>';
		}
		$table=$table.'</table><br />';
	}
	$con = $conexion->query("select P.id_part, P.inf_part, P.pieza, E.nombre, E.apellido1, E.apellido2, E.id, P.fecha_resolucion, P.hora_resolucion, nom_tec
	from parte P, Empleados E
	where P.emp_crea=E.id and resuelto='0' and nom_tec is not null group by P.id_part, P.inf_part, E.nombre, E.id order by P.id_part asc");
	if(mysqli_num_rows($con)>0)
	{
		$table=$table.'
		<table>
			<tr>
				<th colspan="9">Partes abiertos atendidos</th>
			</tr>
			<tr>
				<th>Numero de parte</th>
				<th>Nombre de empleado</th>
				<th>Id del empleado</th>
				<th>Información del parte</th>
				<th>Pieza afectada</th>
				<th>Fecha resolución</th>
				<th>Hora resolución</th>
				<th>Técnico a cargo</th>
				<th>--</th>
			</tr>';
		while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
		{
			//insercion partes (html)
			$table=$table.'
			<tr>
				<td>'.$fila['id_part'].'</td>
				<td>'.$fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'].'</td>
				<td>'.$fila['id'].'</td>
				<td>'.$fila['inf_part'].'</td>
				<td>'.$fila['pieza'].'</td>
				<td>--</td>
				<td>--</td>
				<td>'.$fila['nom_tec'].'</td>
				<td>
					<form action="modparte.php" method="post">
						<input type="hidden" name="id" value="'.$fila['id_part'].'" />
						<input type="submit" value="Ver parte" />
					</form>
				</td>
			</tr>';
		}
		$table=$table.'</table><br />';
	}
	$con = $conexion->query("select P.id_part, P.inf_part, P.pieza, E.nombre, E.apellido1, E.apellido2, E.id, P.fecha_resolucion, P.hora_resolucion, nom_tec
	from parte P, Empleados E
	where P.emp_crea=E.id and resuelto='1' group by P.id_part, P.inf_part, E.nombre, E.id order by P.id_part asc");
	if(mysqli_num_rows($con)>0)
	{
		$table=$table.'
				<table>
			<tr>
				<th colspan="9">Partes cerrados</th>
			</tr>
			<tr>
				<th>Numero de parte</th>
				<th>Nombre de empleado</th>
				<th>Id del empleado</th>
				<th>Información del parte</th>
				<th>Pieza afectada</th>
				<th>Fecha resolución</th>
				<th>Hora resolución</th>
				<th>Técnico a cargo</th>
				<th>--</th>
			</tr>';
		while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
		{
			//insercion partes (html)
			$id=$fila['id_part'];
			$table=$table.'
			<tr>
				<td>'.$fila['id_part'].'</td>
				<td>'.$fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'].'</td>
				<td>'.$fila['id'].'</td>
				<td>'.$fila['inf_part'].'</td>
				<td>'.$fila['pieza'].'</td>
				<td>'.$fila['fecha_resolucion'].'</td>
				<td>'.$fila['hora_resolucion'].'</td>
				<td>'.$fila['nom_tec'].'</td>
				<td>--</td>
			</tr>';
		}
		$table=$table.'</table><br />';
	}
}
else
{
	$table=$table.'<div class="respuesta"><p>Esta página solo esta disponible para administradores</p><p><a href=\'login.html\'>Login</a></p></div>';
}
$table=$table.'
		</div>
		<div class="Pie">
			<p>Trabajo realizado por Jose Javier Valero Fuentes y Juan Francisco Navarro Ramiro para el curso de ASIR 2º X</p>
		</div>
	</body>
</html>';
echo $table;
mysqli_close($conexion);
?>