<?php
session_start();
?>
<?php
if (isset($_SESSION['loggedin']))
{
		//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	//$conexion = new mysqli($_SESSION['host_db'], $_SESSION['user_db'], $_SESSION['pass_db'], $_SESSION['db_name']);
	$user = json_decode($_SESSION['user']);
	//$name = $_SESSION['username'];
	$name = $user->name;
	if ($_SESSION['tipo'] == 'Admin')
	{
		$con = $conexion->query("select P.id_part, P.inf_part, P.pieza, E.nombre, E.apellido1, E.apellido2, E.id
		from parte P, Empleados E
		where P.emp_crea=E.id and resuelto='0' and P.nom_tec is null group by P.id_part, P.inf_part, E.nombre, E.id order by P.id_part asc");
		if(mysqli_num_rows($con)>0)
		{
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'
			<table>
				<tr>
					<th colspan="10">Partes abiertos sin atención</th>
				</tr>
			</table><br />
			<table>
				<tr>
					<th>Nº parte</th>
					<th>Empleado</th>
					<th>Información</th>
					<th>Pieza afectada</th>
					<th>--</th>
				</tr>';
			
			//recorrer datos de los partes abiertos sin atender
			while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
			{
				//insercion partes (html)
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<tr>
					<td>'.$fila['id_part'].'</td>
					<td>'.$fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'].'</td>
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
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table><br />';
		}
		$con = $conexion->query("select P.not_tec, P.tec_res, P.id_part, P.inf_part, P.pieza, E.nombre, E.apellido1, E.apellido2, E.id, P.fecha_resolucion, P.hora_resolucion, nom_tec
		from parte P, Empleados E
		where P.emp_crea=E.id and resuelto='0' and nom_tec is not null group by P.id_part, P.inf_part, E.nombre, E.id order by P.id_part asc");
		if(mysqli_num_rows($con)>0)
		{
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'
			<table>
				<tr>
					<th colspan="10">Partes abiertos atendidos</th>
				</tr>
			</table><br />
			<table>
				<tr>
					<th>Nº parte</th>
					<th>Empleado</th>
					<th>Información</th>
					<th>Pieza afectada</th>
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
					<td>'.$fila['not_tec'].'</td>
					<td>'.$fila['nom_tec'].'</td>';				
				if($_SESSION['id'] == $fila['tec_res'])
				{
					$_SESSION['mensaje'] = $_SESSION['mensaje'].'
					<td>
						<form action="modparte.php" method="post">
							<input type="hidden" name="id" value="'.$fila['id_part'].'" />
							<input type="submit" value="Ver parte" />
						</form>
					</td>';
				}
				else
				{
					$_SESSION['mensaje'] = $_SESSION['mensaje'].'<td>--</td>';
				}
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'</tr>';
			}
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table><br />';
		}
		$con = $conexion->query("select P.not_tec, P.id_part, P.inf_part, P.pieza, E.nombre, E.apellido1, E.apellido2, E.id, P.fecha_resolucion, P.hora_resolucion, nom_tec
		from parte P, Empleados E
		where P.emp_crea=E.id and resuelto='1' group by P.id_part, P.inf_part, E.nombre, E.id order by P.id_part asc");
		if(mysqli_num_rows($con)>0)
		{
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'
			<table>
				<tr>
					<th colspan="10">Partes cerrados</th>
				</tr>
			</table><br />
			<table>
				<tr>
					<th>Nº parte</th>
					<th>Empleado</th>
					<th>Información</th>
					<th>Pieza afectada</th>
					<th>Fecha resolución</th>
					<th>Hora resolución</th>
					<th>Notas técnico</th>
					<th>Técnico a cargo</th>
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
				</tr>';
			}
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table><br />';
		}
	}
	elseif ($_SESSION['tipo']=='Tecnico')
	{
		$nomtec = $conexion->query("
		select P.nom_tec
		from parte P, empleados E
		where E.id=P.tec_res and E.dni='$name'
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
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<table class="tabla_tecnico">
					<tr>
						<th colspan=10>Partes sin atender</th>
					</tr>
				</table><br />
				<table>
					<tr>
						<th>Numero de parte</th>
						<th>Nombre de empleado</th>
						<th>Id del empleado</th>
						<th>Información del parte</th>
						<th>Pieza afectada</th>
						<th>--</th>
					</tr>';
			while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
			{
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
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
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table><br/>';
		}
		if(mysqli_num_rows($con3)>0)
		{
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<table class="tabla_tecnico">
					<tr>
						<th colspan=10>Partes abiertos</th>
					</tr>
				</table><br />
				<table>
					<tr>
						<th>Numero de parte</th>
						<th>Nombre de empleado</th>
						<th>Id del empleado</th>
						<th>Información del parte</th>
						<th>Pieza afectada</th>
						<th>Notas</th>
						<th>--</th>
					</tr>';
			//recorrer datos de los partes atendidos (si los hay)
			while($fila = mysqli_fetch_array($con3, MYSQLI_ASSOC))
			{
				//insercion partes atendidos(html)
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
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
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table><br/>';
		}
		if(mysqli_num_rows($con4)>0)
		{
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<table class="tabla_tecnico">
					<tr>
						<th colspan=10>Partes cerrados</th>
					</tr>
				</table><br />
				<table>
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
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<tr>
					<td>'.$fila['id_part'].'</td>
					<td>'.$fila['nombre'].'</td>
					<td>'.$fila['id'].'</td>
					<td>'.$fila['inf_part'].'</td>
					<td>'.$fila['pieza'].'</td>
					<td>'.$fila['not_tec'].'</td>
				</tr>';
			}
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table>';
		}
	}
	else
	{
		//Partes sin atender propios
		$con = $conexion->query("select P.id_part, P.fecha_hora_creacion, P.inf_part, P.pieza
		from parte P, Empleados E
		where E.dni='$name' and E.id=P.emp_crea and P.oculto=0 and P.tec_res is null");
		$table=$table.'</td></tr>';
		if(mysqli_num_rows($con)>0)
		{
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'
			<table>
				<tr>
					<th colspan="10">Partes abiertos</th>
				</tr>
			</table><br />
			<table>
				<tr>
					<th>Nº parte</th>
					<th>Fecha de creación</th>
					<th>Información</th>
					<th>Piezas afectadas</th>
					<th>--</th>
				</tr>';
			while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
			{
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<tr>
					<td>'.$fila['id_part'].'</td>
					<td>'.$fila['fecha_hora_creacion'].'</td>
					<td>'.$fila['inf_part'].'</td>
					<td>'.$fila['pieza'].'</td>
					<td>
						<a href="funciones.php?id_part='.$fila['id_part'].'&funcion=Borrar">Borrar</a>
					</td>
				</tr>';
			}
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table><br />';
		}
		//Partes atendidos propios
		$con = $conexion->query("select P.id_part, P.fecha_hora_creacion, P.inf_part, P.not_tec, P.pieza, P.nom_tec
		from parte P, Empleados E
		where E.dni='$name' and E.id=P.emp_crea and P.oculto=0 and P.tec_res is not null and P.resuelto=0");
		$table=$table.'</td></tr>';
		if(mysqli_num_rows($con)>0)
		{
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'
			<table>
				<tr>
					<th colspan="10">Partes atendidos</th>
				</tr>
			</table><br />
			<table>
				<tr>
					<th>Nº parte</th>
					<th>Fecha de creación</th>
					<th>Información</th>
					<th>Piezas afectadas</th>
					<th>Notas técnico</th>
					<th>Tecnico a cargo</th>
				</tr>';
			while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
			{
				$id=$fila['id_part'];
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<tr>
					<td>'.$fila['id_part'].'</td>
					<td>'.$fila['fecha_hora_creacion'].'</td>
					<td>'.$fila['inf_part'].'</td>
					<td>'.$fila['pieza'].'</td>
					<td>'.$fila['not_tec'].'</td>
					<td>'.$fila['nom_tec'].'</td>			
				</tr>';
			}
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table><br />';
		}
		//Partes cerrados propios
		$con = $conexion->query("select P.id_part, P.fecha_hora_creacion, P.inf_part, P.not_tec, P.pieza, P.nom_tec, P.fecha_resolucion, P.hora_resolucion 
		from parte P, Empleados E
		where E.dni='$name' and E.id=P.emp_crea and P.resuelto=1 and P.oculto=0");
		$table=$table.'</td></tr>';
		if(mysqli_num_rows($con)>0)
		{
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'
			<table>
				<tr>
					<th colspan="10">Partes cerrados</th>
				</tr>
			</table><br />
			<table>
				<tr>
					<th>Nº parte</th>
					<th>Fecha de creación</th>
					<th>Información</th>
					<th>Piezas afectadas</th>
					<th>Notas técnico</th>
					<th>Tecnico a cargo</th>
					<th>Fecha de resolución</th>
					<th>Hora de resolución</th>
					<th>--</th>
				</tr>';
			while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
			{
				$id=$fila['id_part'];
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<tr>
					<td>'.$fila['id_part'].'</td>
					<td>'.$fila['fecha_hora_creacion'].'</td>
					<td>'.$fila['inf_part'].'</td>
					<td>'.$fila['pieza'].'</td>
					<td>'.$fila['not_tec'].'</td>
					<td>'.$fila['nom_tec'].'</td>
					<td>'.$fila['fecha_resolucion'].'</td>
					<td>'.$fila['hora_resolucion'].'</td>
					<td>
						<a href="funciones.php?id_part='.$fila['id_part'].'&funcion=Ocultar">Ocultar</a>
					</td>
				</tr>';
			}
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table><br />';
		}
	}
}

mysqli_close($conexion);
header('Location: menu.php');
?>