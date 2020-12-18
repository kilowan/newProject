<?php
session_start();
?>
<?php
if (isset($_SESSION['loggedin']) && $_SESSION['tipo'] == 'Admin')
{
	$name=$_SESSION['username'];
	//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	//$conexion = new mysqli($_SESSION['host_db'], $_SESSION['user_db'], $_SESSION['pass_db'], $_SESSION['db_name']);
	$con = $conexion->query("select ROUND(AVG(Tiempo),0) AS 'tiempo_medio', count(nom_tec) as 'cantidad_partes', nom_tec
	from Tiempo_resolucion
	group by nom_tec
	order by count(nom_tec) desc");
	function tiempo($n, $i)
	{
		if($i == 0 && $n == 86400)
		{
			$_SESSION['time'] = "1 día";
		}
		elseif($i == 0 && $n >= 86400 && $n <= 86460)
		{
			$segundos = $n%60;
			$_SESSION['time'] = "1 día y $segundos segundo(s)";
		}
		elseif($i == 0 && $n >= 90000 && $n <= 90060)
		{
			$segundos=$n%60;
			$_SESSION['time'] = "1 día, 1 hora y $segundos segundo(s)";
		}
		elseif($i == 0 && $n == 3600)
		{
			$_SESSION['time'] = "1 hora";
		}	
		elseif($i == 0 && $n >= 3600 && $n <= 3660)
		{
			$segundos = $n%60;
			$_SESSION['time'] = "1 hora y $segundos segundo(s)";
		}
		elseif($i == 0 && $n <= 60)
		{
			$_SESSION['time'] = "$n segundos";
		}
		elseif($i == 0 && $n>60)
		{
			$segundos = $n%60;
			$n = intdiv($n,60);
			$i++;
		}
		if($i == 1 && $n > 60)
		{
			$minutos=$n%60;
			$n=intdiv($n,60);
			$i++;	
		}
		elseif($i==1 && $n<=60)
		{
			$_SESSION['time']="$n minuto(s) y $segundos segundo(s)";
		}
		if($i == 2 && $n>24)
		{
			$horas = $n%24;
			$n = intdiv($n,24);
			$i++;
		}
		elseif($i == 2 && $n<24)
		{
			$_SESSION['time'] = "$n hora(s), $minutos minuto(s) y $segundos segundo(s)";
		}
		if($i == 3 && $n>365)
		{
			$dias = $n%365;
			$n = intdiv($n,365);				
			$_SESSION['time'] = "$n año(s), $dias día(s), $horas hora(s), $minutos minuto(s) y $segundos segundo(s)";
			$i++;
		}
		elseif($i == 3 && $n <= 365)
		{
			$_SESSION['time'] = "$n día(s), $horas hora(s), $minutos minuto(s) y $segundos segundo(s)";
		}
		return $_SESSION['time'];
	}
	$_SESSION['mensaje'] = '
	<table class="tabla_tiempo">
		<tr>
			<th>Tiempo medio</th>
			<th>Cantidad de partes</th>
			<th>Nombre de empleado</th>
		</tr>';
	//recorrer datos de los partes
	while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
	{
		//insercion partes (html)
		$_SESSION['mensaje'] = $_SESSION['mensaje'].'<tr><td>';
		$_SESSION['mensaje'] = $_SESSION['mensaje'].tiempo($fila['tiempo_medio'], 0);
		$_SESSION['mensaje'] = $_SESSION['mensaje'].'</td><td>';
		$_SESSION['mensaje'] = $_SESSION['mensaje'].$fila['cantidad_partes'];
		$_SESSION['mensaje'] = $_SESSION['mensaje'].'</td><td>';
		$_SESSION['mensaje'] = $_SESSION['mensaje'].$fila['nom_tec'];
		$_SESSION['mensaje'] = $_SESSION['mensaje'].'</td></tr>';
	}
	$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table>';
}
else
{
	$_SESSION['mensaje'] = '<div class="respuesta"><p>Esta página solo esta disponible para administradores</p>';
}
mysqli_close($conexion);
header('Location: menu.php');
?>	