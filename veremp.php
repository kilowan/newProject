<?php
session_start();
?>
<?php
if (isset($_SESSION['loggedin']))
{	
	//Funcion tiempo
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
	function selectNewPartes($conexion, $user)
	{
		//Partes sin atender propios
		return $conexion->query("SELECT P.id_part, P.fecha_hora_creacion, P.inf_part, P.pieza
		FROM parte P INNER JOIN Empleados E
		ON AND E.id=P.emp_crea 
		WHERE E.dni='$user->dni' AND E.id=$user->id AND P.tec_res IS NULL");
	}
	function selectOwnPartes($conexion, $user)
	{
		//Partes atendidos propios
		return $conexion->query("SELECT P.id_part, P.fecha_hora_creacion, P.inf_part, P.not_tec, P.pieza, P.nom_tec
		FROM parte P INNER JOIN Empleados E
		ON E.id=P.emp_crea 
		WHERE E.dni='$user->dni' AND E.id=$user->id AND P.tec_res IS NOT NULL AND P.resuelto=0");
	}
	function selectOtherPartes($conexion, $user)
	{
		//Partes atendidos no propios
		return $conexion->query("SELECT P.id_part, P.fecha_hora_creacion, P.inf_part, P.not_tec, P.pieza, P.nom_tec
		FROM parte P INNER JOIN  Empleados E
		ON E.id=P.tec_res
		WHERE E.dni='$user->dni' AND P.tec_res IS NOT NULL AND P.resuelto=0");
	}
	function selectNewOtherPartes($conexion, $user)
	{
		//Partes sin atender no propios
		return $conexion->query("SELECT P.id_part, P.fecha_hora_creacion, P.inf_part, P.pieza, E.nombre, E.apellido1, E.apellido2
		FROM parte P, Empleados E
		WHERE E.dni!='$user->dni' AND E.id=P.emp_crea AND P.tec_res IS NULL");
	}
	function countOldPartes($conexion, $user)
	{
		//Partes cerrados propios
		$con = $conexion->query("SELECT COUNT(P.id_part)
		FROM Empleados E INNER JOIN parte P
		ON E.id=P.emp_crea
		WHERE E.id=$user->id AND E.dni='$user->dni'");
		return mysqli_num_rows($con);
	}
	function selectOldOtherPartes($conexion, $user)
	{
		//Partes cerrados	
		return $conexion->query("SELECT T.tiempo, P.id_part, P.inf_part, P.not_tec, P.fecha_hora_creacion, P.fecha_resolucion, P.hora_resolucion, P.emp_crea
		FROM Empleados E INNER JOIN parte P
		ON E.id=P.tec_res
		INNER JOIN tiempo_resolucion T
		ON T.id_part=P.id_part
		WHERE P.tec_res!=P.emp_crea and E.id=$user->id and E.dni='$user->dni'");
	}
	function selectOldPartes($conexion, $user)
	{
		return $conexion->query("SELECT T.tiempo, P.id_part, P.nom_tec, P.not_tec, P.inf_part, P.pieza, P.fecha_hora_creacion
		FROM Empleados E INNER JOIN parte P
		ON E.id=P.emp_crea
		INNER JOIN tiempo_resolucion T
		ON T.id_part=P.id_part
		WHERE E.id=$user->id AND E.dni='$user->dni' AND P.oculto=0");
	}
	function countHiddenPartes($conexion, $user)
	{
		$con = $conexion->query("SELECT COUNT(*)
		FROM parte 
		WHERE oculto=1 AND emp_crea = $user->id");
		return mysqli_num_rows($con);
	}
	function selectHiddenPartes($conexion, $user)
	{
		return $conexion->query("SELECT P.id_part, P.inf_part, P.pieza, E.nombre, E.apellido1, E.apellido2, E.id, P.fecha_resolucion, P.hora_resolucion, nom_tec, not_tec
		FROM parte P INNER JOIN Empleados E
		ON P.emp_crea=E.id 
		WHERE oculto='1' AND E.dni='$dni' 
		GROUP BY P.id_part, P.inf_part, E.nombre, E.id 
		ORDER BY P.id_part ASC");
	}
	function selectParte($conexion, $id_part)
	{
		return $conexion->query("SELECT *
		FROM parte
		WHERE id_part=$id_part AND tec_res is null");
	}
	function selectFullDataParte($conexion, $id_part)
	{
		return $conexion->query("SELECT E.nombre, E.apellido1, E.apellido2, E.id, P.not_tec, P.inf_part, P.pieza, P.fecha_hora_creacion 
		FROM Empleados E INNER JOIN parte P 
		ON E.id=P.emp_crea 
		WHERE id_part=$id_part");
	}
	function selectEmpleado($conexion, $emp_crea)
	{
		return $conexion->query("SELECT E.nombre, E.apellido1, E.apellido2
		FROM Empleados E INNER JOIN parte P
		ON P.emp_crea=E.id 
		WHERE P.emp_crea=$emp_crea");
	}
	function selectEmpleadoNoAdmin($conexion)
	{
		$id_emp = $_GET['id_emp'];
		return $conexion->query("select dni, nombre, apellido1, apellido2, tipo
		from Empleados
		where tipo not in ('Admin') and id=$id_emp");
	}
	function selectEmpleados($conexion)
	{
		//Lista de empleados no administradores.
		return $conexion->query("select id, dni, nombre, apellido1, apellido2, tipo
		from Empleados
		where tipo not in ('Admin')");
	}
	function tiempoMedio($conexion, $user)
	{
		return $conexion->query("SELECT ROUND(AVG(Tiempo),0) AS 'tiempo_medio', count(nom_tec) AS 'cantidad_partes', nom_tec
		FROM Tiempo_resolucion
		WHERE tec_res=$user->id
		GROUP BY nom_tec
		ORDER BY ROUND(AVG(Tiempo),0) DESC");
	}
	function personalData($user)
	{
		return '
		<br /><table>
			<tr>
				<th>Datos personales</th>
			</tr>
		</table><br />
		<table>
			<tr>
				<th>ID</th>
				<th>DNI</th>
				<th>Nombre</th>
				<th>Primer apellido</th>
				<th>Segundo apellido</th>
				<th>Tipo</th>
			</tr>
			<tr>
				<td>'.$user->id.'</td>
				<td>'.$user->dni.'</td>
				<td>'.$user->name.'</td>
				<td>'.$user->surname1.'</td>
				<td>'.$user->surname2.'</td>
				<td>'.$user->tipo.'</td>
			</tr>
		</table><br />';
	}
	/*function incidencesData()
	{

	}*/
	//Datos previos
	function check($GET, $SESSION)
	{
		$data = null;
		if(isset($GET))
		{
			$data = $GET;
		}
		else
		{
			$data = $SESSION;
			$SESSION = null;
		}
		return $data;
	}
	function htmlMaker($data, $tag)
	{
		return '<'.$tag.'>'.$data.'</'.$tag.'>';
	}
	$user = $_SESSION['user'];
	$user = json_decode($user);
	$dni = $user->dni;
	$id_emp = $user->id;
	$tipo = $user->tipo;
	$nombreCom = $user->comName;
	$nombre = $user->name;
	$apellido1 = $user->surname1;
	$apellido2 = $user->surname2;
	$funcion = check($_GET['funcion'], $_SESSION['funcion']);
	
	//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	//Vista Datos personales
	if($funcion == 'Datos_personales' || $funcion == 'Admin')
	{
		//Mostrar datos personales
		$_SESSION['mensaje'] = $_SESSION['mensaje'].personalData($user);
	}
	if($tipo != 'Tecnico')
	{
		//Vista Partes
		if($funcion == 'Partes'  || $funcion == 'Admin')
		{
			//Partes sin atender propios
			$con = selectNewPartes($conexion, $user);
			$table=$table.'</td></tr>';
			if(mysqli_num_rows($con)>0)
			{
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<table>
					<tr>
						<th colspan="10">Partes propios abiertos</th>
					</tr>
				</table><br />
				<table>
					<tr>
						<th>Nº parte</th>
						<th>Fecha de creación</th>
						<th>Información</th>
						<th>Piezas afectadas</th>';
						if($funcion != 'Admin')
						{
							$_SESSION['mensaje'] = $_SESSION['mensaje'].'
							<th colspan="2">--</th>';
						}
					$_SESSION['mensaje'] = $_SESSION['mensaje'].'
					</tr>';		
				while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
				{
					$_SESSION['mensaje'] = $_SESSION['mensaje'].'
					<tr>
						<td>'.$fila['id_part'].'</td>
						<td>'.$fila['fecha_hora_creacion'].'</td>
						<td>'.$fila['inf_part'].'</td>
						<td>'.$fila['pieza'].'</td>';
						if($funcion != 'Admin')
						{
							$_SESSION['mensaje'] = $_SESSION['mensaje'].'
							<td>
								<a href="funciones.php?id_part='.$fila['id_part'].'&funcion=Borrar_parte">Borrar</a>
							</td>
							<td>
								<a href="veremp.php?funcion=Editar_parte&id_emp='.$id_emp.'&dni='.$dni.'&id_part='.$fila['id_part'].'">Editar</a>
							</td>';
						}
					$_SESSION['mensaje'] = $_SESSION['mensaje'].'
					</tr>';
				}
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table><br />';
			}			
			//Partes atendidos propios
			$con = selectOwnPartes($conexion, $user);
			$table=$table.'</td></tr>';
			if(mysqli_num_rows($con)>0)
			{
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<table>
					<tr>
						<th colspan="10">Partes propios atendidos</th>
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
			$num = countOldPartes($conexion, $user);
			if ($num>0)
			{
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<table>
					<tr>
						<th colspan="10">Partes propios cerrados</th>
					</tr>
				</table>';
			}
			$con = selectOldPartes($conexion, $user);	
			if (mysqli_num_rows($con) > 0)
			{			
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<br /><table>
					<tr>
						<th>Nº parte</th>
						<th>Fecha de creación</th>
						<th>Información</th>
						<th>Piezas afectadas</th>
						<th>Notas técnico</th>
						<th>Tecnico a cargo</th>
						<th>Tiempo de resolución</th>';
						if($funcion != 'Admin')
						{
							$_SESSION['mensaje'] = $_SESSION['mensaje'].'<th>--</th>';
						}
					$_SESSION['mensaje'] = $_SESSION['mensaje'].'
					</tr>';
				while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
				{
					$_SESSION['mensaje'] = $_SESSION['mensaje'].'
					<tr>
						<td>'.$fila['id_part'].'</td>
						<td>'.$fila['fecha_hora_creacion'].'</td>
						<td>'.$fila['inf_part'].'</td>
						<td>'.$fila['pieza'].'</td>
						<td>'.$fila['not_tec'].'</td>
						<td>'.$fila['nom_tec'].'</td>
						<td>'.tiempo($fila['tiempo'], 0).'</td>';
						if($funcion != 'Admin')
						{
						$_SESSION['mensaje'] = $_SESSION['mensaje'].'
						<td>
							<a href="funciones.php?id_part='.$fila['id_part'].'&funcion=Ocultar_parte">Ocultar</a>
						</td>';
						}
					$_SESSION['mensaje'] = $_SESSION['mensaje'].'
					</tr>';
				}
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				</table><br />';
			}
			$con = countHiddenPartes($conexion, $user);
			if (mysqli_num_rows($con) > 0 && $funcion != 'Admin')
			{
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<table>
					<tr>
						<td colspan="8">
							<a href="veremp.php?funcion=Ocultos&id_emp='.$id_emp.'&dni='.$dni.'">Ver ocultos</a>
						</td>
					</tr>
				</table><br />';
			}
		}
		//Vista Agregar parte
		if($funcion == 'Agregar_parte')
		{
			$_SESSION['mensaje'] = '
			<form class="crearP" action="funciones.php" method="post">
				<input type="hidden" name="funcion" value="Crear_parte">
				<label>Descripción del problema:</label><br />
				<textarea name="descripcion" rows="10" cols="40" required placeholder="resumen del fallo"></textarea><br />
				<p> ¿Que pieza crees que falla?:</p>
				<p> 
					<select name="pieza" required>
						<option value="--" selectted="selected">--</option>
						<option value="nose">No lo se</option>
						<option value="torre">La torre</option>
						<option value="pantalla">El monitor o proyector</option>
						<option value="raton">El raton</option>
						<option value="teclado">El teclado</option>
						<option value="impresora">La impresora</option>
					</select>
				</p>
				<input type="submit" name="Submit" value="Crear parte">
			</form><br/>';
		}
		//Vista Partes ocultos
		if($funcion == 'Ocultos')
		{
			$con = selectHiddenPartes($conexion, $user);
			if(mysqli_num_rows($con)>0)
			{
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
					<table>
						<tr>
							<th colspan="10">Partes ocultos</th>
						</tr>
					</table>
					<table><br />
						<tr>
							<th>Nº parte</th>
							<th>Empleado</th>
							<th>Información</th>
							<th>Pieza afectada</th>
							<th>Fecha resolución</th>
							<th>Hora resolución</th>					
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
						<td>'.$fila['fecha_resolucion'].'</td>
						<td>'.$fila['hora_resolucion'].'</td>
						<td>'.$fila['not_tec'].'</td>
						<td>'.$fila['nom_tec'].'</td>
						<td>
							<a href="funciones.php?id_part='.$fila['id_part'].'&funcion=Mostrar_parte">Mostrar</a>
						</td>				
					</tr>';
				}
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table><br/>';
			}
		}	
	}
	//Vista Editar parte
	if($funcion == 'Editar_parte')
	{
		$id_part = $_GET['id_part'];
		$con = selectParte($conexion, $id_part);
		$fila = mysqli_fetch_array($con, MYSQLI_ASSOC);
		$nombreCom = $fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'];
		$_SESSION['mensaje'] = $_SESSION['mensaje'].'
		<br /><table>
			<tr>
				<th>Editar empleado</th>
			</tr>
		</table><br />
		<form action="funciones.php" method="post">
			<input type="hidden" name="id_part" value="'.$fila['id_part'].'" />
			<input type="hidden" name="funcion" value="Actualizar_parte" />
			<table>
				<tr>
					<th>Nº parte</th>
					<th>Fecha de creación</th>
					<th>Información</th>
					<th>Piezas afectadas</th>
					<th>--</th>
				</tr>
				<tr>
					<td>'.$fila['id_part'].'</td>
					<td>'.$fila['fecha_hora_creacion'].'</td>
					<td><input type="text" name="inf_part" value="'.$fila['inf_part'].'" required /></td>
					<td>'.$fila['pieza'].'</td>
					<td><input type="submit" value="Guardar" /></td>
				</tr>
			</table>
		</form>';			
	}
	if($tipo == 'Tecnico' || $tipo == 'Admin')
	{
		//Vista Partes
		if($funcion == 'Partes' || $funcion == 'Admin')
		{
			//Partes abiertos no propios
			$con = selectNewOtherPartes($conexion, $user);
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
						<th>Empleado</th>
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
						<td>'.$fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'].'</td>
						<td>'.$fila['fecha_hora_creacion'].'</td>
						<td>'.$fila['inf_part'].'</td>
						<td>'.$fila['pieza'].'</td>';
					if(($id == $fila['tec_res'] || $fila['tec_res'] == "") && $funcion != 'Admin')
					{
							$_SESSION['mensaje'] = $_SESSION['mensaje'].'
							<td>
								<a href="veremp.php?funcion=Modificar_parte&id_emp='.$id_emp.'&dni='.$dni.'&id_part='.$fila['id_part'].'">Atender</a>
							</td>
						</tr>';
					}
					else
					{
						$_SESSION['mensaje'] = $_SESSION['mensaje'].'
							<td>--</td>
						</tr>';
					}
				}
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table><br />';
			}		
			//Partes atendidos
			$con = selectOtherPartes($conexion, $user);	
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
						<td>'.$fila['not_tec'].'</td>
						<td>'.$fila['nom_tec'].'</td>
						<td>
							<a href="veremp.php?funcion=Modificar_parte&id_emp='.$id_emp.'&dni='.$dni.'&id_part='.$fila['id_part'].'">Modificar</a>
						</td>
					</tr>';
				}
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table><br />';
			}
			//Partes cerrados
			$con = selectOldOtherPartes($conexion, $user);
			if (mysqli_num_rows($con) > 0)
			{
					$_SESSION['mensaje'] = $_SESSION['mensaje'].'
					<table>
						<tr>
							<th>Partes cerrados</th>
						</tr>
					</table><br />
					<table>
						<tr>
							<th>ID</th>
							<th>Empleado</th>
							<th>Datos</th>
							<th>Datos técnico</th>
							<th>Fecha/ hora</th>
							<th>Tiempo de resolución</th>
						</tr>';				
				while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
				{		
					$emp_crea = $fila['emp_crea'];
					$nom_emp = selectEmpleado($conexion, $emp_crea);
					$filas = mysqli_fetch_array($nom_emp, MYSQLI_ASSOC);
					$_SESSION['mensaje'] = $_SESSION['mensaje'].'
						<tr>
							<td>'.$fila['id_part'].'</td>
							<td>'.$filas['nombre'].' '.$filas['apellido1'].' '.$filas['apellido2'].'</td>
							<td>'.$fila['inf_part'].'</td>
							<td>'.$fila['not_tec'].'</td>
							<td>'.$fila['fecha_hora_creacion'].'</td>
							<td>'.tiempo($fila['tiempo'], 0).'</td>
						</tr>';
				}
			}
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'
			</table><br />';
		}
		//Vista Estadísticas
		if($funcion == 'Estadisticas' || $funcion == 'Admin')
		{
			$tiempo_medio = tiempoMedio($conexion, $user);
			if (mysqli_num_rows($tiempo_medio) > 0)
			{
				$fila2 = mysqli_fetch_array($tiempo_medio, MYSQLI_ASSOC);
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<table>
					<tr>
						<th colspan="2">Estadisticas</th>
					</tr>
				</table><br />
				<table>
					<tr>
						<th>Tiempo medio</th>
						<th>Partes resueltos</th>
					</tr>
					<tr>
						<td>'.tiempo($fila2['tiempo_medio'], 0).'</td>
						<td>'.$fila2['cantidad_partes'].'</td>
					</tr>';
			}
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table><br />';
			$piez = $conexion->query("select pieza, count(pieza) as 'numeroP' 
			from parte
			where resuelto=1
			group by pieza");
			if (mysqli_num_rows($piez) > 0)
			{
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
					<table>
						<tr>
							<th colspan="2">Piezas reportadas</th>
						</tr>
						<tr>
							<th>Nombre</th>
							<th>Nº de reportes</th>
						</tr>';
				while($fila = mysqli_fetch_array($piez, MYSQLI_ASSOC))
				{
					$_SESSION['mensaje'] = $_SESSION['mensaje'].'
						<tr>
							<td>'.$fila['pieza'].'</td>
							<td>'.$fila['numeroP'].'</td>
						</tr>';
				}
			}
		}
		//Vista Lista de empleados
		if($funcion == 'Lista')
		{
			//Lista de empleados
			$con = selectEmpleados($conexion);
			//comprobación partes existentes no cerrados
			if(mysqli_num_rows($con)>0)		
			{
				//insercion titulos tabla (html)
				$_SESSION['mensaje'] = '
				<br /><table><tr><th>Lista de empleados</th></tr></table><br />
					<table>
						<tr>
							<th>ID de empleado</th>
							<th>DNI del empleado</th>
							<th>Nombre</th>
							<th>Primer apellido</th>
							<th>Segundo apellido</th>
							<th>Tipo de empleado</th>
							<th colspan="3">--</th>
						</tr>';
				//recorrer datos de los empleados
				while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
				{
					//insercion partes (html) 
					$_SESSION['mensaje'] = $_SESSION['mensaje'].'
						<tr>
							<td><a href="veremp.php?id_emp='.$fila['id'].'&dni='.$fila['dni'].'&funcion=Admin&tipo=Admin">'.$fila['id'].'</a></td>
							<td>'.$fila['dni'].'</td>
							<td>'.$fila['nombre'].'</td>
							<td>'.$fila['apellido1'].'</td>
							<td>'.$fila['apellido2'].'</td>
							<td>'.$fila['tipo'].'</td>
							<td><a href="funciones.php?id_emp='.$fila['id'].'&funcion=Borrar_empleado">Borrar</a></td>
							<td><a href="veremp.php?funcion=Editar_empleado&id_emp='.$fila['id'].'&dni='.$fila['dni'].'">Editar</a></td>
						</tr>';
				}
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
						<tr>
						<td colspan="8">
							<a href="veremp.php?funcion=Agregar_empleado&id_emp='.$id_emp.'&dni='.$dni.'">Agregar nuevo</a>
						</tr>
					</table>';
			}
		}
		//Vista Agregar empleado
		if ($funcion == 'Agregar_empleado')
		{
			$_SESSION['mensaje'] = '
				<script>
				var usuario = 
				{
					name = document.getElementByName("nombre"),
					surname1 = document.getElementByName("apellido1"),
					surname2 = document.getElementByName("apellido2"),
					dni = document.getElementByName("dni"),
					tipo = document.getElementByName("tipo")
				}

				//document.getElementByName("Submit").onclick = function() {myFunction()};
				document.getElementById("Submit").addEventListener("click", myFunction);
				function myFunction() {
					  var textarea = document.createElement("textarea");
					  var texto = document.getElementById("dni").value;
					  var body = document.getElementById("body");
					  textarea.appendChild(texto);
					  body.appendChild(textarea);
					  document.forms[0].submit();
				}
				</script>
			<form class="nuevoemp" action="funciones.php" method="post" id="formulario">
				<input type="hidden" name="funcion" value="Crear_empleado">
				<h1>Hoja del nuevo empleado:</h1><br />
				<label>DNI:</label>
				<input type="text" name="dni" required><br />
				<label>Nombre:</label>
				<input type="text" name="nombre" required><br />
				<label>Primer Apellido:</label>
				<input type="text" name="apellido1" required><br />
				<label>Segundo Apellido:</label>
				<input type="text" name="apellido2" ><br />
				<label>Contraseña:</label>
				<input type="password" name="pass" required><br />
				<p> ¿Que tipo de empleado es?:</p>
				<p>
					<select name="tipo" required>
						<option value="Limpiador">Un limpiador</option>
						<option value="Encargado">Un encargado</option>
						<option value="Tecnico">Un tecnico</option>
						<option value="Admin">Un administrador</option>
						<option value="Temporal">Uno temporal</option>
						<option value="Otro">Otro tipo aún no definido</option>
					</select>
				</p>
				<input type="hidden" name="user" id="user"><br />
				</form><br/>
				<button name="Submit" id="Submit">Añadir empleado</button>';
		}
		//Vista Editar empleado
		if($funcion == 'Editar_empleado')
		{
			$id_emp = $_GET['id_emp'];
			$con = selectEmpleadoNoAdmin($conexion);
			$fila = mysqli_fetch_array($con, MYSQLI_ASSOC);
			$nombreCom = $fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'];
			$_SESSION['mensaje'] = $_SESSION['mensaje'].'
			<br /><table>
				<tr>
					<th>Editar empleado</th>
				</tr>
			</table><br />
			<form action="funciones.php" method="post">
				<input type="hidden" name="id_emp" value="'.$id_emp.'" />
				<input type="hidden" name="funcion" value="Actualizar_empleado" />
				<table>
					<tr>
						<th>DNI</th>
						<th>Nombre</th>
						<th>Primer apellido</th>
						<th>Segundo apellido</th>
						<th>Tipo</th>
						<th>--</th>
					</tr>
					<tr>
						<td><input type="text" name="dni" value="'.$fila['dni'].'" required /></td>
						<td><input type="text" name="nombre" value="'.$fila['nombre'].'" required /></td>
						<td><input type="text" name="apellido1" value="'.$fila['apellido1'].'" required /></td>
						<td><input type="text" name="apellido2" value="'.$fila['apellido2'].'" /></td>
						<td><input type="text" name="tipo" value="'.$fila['tipo'].'" required /></td>
						<td><input type="submit" value="Guardar" /></td>
					</tr>
				</table>
			</form>';			
		}
		//Vista Modificar parte
		if($funcion == 'Modificar_parte')
		{
			$id_part = $_GET['id_part'];
			//Extrae datos parte
			$con = selectFullDataParte($conexion, $id_part);
			$fila = mysqli_fetch_array($con, MYSQLI_ASSOC);
			$_SESSION['mensaje'] = '
			<div class="mod_parte">
				<p>Formulario de edición</p>
				<p>Nombre del empleado: <strong>'.$fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'].'</strong></p>
				<p>Información del parte: <strong>'.$fila['inf_part'].'</strong></p>
				<p>Pieza afectada: <strong>'.$fila['pieza'].'</strong></p>
				<p>Fecha de creacion: <strong>'.$fila['fecha_hora_creacion'].'</strong></p>
				<p>Notas anteriores: <strong>'.$fila['not_tec'].'</strong></p>
				
				<form action="" method="post">
					<label>Notas de resolución:</label><br/>
					<textarea name="not_tec" rows="2" cols="40" required></textarea><br/>
					
				<p>Piezas afectadas:</p>
				<p> 
					<select name="pieza">
						<option value="--" selectted="selected">--</option>
						<optgroup label="Sobre la torre">
							<option value="torre">La torre</option>
							<option value="Placa base">La placa base</option>
							<option value="HDD">El disco duro</option>
							<option value="procesador">El procesador</option>
							<option value="grafica">La grafica</option>
							<option value="RAM">La memoria RAM</option>
							<option value="lector">El lector</option>
						</optgroup>
						<optgroup label="perifericos">
							<option value="pantalla">El monitor o proyector</option>
							<option value="raton">El raton</option>
							<option value="teclado">El teclado</option>
							<option value="impresora">La impresora</option>
						</optgroup>
					<optgroup label="otros">
						 <option value="regleta">La regleta</option>
						 <option value="Router">El router</option>
					</optgroup>
					</select>
				</p>
				<input type="hidden" name="id_part" value="'.$id_part.'" />
				<input type="hidden" name="id_emp" value="'.$fila['id'].'" />
				<input type="submit" name="Editar parte" value="Editar parte" onclick=this.form.action="insertparte.php" />
				<input type="submit" name="Cerrar parte" value="Cerrar parte" onclick=this.form.action="cierraparte.php" />
				</form>
			</div>
		</table><br />';
		}
	}
}
header('Location: menu.php');
mysqli_close($conexion);
?>