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
					<p>Ranking de resolución de partes</p>
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
	/*$host_db = "localhost";
	$user_db = "Ad";
	$pass_db = "1234";
	$db_name = "Fabrica";
	$conexion = new mysqli($host_db, $user_db, $pass_db, $db_name);*/
	$con = $conexion->query("select count(P.id_part) as 'cantidad_partes', E.nombre, E.apellido1, E.apellido2
	from parte P inner join Empleados E
	on E.id=P.tec_res
	group by E.nombre, E.apellido1, E.apellido2
	order by count(P.id_part) desc");
	$user = json_decode($_SESSION['user']);
	//$name = $_SESSION['username'];
	$name = $user->name;
	if(mysqli_num_rows($con)>0)
	{
		$table=$table.'
		<table class="tabla_ranking">
			<tr>
				<th>Partes resueltos</th>
				<th>Nombre de empleado</th>
			</tr>';
		//recorrer datos de los partes
		while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
		{
			//insercion partes (html)
			$table=$table.'
			<tr>
				<td>'.$fila['cantidad_partes'].'</td>
				<td>'.$fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'].'</td>
			</tr>';
		}
		$table=$table.'</table>';
	}
}
else
{
	$table=$table.'<div class="respuesta"><p>Esta página solo esta disponible para a</p><p><a href=\'login.html\'>Login</a></p></div>';
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
