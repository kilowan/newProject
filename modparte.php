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
	$id = $_POST['id'];
	//extraer datos parte
	$con = selectFullDataParte($conexion, $id_part);
	/*$con = $conexion->query("select E.nombre, P.not_tec, P.inf_part, P.pieza, P.fecha_hora_creacion from Empleados E, parte P where 
	E.id=P.emp_crea and id_part=$id");*/
	//imprime datos del parte
	while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
	{
		$_SESSION['mensaje'] = '
		<div class="mod_parte">
			<p>Formulario de edición</p>
			<p>Nombre del empleado: <strong>'.$fila['nombre'].'</strong></p>
			<p>Información del parte: <strong>'.$fila['inf_part'].'</strong></p>
			<p>Pieza afectada: <strong>'.$fila['pieza'].'</strong></p>
			<p>Fecha de creacion: <strong>'.$fila['fecha_hora_creacion'].'</strong></p>
			<p>Notas anteriores: <strong>'.$fila['not_tec'].'</strong></p>
			
			<form action="" method="post">
				<label>Notas de resolución:</label><br/>
				<textarea name="not_tec" rows="2" cols="40" required></textarea><br/>
				
			<p>Piezas afectadas:</p>
			<p> 
				<select name="piezas">
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
			<input type="hidden" name="id" value="'.$id.'" />
			<input type="submit" name="Editar parte" value="Editar parte" onclick=this.form.action="insertparte.php" />
			<input type="submit" name="Cerrar parte" value="Cerrar parte" onclick=this.form.action="cierraparte.php" />
			</form>
		</div>
	</table><br />';
	}	
}
mysqli_close($conexion);
header('Location: menu.php');
?>
