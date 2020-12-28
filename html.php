<?php
    function modParte($conexion, $user)
    {
        $response = "";
        $permissions = permissions($user);
        if (in_array(21, $permissions)) 
        {
            $id_part = $_GET['id_part'];
            //Extrae datos parte
            $con = selectFullDataParte($conexion, $id_part);
            $fila = mysqli_fetch_array($con, MYSQLI_ASSOC);
            $response = $response.'<br />'.headerData('Editar Parte').'
            <table>
                <tr>
                    <td>Nombre del empleado</td>
                    <td>'.$fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'].'</td>
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
                    <td>Fecha de creación</td>
                    <td>'.checkInput($fila['fecha_hora_creacion']).'</td>
                </tr>
                <tr>
                    <td>Pieza afectada</td>
                    <td>'.checkInput($fila['pieza']).'</td>
                </tr>
            </table>'.getNotes($conexion, $id_part, $user).'
            <form action="" method="post">
            <table>
                <tr>
                    <td>Piezas afectadas:</td>
                    <td>
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
                    </td>
                </tr>
                <tr>
                    <td>Nueva nota</td>
                    <td><input type="text" name="not_tec" /></td>
                </tr>
                <input type="hidden" name="id_part" value="'.$id_part.'" />
                <input type="hidden" name="id_emp" value="'.$fila['id'].'" />
                <tr>
                    <td colspan="2">
                        <input type="submit" name="Editar parte" value="Editar parte" onclick=this.form.action="insertparte.php" />
                        <input type="submit" name="Cerrar parte" value="Cerrar parte" onclick=this.form.action="cierraparte.php" />
                    </td>
                </tr>
            </table>';
        }
        return $response;
    }
    function buttons($id, int $state, $user, $maker)
    {
        $permissions = permissions($user);
        //State
        //0: New
        //1: Attended
        //2: Closed
        //3: Hidden
        $data = "";
        if ($state == 0 && in_array(5, $permissions)&& $maker == $user->id) {
            $data = $data.'
            <td>
                <a href="veremp.php?id_part='.$id.'&funcion=Borrar_parte">Borrar</a>
            </td>
            <td>
                <a href="veremp.php?funcion=Editar_parte&id_emp='.$user->id.'&dni='.$user->dni.'&id_part='.$id.'">Editar</a>
            </td>';
        }
        else if ($state == 0 && (in_array(2, $permissions) || in_array(9, $permissions)) && $maker != $user->id) {
            $data = $data.'<td colspan="2"><a href="veremp.php?funcion=Atender_parte&id_emp='.$user->id.'&dni='.$user->dni.'&id_part='.$id.'">Atender</a></td>';
        }
        else if ($state == 1 && (in_array(4, $permissions) || in_array(10, $permissions)) && $maker != $user->id) {
            $data = $data.'<td colspan="2"><a href="veremp.php?funcion=Modificar_parte&id_emp='.$user->id.'&dni='.$user->dni.'&id_part='.$id.'">Modificar</a></td>';
        }
        else if ($state == 2 && in_array(7, $permissions)) {
            $data = $data.'<td colspan="2"><a href="veremp.php?id_part='.$id.'&funcion=Ocultar_parte">Ocultar</a></td>';
        }
        else if ($state == 3 && in_array(8, $permissions)) {
            $data = $data.'<td colspan="2"><a href="veremp.php?id_part='.$id.'&funcion=Mostrar_parte">Mostrar</a></td>';
        }
        return $data;
    }
    function personalData($user)
	{
		return '
        <br />'.htmlMaker("table", htmlMaker("tr", htmlMaker("th", "Datos personales"))).'<br />
        
            '.htmlMaker("table", htmlMaker("tr", htmlMaker("td", "Id Empleado").htmlMaker("td", $user->id))
            .htmlMaker("tr", htmlMaker("td", "DNI").htmlMaker("td", $user->dni))
            .htmlMaker("tr", htmlMaker("td", "Nombre").htmlMaker("td", $user->name))
            .htmlMaker("tr", htmlMaker("td", "Primer apellido").htmlMaker("td", $user->surname1))
            .htmlMaker("tr", htmlMaker("td", "Segundo apellido").htmlMaker("td", $user->surname2))
            .htmlMaker("tr", htmlMaker("td", "Tipo").htmlMaker("td", $user->tipo))).'<br />';
    }
    function permissions($user)
	{
		//Lectura

		//Tecnico
		//Permiso 0 -> Datos personales
		//Permiso 1 -> Estadísticas
		//Permiso 2 -> Partes abiertos de empleados
		//Permiso 3 -> Partes cerrados de empleados
        //Permiso 4 -> Partes atendidos de empleados
        //Permiso 16 -> Estadísticas
        //Permiso 18 -> Piezas eportadas

		//Empleado
		//Permiso 0 -> Datos personales
		//Permiso 5 -> Partes abiertos creados por el mismo
		//Permiso 6 -> Partes atendidos creados por el mismo
		//Permiso 7 -> Partes cerrados (visibles) creados por el mismo
		//Permiso 8 -> Partes cerrados (ocultos) creados por el mismo

		//Admin
		//Permiso 0 -> Datos personales
		//Permiso 1 -> Estadísticas
		//Permiso 5 -> Partes abiertos creados por el mismo
		//Permiso 6 -> Partes atendidos creados por el mismo
		//Permiso 7 -> Partes cerrados (visibles) creados por el mismo
		//Permiso 8 -> Partes cerrados (ocultos) creados por el mismo
		//Permiso 9 -> Partes abiertos de empleados (no propios)
		//Permiso 10 -> Partes cerrados de empleados (no propios)
        //Permiso 11 -> Partes atendidos de empleados (no propios)
        //Permiso 15 -> Lista de empleados
        //Permiso 16 -> Estadísticas
        //Permiso 17 -> Estadísticas globales
        //Permiso 18 -> Piezas eportadas

		//Escritura

		//Tecnico
        //Permiso 11 -> Atender parte de empleados
        //Permiso 21 -> Modificar parte de empleados

		//Empleado
		//Permiso 12 -> Crear parte
		//Permiso 13 -> Borrar parte propio no atendido

		//Admin
		//Permiso 12 -> Crear parte
		//Permiso 13 -> Borrar parte propio no atendido
        //Permiso 14 -> Atender parte de empleados (no propios)
        //Permiso 19 -> Crear empleado
        //Permiso 20 -> Editar empleado
        //Permiso 21 -> Modificar parte de empleados

		if($user->tipo == 'Tecnico')
		{
			$permissions[0] = 0;
			$permissions[1] = 1;
			$permissions[2] = 2;
			$permissions[3] = 3;
			$permissions[4] = 4;
            $permissions[5] = 11;
            $permissions[6] = 16;
            $permissions[7] = 18;
            $permissions[8] = 21;

        }
        else if ($user->tipo == 'Admin')
		{
			$permissions[0] = 0;
			$permissions[1] = 1;
			$permissions[2] = 5;
			$permissions[3] = 6;
			$permissions[4] = 7;
			$permissions[5] = 8;
			$permissions[6] = 9;
			$permissions[7] = 10;
			$permissions[8] = 11;
			$permissions[9] = 12;
			$permissions[10] = 13;
            $permissions[11] = 14;
            $permissions[12] = 15;
            $permissions[13] = 16;
            $permissions[14] = 17;
            $permissions[15] = 18;
            $permissions[16] = 19;
            $permissions[17] = 20;
            $permissions[18] = 21;
		}
		else
		{
			$permissions[0] = 0;
			$permissions[1] = 5;
			$permissions[2] = 6;
			$permissions[3] = 7;
			$permissions[4] = 8;
			$permissions[5] = 12;
			$permissions[6] = 13;
		}

		return $permissions;
    }
    function checkInput($input)
	{
		if($input == "" || $input == null)
		{
			return "none";
		}
		else
		{
			return $input;
		}
    }
    function htmlMaker($tag, $data)
    {
        return '<'.$tag.'>'.$data.'</'.$tag.'>';
    }
    function links($user, $nums)
    {
        $table = "";
        $permissions = permissions($user);
        if(in_array(12, $permissions))
        {
            $table = $table.'<a class="link" href="veremp.php?funcion=Agregar_parte&id_emp='.$user->id.'&dni='.$user->dni.'">Crear parte</a>';
        }
        if($nums[0] > 0 || $nums[1] > 0)
        {
            $table = $table.'&nbsp'.'<a class="link" href="veremp.php?id_emp='.$user->id.'&dni='.$user->dni.'&funcion=Partes">Ver partes</a>';
        }	
        if(in_array(1, $permissions))
        {
            $table=$table.'&nbsp<a class="link" href="veremp.php?funcion=Estadisticas&id_emp='.$user->id.'&dni='.$user->dni.'">Estadísticas</a>';
        }
        if(in_array(15, $permissions))
        {
            $table = $table.'&nbsp<a class="link" href="veremp.php?funcion=Lista&id_emp='.$user->id.'&dni='.$user->dni.'">Lista empleados</a>';
        }
        $table = $table.'<a class="link" href="veremp.php?funcion=Datos_personales&id_emp='.$user->id.'&dni='.$user->dni.'">Datos personales</a>';
        return $table;
    }
    function readIncidences($con, $user, int $state)
    {
        $response = "";
        while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
        {
            $response = $response.'
            <tr>
                <td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state='.$state.'">'.$fila['id_part'].'</a></td>
                <td>'.checkInput($fila['fecha_hora_creacion']).'</td>
                <td>'.checkInput($fila['inf_part']).'</td>
                <td>'.checkInput($fila['pieza']).'</td>
            </tr>';
        }
        return $response;
    }
    function showParteview($con, $user, int $state)
    {
        return '
        <table>
            <tr>
                <th>Nº parte</th>
                <th>Fecha de creación</th>
                <th>Información</th>
                <th>Piezas afectadas</th>
            </tr>'.readIncidences($con, $user, $state).'
        </table><br />';
    }
    function showPartes($conexion, $user)
    {
        $permissions = permissions($user);
        $response = "";
        if (in_array(5, $permissions)) {
            //Partes abiertos propios (empleado o admin)
            $con = selectNewPartes($conexion, $user);
            if ($con->num_rows>0) {
                $response = $response.headerData('Partes abiertos', 'colspan="10"');
                $response = $response.showParteview($con, $user, 0);
            }
        }
        if (in_array(6, $permissions)) {
            //Partes atendidos propios (empleado o admin)
            $con = selectOwnPartes($conexion, $user);
            if($con->num_rows>0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th colspan="10">Partes atendidos</th>
                    </tr>
                </table><br />';
                $response = $response.showParteview($con, $user, 1);
            }
        }
        if (in_array(7, $permissions)) {
            //Partes cerrados propios (empleado o admin)
            $num = countOldPartes($conexion, $user);
            if ($num>0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th colspan="10">Partes cerrados</th>
                    </tr>
                </table>';
            }
            $con = selectOldPartes($conexion, $user);	
            if ($con->num_rows > 0)
            {
                $response = $response.showParteview($con, $user, 2);
            }
        }
        if (in_array(8, $permissions)) {
            //Partes propios ocultos (Empleado o Admin)
            $data = countHiddenPartes($conexion, $user);
            if ($data > 0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <td colspan="8">
                            <a href="veremp.php?funcion=Ocultos&id_emp='.$user->id.'&dni='.$user->dni.'">Ver ocultos</a>
                        </td>
                    </tr>
                </table><br />';
            }
        }
        if (in_array(2, $permissions) || in_array(9, $permissions)) {
            //Partes abiertos no propios (Técnico o Admin)
            $con = selectNewOtherPartes($conexion, $user);
            if($con->num_rows>0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th colspan="10">Partes abiertos</th>
                    </tr>
                </table><br />';
                $response = $response.showParteview($con, $user, 0);
            }
        }
        if (in_array(3, $permissions) || in_array(10, $permissions)) {
            //Partes atendidos no propios (Técnico o Admin)
            $con = selectOtherPartes($conexion, $user);
            if($con->num_rows>0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th colspan="10">Partes atendidos</th>
                    </tr>
                </table><br />';
                $response = $response.showParteview($con, $user, 1);
            }
        }
        if (in_array(4, $permissions) || in_array(11, $permissions)) {
            //Partes cerrados no propios (Técnico o Admin)
            $con = selectOldOtherPartes($conexion, $user);
            if ($con->num_rows > 0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th>Partes cerrados</th>
                    </tr>
                </table><br />';
                $response = $response.showParteview($con, $user, 2);
            }
        }
        return $response;
    }
    function getNotes($conexion, $id_part, $user)
    {
        $response = "";
        $con = selectNotes($conexion, $id_part);
        if ($con->num_rows >0) {
            $response = $response.'
            <br /><table>
                <tr>
                    <th colspan="2">Notas del ténico</th>
                </tr>
            </table><br />
            <table>
                <tr>
                    <th>Nota</th>
                    <th>Fecha</th>
                </tr>';
            while ($result = $con->fetch_array(MYSQLI_ASSOC)) 
            {
                $response = $response.'
                <tr>
                    <td>'.$result['noteStr'].'</td>
                    <td>'.$result['date'].'</td>
                </tr>';
            }
            $response = $response.'</table><br />';
        }
        return $response;
    }
    function showDetailParteView($conexion, $user, $id_part)
    {
        $id_part = $_GET['id_part'];
		$state = $_GET['state'];
		$fila = selectIncidence($conexion, $id_part);
        return '
        <br />'.headerData('Ver Parte').'
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
        </table>'.getNotes($conexion, $id_part, $user);
    }
    function addParte($user)
    {
        $response = "";
        $permissions = permissions($user);
        if (in_array(12, $permissions)) {
            $response = $response.'
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
        return $response;
    }
    function showHiddenPartes($conexion, $user)
    {
        $response = "";
        $permissions = permissions($user);
        if (in_array(8, $permissions)) 
        {
            $con = selectHiddenPartes($conexion, $user);
            if($con->num_rows>0)
            {
                $response = $response.headerData('Partes ocultos', 'colspan="10"');
                $response = $response.showParteview($con, $user, $state);
            }
        }
        return $response;
    }
    function editParte($conexion)
    {
        $id_part = $_GET['id_part'];
		$con = selectParte($conexion, $id_part);
		$fila = mysqli_fetch_array($con, MYSQLI_ASSOC);
		$nombreCom = $fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'];
		return '
		<br />'.headerData('Editar parte').'
		<form action="veremp.php" method="post">
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
    function editEmployee($conexion, $user)
    {
        $response = "";
        $permissions = permissions($user);
        if (in_array(20, $permissions)) 
        {
            $id_emp = $_GET['id_emp'];
            $con = selectEmpleadoNoAdmin($conexion);
            $fila = mysqli_fetch_array($con, MYSQLI_ASSOC);
            $nombreCom = $fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'];
            $response = $response.'
            <br />'.headerData('Editar empleado').'
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
        return $response;
    }
    function showGlobalStatistics($user_data, $conexion)
    {
        $response = "";
        $permissions = permissions($user_data);
        if(in_array(17, $permissions))
        {
            $tiempo_medio_global = tiempoMedioAdmin($conexion);
            if ($tiempo_medio_global->num_rows > 0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th colspan="2">Estadisticas globales</th>
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
                    $response = $response.'
                    <tr>
                        <td>'.tiempo($fila3['tiempo_medio'], 0).'</td>
                        <td>'.$fila3['nom_tec'].'</td>
                    </tr>';
                }
                $response = $response.'</table><br />';
            }
        }
        return $response;
    }
    function reportedPieces($conexion, $user)
    {
        $response = "";
        $permissions = permissions($user);
        if (in_array(18, $permissions)) 
        {
            $piez = countPiezas($conexion);
            if ($piez->num_rows > 0)
            {
                $response = $response.'
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
                    $response = $response.'
                        <tr>
                            <td>'.$fila['pieza'].'</td>
                            <td>'.$fila['numeroP'].'</td>
                        </tr>';
                }
            }
        }
        return $response;
    }
    function showStadistics($conexion, $user)
    {
        $response = "";
        $permissions = permissions($user);
        if (in_array(16, $permissions)) 
        { 
            $tiempo_medio = tiempoMedio($conexion, $user);           
            $rows = $tiempo_medio->num_rows;
            if ($rows > 0)
            {
                $fila2 = $tiempo_medio->fetch_array(MYSQLI_ASSOC);
                $response = $response.headerData('Estadisticas', 'colspan="2"').'
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
        }
        return $response;
    }
    function headerData(string $headerData, ?string $extraData = null)
    {
        if ($extraData != null) 
        {
            return '<table>
            <tr>
                <th '.$extraData.'>'.$headerData.'</th>
            </tr>
        </table><br />';
        }
        else
        {
            return '
            <table>
                <tr>
                    <th>'.$headerData.'</th>
                </tr>
            </table><br />';
        }
    }
    function employeeList($conexion, $user)
    {
        $response = "";
        $permissions = permissions($user);
        if (in_array(15, $permissions)) 
        {
                    //Lista de empleados
            $con = selectEmpleados($conexion);
            //comprobación partes existentes no cerrados
            if($con->num_rows>0)		
            {
                //insercion titulos tabla (html)
                $users = array();
                //recorrer datos de los empleados
                $response = $response.'
                <br />'.headerData('Lista de empleados').'
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
                    $response = $response.'
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
                $response = $response.'
                    <tr>
                    <td colspan="8">
                        <a href="veremp.php?funcion=Agregar_empleado&id_emp='.$user->id.'&dni='.$user->dni.'">Agregar nuevo</a>
                    </tr>
                </table>';
            }
        }
        return $response;
    }
    function addEmployee($user)
    {
        $response = "";
        $permissions = permissions($user);
        if (in_array(19, $permissions)) {
            $response = $response.'
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
        return $response;
    }

?>