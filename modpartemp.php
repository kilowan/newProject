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
					<p>Partes creados por ti</p>
				</div>
				<div class="Logo">
					<a href="logout.php">
						<img class="cierra" src="shutdown.png" alt="Cerrar sesión" />
					</a>
				</div>
		</div>
		<div class="cuerpo">';
if (isset($_SESSION['loggedin']))
{
		//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	//$conexion = new mysqli($_SESSION['host_db'], $_SESSION['user_db'], $_SESSION['pass_db'], $_SESSION['db_name']);
	$user = json_decode($_SESSION['user']);
	$name = $user->name;
	//$name = $_SESSION['username'];
	$con = $conexion->query("select P.resuelto, P.id_part, P.fecha_hora_creacion, P.inf_part, P.fecha_resolucion, P.hora_resolucion, P.not_tec, P.pieza
	from parte P inner join Empleados E
	on E.id=P.emp_crea 
	where E.dni='$name' and and P.oculto=0");
	
	$table=$table.'
	<table>
		<tr>
			<th>Numero de parte</th>
			<th>Fecha de creación</th>
			<th>Información del parte</th>
			<th>Piezas afectadas</th>
			<th>Notas del técnico</th>
			<th>Tecnico a cargo</th>
			<th>Fecha de resolución</th>
			<th>Hora de resolución</th></tr>';
	while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
	{
		$id=$fila['id_part'];
		$table=$table.'
		<tr>
			<td>'.$fila['id_part'].'</td>
			<td>'.$fila['fecha_hora_creacion'].'</td>
			<td>'.$fila['inf_part'].'</td>
			<td>'.$fila['pieza'].'</td>
			<td>'.$fila['not_tec'].'</td>
			<td>';
		$nom = $conexion->query("select E.nombre, E.apellido1, E.apellido2 from Empleados E, parte P where E.id=P.tec_res and id_part=$id");
		if(mysqli_num_rows($nom)>0)
		{
			while($row = mysqli_fetch_array($nom, MYSQLI_ASSOC))
			{
				$name='';
				$name=$name.$row['nombre'].' '.$row['apellido1'].' '.$row['apellido2'];
			}
			$table=$table.$name.'</td>
			<td>'.$fila['fecha_resolucion'].'</td>
			<td>'.$fila['hora_resolucion'];
		}
		else
		{
			$table=$table.'--</td>
			<td>--</td>
			<td>--';
		}
		if($fila['resuelto']==0 && $fila['not_tec']=='')
	 	{
			$table=$table.'</td>
			<td>
				<form action="deleteparte.php" method="post">
					<input type="hidden" name="id" value="'.$id.'">
					<input type="submit" value="Eliminar">
				</form>';
		}
		elseif($fila['resuelto']==1)
		{
			$table=$table.'</td>
			<td>
				<form action="hideparte.php" method="post">
					<input type="hidden" name="id" value="'.$id.'">
					<input type="submit" value="Ocultar">
				</form>';
		}
		$table=$table.'</td></tr>';
	}
	$table=$table.'</table>';
}
else
{
	$table=$table.'<div class="respuesta"><p>Esta página solo esta disponible para empleados</p><p><a href=\'login.html\'>Login</a></p></div>';
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