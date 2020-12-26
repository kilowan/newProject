<?php session_start(); ?>
<?php
include 'functions.php';
if (isset($_SESSION['loggedin']))
{
	//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	$user = json_decode($_SESSION['user']);
	$table = '';
	$nums = structure($user, $conexion);
	if (!isset($_SESSION['mensaje']))
	{
		$_SESSION['mensaje'] = "";
	}
	$table = $table.'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
		"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>Empleado</title>
				<LINK REL=StyleSheet HREF="formato.css" TYPE="text/css" MEDIA=screen>
				<meta http-equiv="content-type" content="text/html; charset=utf-8" />
			</head>
		<body>
			<div class="cabecera">
				<p class="mensaje">Bienvenido'.' '.$user->comName.'<p>
				<div class="Logo">
					<a href="funciones.php?funcion=Logout">
						<img class="cierra" src="shutdown.png" alt="Cerrar sesión" />
					</a>
				</div>
			 <div class="opciones">'.links($user, $nums).'</div>
			 <div class="cuerpo">'.$_SESSION['mensaje'].'</div>
			 <div class="Pie">
				<p>Trabajo realizado por Jose Javier Valero Fuentes y Juan Francisco Navarro Ramiro para el curso de ASIR 2º X</p>
			</div>
		</body>
	</html>';
	$_SESSION['mensaje'] = "";
}
else
{
	$_SESSION['mensaje'] = '<p class="respuesta">Esta página sólo está disponible para empleados</p>';
	header('Location: login.php'); 
}
echo $table;
mysqli_close($conexion); ?>