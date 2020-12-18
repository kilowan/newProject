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
					<p>Tiempo medio de resolución de partes</p>
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
	//$host_db = "localhost";
	//$user_db = "Ad";
	//$pass_db = "1234";
	//$db_name = "Fabrica";
	//$conexion = new mysqli($host_db, $user_db, $pass_db, $db_name);
	//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	$con = $conexion->query("SELECT ROUND(AVG(Tiempo),0) AS 'tiempo_medio', nom_tec FROM Tiempo_resolucion
	GROUP BY nom_tec");
	$name = $_SESSION['username'];
	if(mysqli_num_rows($con)>0)
	{
		function tiempo($n, $i)
		{
			if($i==0 && $n==86400)
			{
				$time="1 día";
			}
			elseif($i==0 && $n>=86400 && $n<=86460)
			{
				$segundos=$n%60;
				$time="1 día y $segundos segundo(s)";
			}
			elseif($i==0 && $n>=90000 && $n<=90060)
			{
				$segundos=$n%60;
				$time="1 día, 1 hora y $segundos segundo(s)";
			}
			elseif($i==0 && $n==3600)
			{
				$time="1 hora";
			}	
			elseif($i==0 && $n>=3600 && $n<=3660)
			{
				$segundos=$n%60;
				$time="1 hora y $segundos segundo(s)";
			}
			elseif($i==0 && $n<=60)
			{
				$time="$n segundos";
			}
			elseif($i==0 && $n>60)
			{
				$segundos=$n%60;
				$n=intdiv($n,60);
				$i++;
			}
			if($i==1 && $n>60)
			{
				$minutos=$n%60;
				$n=intdiv($n,60);
				$i++;	
			}
			elseif($i==1 && $n<=60)
			{
				$time="$n minuto(s) y $segundos segundo(s)";
			}
			if($i==2 && $n>24)
			{
				$horas=$n%24;
				$n=intdiv($n,24);
				$i++;
			}
			elseif($i==2 && $n<24)
			{
				$time="$n hora(s), $minutos minuto(s) y $segundos segundo(s)";
			}
			if($i==3 && $n>365)
			{
				$dias=$n%365;
				$n=intdiv($n,365);				
				$time="$n año(s), $dias día(s), $horas hora(s), $minutos minuto(s) y $segundos segundo(s)";
				$i++;
			}
			elseif($i==3 && $n<=365)
			{
				$time="$n día(s), $horas hora(s), $minutos minuto(s) y $segundos segundo(s)";
			}
			return $time;
		}
		$table=$table.'
		<table class="tabla_tiempo">
			<tr>
				<th>Tiempo medio</th>
				<th>Nombre de empleado</th>
			</tr>';
		//recorrer datos de los partes
		while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
		{
			//insercion partes (html)
			$table=$table.'
			<tr>
				<td>'.tiempo($fila['tiempo_medio'], 0).'</td>
				<td>'.$fila['nom_tec'].'</td>
			</tr>';
		}
		$table=$table.'</table>';
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