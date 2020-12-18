<?php
session_start();
?>
<?php
if (isset($_SESSION['loggedin']) && ($_SESSION['tipo']=='Admin' || $_SESSION['tipo']=='Tecnico'))
{
		//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	//$conexion = new mysqli($_SESSION['host_db'], $_SESSION['user_db'], $_SESSION['pass_db'], $_SESSION['db_name']);
	$user = json_decode($_SESSION['user']);
	$username = $user->username;
	//$username = $_SESSION['username'];
	$piez = $conexion->query("select pieza, count(pieza) as 'numeroP' 
	from parte
	where resuelto=1
	group by pieza");
	$_SESSION['mensaje'] = '<div class="respuesta">';
	while($fila = mysqli_fetch_array($piez, MYSQLI_ASSOC))
	{
		$_SESSION['mensaje'] = $_SESSION['mensaje'].'
		<p>La pieza <strong>'.$fila['pieza'].'</strong> ha sido reportada <strong>'.$fila['numeroP'].'</strong> veces</p>';
	}
	$_SESSION['mensaje'] = $_SESSION['mensaje'].'</div>';
}
else
{
	$_SESSION['mensaje'] = '<p class="respuesta">Esta página sólo esta disponible para técnicos o administradores</p>';
}
mysqli_close($conexion);
header('Location: menu.php');
?>