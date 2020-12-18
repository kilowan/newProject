<?php
session_start();
?>
<?php
if (isset($_SESSION['loggedin']) && $_SESSION['tipo']=='Admin')
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
}
</script>
	<form class="nuevoemp" action="rellemp.php" method="post">
		<h1>Hoja del nuevo empleado:</h1><br />
		<label>DNI:</label>
		<input type="text" name="dni" id="dni" required><br />
		<label>Nombre:</label>
		<input type="text" name="nombre" required><br />
		<label>Primer Apellido:</label>
		<input type="text" name="apellido1" required><br />
		<label>Segundo Apellido:</label>
		<input type="text" name="apellido2" ><br />
		<label>Contraseña:</label>
		<input type="password" name="pass" required><br />
		<label>User:</label>
        <textarea name="user" id="user"></textarea><br />
		<p> ¿Que tipo de empleado/a es?:</p>
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
		</form><br/>
        <button name="Submit" id="Submit">Añadir empleado</button>';
}
else
{
	$_SESSION['mensaje'] = '<p class="respuesta">Esta página sólo está disponible para administradores</p>';
}
header('Location: menu.php');
?>