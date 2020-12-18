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
if (isset($_SESSION['loggedin']) && $_SESSION['tipo']=='Tecnico')
{
	//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	//$conexion = new mysqli($_SESSION['host_db'], $_SESSION['user_db'], $_SESSION['pass_db'], $_SESSION['db_name']);
	$user = json_decode($_SESSION['user']);
	$name = $user->name;
	//$name = $_SESSION['username'];
	$nomtec = $conexion->query("
	select P.nom_tec
	from parte P inner join empleados E
	on E.id=P.tec_res
	where E.dni='$name'
	group by P.nom_tec");
	$con = $conexion->query("
	select P.id_part, P.inf_part, P.pieza, E.nombre, E.id 
	from parte P, Empleados E 
	where P.emp_crea=E.id and resuelto='0' and P.nom_tec is null
	group by P.id_part, P.inf_part, E.nombre, E.id 
	order by P.id_part asc");
	while($fila = mysqli_fetch_array($nomtec, MYSQLI_ASSOC))
	{
		$nombretec = $fila['nom_tec'];
	}
	$con3 = $conexion->query("
	select P.id_part, P.inf_part, P.pieza, E.nombre, E.id, P.not_tec
	from parte P, Empleados E 
	where P.emp_crea=E.id and resuelto='0' and P.nom_tec='$nombretec'
	group by P.id_part, P.inf_part, E.nombre, E.id, P.not_tec
	order by P.id_part asc");
	$con4 = $conexion->query("
	select P.id_part, P.inf_part, P.pieza, E.nombre, E.id, P.not_tec
	from parte P, Empleados E 
	where P.emp_crea=E.id and resuelto='1' and P.nom_tec='$nombretec'
	group by P.id_part, P.inf_part, E.nombre, E.id, P.not_tec
	order by P.id_part asc");
	//recorrer datos de los partes sin atender (si los hay)
	if(mysqli_num_rows($con)>0)
	{
		//insercion partes sin atender(html) 
		$table=$table.'
			<table class="tabla_tecnico">
				<tr>
					<th colspan=10>Partes sin atender</th>
				</tr>
				<tr>
					<th>Numero de parte</th>
					<th>Nombre de empleado</th>
					<th>Id del empleado</th>
					<th>Información del parte</th>
					<th>Pieza afectada</th>
				</tr>';
		while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
		{
			$table=$table.'
			<tr>
				<td>'.$fila['id_part'].'</td>
				<td>'.$fila['nombre'].'</td>
				<td>'.$fila['id'].'</td>
				<td>'.$fila['inf_part'].'</td>
				<td>'.$fila['pieza'].'</td>
				<td>
					<form action="modparte.php" method="post">
						<input type="hidden" name="id" value="'.$fila['id_part'].'" />
						<input type="submit" value="Ver parte" />
					</form>
				</td>
			</tr>';
		}
		$table=$table.'</table><br/>';
	}
	if(mysqli_num_rows($con3)>0)
	{
		$table=$table.'
			<table class="tabla_tecnico">
				<tr>
					<th colspan=10>Partes abiertos</th>
				</tr>
				<tr>
					<th>Numero de parte</th>
					<th>Nombre de empleado</th>
					<th>Id del empleado</th>
					<th>Información del parte</th>
					<th>Pieza afectada</th>
					<th>Notas</th>
				</tr>';
		//recorrer datos de los partes atendidos (si los hay)
		while($fila = mysqli_fetch_array($con3, MYSQLI_ASSOC))
		{
			//insercion partes atendidos(html)
			$table=$table.'
			<tr>
				<td>'.$fila['id_part'].'</td>
				<td>'.$fila['nombre'].'</td>
				<td>'.$fila['id'].'</td>
				<td>'.$fila['inf_part'].'</td>
				<td>'.$fila['pieza'].'</td>
				<td>'.$fila['not_tec'].'</td>
				<td>
					<form action="modparte.php" method="post">
						<input type="hidden" name="id" value="'.$fila['id_part'].'" />
						<input type="submit" value="Ver parte" />
					</form>
				</td>
			</tr>';
		}
		$table=$table.'</table><br/>';
	}
	if(mysqli_num_rows($con4)>0)
	{
		$table=$table.'
			<table class="tabla_tecnico">
				<tr>
					<th colspan=10>Partes cerrados</th>
				</tr>
				<tr>
					<th>Numero de parte</th>
					<th>Nombre de empleado</th>
					<th>Id del empleado</th>
					<th>Información del parte</th>
					<th>Pieza afectada</th>
					<th>Notas</th>
				</tr>';
		//recorrer datos de los partes cerrados (si los hay)
		while($fila = mysqli_fetch_array($con4, MYSQLI_ASSOC))
		{
			//insercion partes cerrados(html)
			$table=$table.'
			<tr>
				<td>'.$fila['id_part'].'</td>
				<td>'.$fila['nombre'].'</td>
				<td>'.$fila['id'].'</td>
				<td>'.$fila['inf_part'].'</td>
				<td>'.$fila['pieza'].'</td>
				<td>'.$fila['not_tec'].'</td>
			</tr>';
		}
		$table=$table.'</table>';
	}
}
else
{
	$table=$table.'<div class="respuesta"><p>Esta página solo esta disponible para técnicos</p><p><a href=\'login.html\'>Login</a></p></div>';
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