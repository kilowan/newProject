<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Login</title>
		<LINK REL=StyleSheet HREF="formato.css" TYPE="text/css" MEDIA=screen>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	</head>
	<body>
		<div class="cabecera">
			<div class="nombre">
				<p>J&J.SA </p>
			</div>
			<div class="mensaje">
				<p>Bienvenidos</p>
			</div>
			<form action="checklogin.php" method="post" >
				<div class="login">
					<label>Usuario:</label>
					<input name="username" type="text" id="username" required>
					<label>Contraseña:</label>
					<input name="password" type="password" id="password" required>
					<input type="submit" name="Submit" value="LOGIN">
				</div>
			</form>
		</div>
		<div class="cuerpo">
			<?php
			session_start();
			?>
			<?php
			echo $_SESSION['mensaje'];
			$_SESSION['mensaje'] = '';
			?>
		</div>
		<div class="Pie">
			<p>Trabajo realizado por Jose Javier Valero Fuentes y Juan Francisco Navarro Ramiro para el curso de ASIR 2º X</p>
		</div>
	</body>
</html>
