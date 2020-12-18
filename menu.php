<?php session_start(); ?>
<?php
//SQL
function selectOwnParte($conexion, $dni)
{
	//partes no ocultos propios (empleado)
	$con = $conexion->query("SELECT COUNT(*) 
	FROM parte 
	WHERE oculto=0 AND emp_crea = (SELECT id FROM Empleados WHERE dni = '$dni')");
	return mysqli_num_rows($con);
}
function selectParte($conexion)
{
	//Partes sin atender (tecnico)
	$con = $conexion->query("SELECT COUNT(P.id_part)
	FROM parte P INNER JOIN Empleados E 
	ON P.emp_crea=E.id 
	WHERE P.not_tec IS NULL 
	GROUP BY P.id_part, P.inf_part, E.nombre, E.id");
	return mysqli_num_rows($con);
}
function selectPartes($conexion, $nombreCom)
{
	$con = $conexion->query("SELECT COUNT(P.id_part)
	FROM parte P INNER JOIN Empleados E 
	ON P.emp_crea=E.id 
	WHERE P.nom_tec='$nombreCom' 
	GROUP BY P.id_part, P.inf_part, E.nombre, E.id");
	return mysqli_num_rows($con);
}
function selectAllPartes($conexion)
{
	$con = $conexion->query("SELECT COUNT(P.id_part)
	FROM parte P INNER JOIN Empleados E
	ON P.emp_crea=E.id
	GROUP BY P.id_part, P.inf_part, E.nombre, E.id");
	return mysqli_num_rows($con);
}
//OTHER
function links($user, $nums)
{
	$table = "";
	$userEncoded = json_encode($user);
	if($user->tipo != 'Tecnico')
	{
		$table = $table.'<a class="link" href="veremp.php?funcion=Agregar_parte&id_emp='.$user->id.'&dni='.$user->dni.'">Crear parte</a>';
	}
	if($nums[0] > 0 || $nums[1] > 0)
	{
		$table = $table.'&nbsp'.'<a class="link" href="veremp.php?id_emp='.$user->id.'&dni='.$user->dni.'&funcion=Partes">Ver partes</a>';
	}
	if($user->tipo == 'Admin' || $user->tipo == 'Tecnico')
	{
		$table=$table.'&nbsp<a class="link" href="veremp.php?funcion=Estadisticas&id_emp='.$user->id.'&dni='.$user->dni.'">Estadísticas</a>';
		if($user->tipo == 'Admin')
		{
			//boton de borrar empleados
			$table = $table.'
			<a class="link" href="veremp.php?funcion=Lista&id_emp='.$user->id.'&dni='.$user->dni.'">Lista empleados</a>';
		}
	}
	return $table;
}
function structure($user, $conexion)
{
	if($user->tipo != 'Tecnico' && $user->tipo != 'Admin')
	{
		//partes no ocultos propios (empleado)
		$nums[0] = selectOwnParte($conexion, $dni);
		$nums[1] = 0;
	}
	else
	{
		if($user->tipo == 'Tecnico')
		{
			//Partes sin atender (tecnico)
			$nums[0] = selectParte($conexion);
			//Partes atendidos propios (tecnico)
			$nums[1] = selectPartes($conexion, $user->comName);
		}
		else
		{
			//Lista de partes (admin)
			$nums[0] = selectAllPartes($conexion);
			$nums[1] = 0;
		}
	}
	return $nums;
}

if (isset($_SESSION['loggedin']))
{
	//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	$user = json_decode($_SESSION['user']);
	$table = '';
	$nums = structure($user, $conexion);
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
			 <div class="opciones">'.links($user, $nums).'
			<a class="link" href="veremp.php?funcion=Datos_personales&id_emp='.$user->id.'&dni='.$user->dni.'">Datos personales</a>
		</div>
	</div>';
	if (isset($_SESSION['mensaje']))
	{
		$table = $table.'<div class="cuerpo">'.$_SESSION['mensaje'].'</div>';
		$_SESSION['mensaje'] = "";
	}
	$table = $table.'<div class="Pie">
				<p>Trabajo realizado por Jose Javier Valero Fuentes y Juan Francisco Navarro Ramiro para el curso de ASIR 2º X</p>
			</div>
		</body>
	</html>';
}
else
{
	$_SESSION['mensaje'] = '<p class="respuesta">Esta página sólo está disponible para empleados</p>';
	header('Location: login.php'); 
}
echo $table;
mysqli_close($conexion); ?>