<?php
session_start();
?>
<?php
if (isset($_SESSION['loggedin']))
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
header('Location: menu.php');
?>
