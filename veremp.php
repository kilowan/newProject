<?php
session_start();
?>
<?php
include 'functions.php';
if (isset($_SESSION['loggedin']))
{	
	$user = $_SESSION['user'];
	$user = json_decode($user);
	$dni = $user->dni;
	$id_emp = $user->id;
	$tipo = $user->tipo;
	$nombreCom = $user->comName;
	$nombre = $user->name;
	$apellido1 = $user->surname1;
	$apellido2 = $user->surname2;
	$table = "";
	if(isset($_GET['funcion']))
	{
		$funcion = $_GET['funcion'];
	}
	else
	{
		$funcion = $_SESSION['funcion'];
		$SESSION = null;
	}
	//$funcion = check($_GET['funcion'], $_SESSION['funcion']);
	$permissions = permissions($user);
	
	//Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	//Vista Datos personales
	if($funcion == 'Datos_personales' || $funcion == 'Admin')
	{
		//Mostrar datos personales
		$_SESSION['mensaje'] = $_SESSION['mensaje'].personalData($user);
	}
	if($funcion == 'Ver_parte')
	{
		$id_part = $_GET['id_part'];
		$state = $_GET['state'];
		$fila = selectIncidence($conexion, $id_part);
		$_SESSION['mensaje'] = $_SESSION['mensaje'].'
		<br /><table>
			<tr>
				<th>Ver Parte</th>
			</tr>
		</table><br />
		<table>
			<tr>
				<td>Nº parte</td>
				<td>'.checkInput($fila['id_part']).'</td>
			</tr>
			<tr>
				<td>Empleado</td>
				<td>'.checkInput($fila['emp_crea']).'</td>
			</tr>
			<tr>
				<td>Información</td>
				<td>'.checkInput($fila['inf_part']).'</td>
			</tr>
			<tr>
				<td>Tecnico a cargo</td>
				<td>'.checkInput($fila['tec_res']).'</td>
			</tr>
			<tr>
				<td>Notas</td>
				<td>'.checkInput($fila['not_tec']).'</td>
			</tr>
			<tr>
				<td>Fecha de creación</td>
				<td>'.checkInput($fila['fecha_hora_creacion']).'</td>
			</tr>
			<tr>
				<td>Información</td>
				<td>'.checkInput($fila['inf_part']).'</td>
			</tr>
			<tr>
				<td>Piezas afectadas</td>
				<td>'.checkInput($fila['pieza']).'</td>
			</tr>
			<tr>'.buttons($id_part, $state, $user, $fila['emp_crea']).'</tr>
		</table>';
	}
	//Borrar parte no atendido
	if($funcion == 'Borrar_parte')
	{
		$id_part = $_GET['id_part'];
		$id_emp = $user->id;
		deleteParte($conexion, $id_part, $user);
		$_SESSION['funcion'] = 'Partes';
	}
	//Ocultar parte cerrado
	if($funcion == 'Ocultar_parte')
	{
		$id_part = $_GET['id_part'];
		hideParte($conexion, $user, $id_part);
		/*$conexion->query("update parte set oculto=1 where id_part=$id_part");*/
		$funcion = 'Partes';
		//header('Location: veremp.php');
	}
	//Mostrar parte oculto
	if($funcion == 'Mostrar_parte')
	{
		$id_part = $_GET['id_part'];
		//$conexion->query("update parte set oculto=0 where id_part=$id_part");
		showHiddenParte($conexion, $id_part);
		$funcion = 'Partes';
		//header('Location: veremp.php');
	}
	if($tipo != 'Tecnico')
	{
		//Vista Partes
		if($funcion == 'Partes'  || $funcion == 'Admin')
		{
			//Partes sin atender propios
			$con = selectNewPartes($conexion, $user);

			$table=$table.'</td></tr>';
			if($con && $con->num_rows >0)
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
						<td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state=0">'.$fila['id_part'].'</a></td>
						<td>'.$fila['fecha_hora_creacion'].'</td>
						<td>'.$fila['inf_part'].'</td>
						<td>'.$fila['pieza'].'</td>';
						if($funcion != 'Admin')
						{
							$_SESSION['mensaje'] = $_SESSION['mensaje'].'
							<td>
								<a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Borrar_parte">Borrar</a>
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
			if($con->num_rows>0)
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
					<td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state=1">'.$fila['id_part'].'</a></td>
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
			if ($con->num_rows > 0)
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
						<td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state=2">'.$fila['id_part'].'</a></td>
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
							<a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ocultar_parte">Ocultar</a>
						</td>';
						}
					$_SESSION['mensaje'] = $_SESSION['mensaje'].'
					</tr>';
				}
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				</table><br />';
			}
			$data = countHiddenPartes($conexion, $user);
			if ($data > 0 && $funcion != 'Admin')
			{
				$_SESSION['mensaje'] = $_SESSION['mensaje'].'
				<table>
					<tr>
						<td colspan="8">
							<a href="veremp.php?funcion=Ocultos&id_emp='.$user->id.'&dni='.$user->dni.'">Ver ocultos</a>
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
						<td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state=3">'.$fila['id_part'].'</a></td>
						<td>'.$fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'].'</td>
						<td>'.$fila['inf_part'].'</td>
						<td>'.$fila['pieza'].'</td>
						<td>'.$fila['fecha_resolucion'].'</td>
						<td>'.$fila['hora_resolucion'].'</td>
						<td>'.$fila['not_tec'].'</td>
						<td>'.$fila['nom_tec'].'</td>
						<td>
							<a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Mostrar_parte">Mostrar</a>
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
				<th>Editar parte</th>
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
			if (!isset($_SESSION['mensaje']))
			{
				$_SESSION['mensaje'] = "";
			}
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
						<td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state=0">'.$fila['id_part'].'</a></td>
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
						<td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state=1">'.$fila['id_part'].'</a></td>
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
							<td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state=2">'.$fila['id_part'].'</a></td>
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
					</tr>
				</table><br />';
			}
			if($tipo == 'Admin')
			{
				$tiempo_medio_global = tiempoMedioAdmin($conexion);
				if (mysqli_num_rows($tiempo_medio_global) > 0)
				{
					$_SESSION['mensaje'] = $_SESSION['mensaje'].'
					<table>
						<tr>
							<th colspan="2">Estadisticas gobales</th>
						</tr>
					</table><br />
					<table>
						<tr>
							<th>Tiempo medio</th>
							<th>Nombre de empleado</th>
						</tr>';
					while($fila3 = mysqli_fetch_array($tiempo_medio_global, MYSQLI_ASSOC))
					{
						//insercion partes (html)
						$_SESSION['mensaje'] = $_SESSION['mensaje'].'
						<tr>
							<td>'.tiempo($fila3['tiempo_medio'], 0).'</td>
							<td>'.$fila3['nom_tec'].'</td>
						</tr>';
					}
					$_SESSION['mensaje'] = $_SESSION['mensaje'].'</table><br />';
				}
			}
			$piez = countPiezas($conexion);
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
				$users = array();
				//recorrer datos de los empleados
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
				while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
				{
					$user = getEmployee($fila);

					array_push($users, $user);
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
		//Vista Atender parte
		if ($funcion == 'Atender_parte') {
			$_SESSION['mensaje'] = modParte($conexion);
		}
		//Vista Modificar parte
		if($funcion == 'Modificar_parte')
		{
			$_SESSION['mensaje'] = modParte($conexion);
		}
	}
}
header('Location: menu.php');
mysqli_close($conexion);
?>