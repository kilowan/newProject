<?php
    include 'functions.php';
    function modParteView($conexion, $user)
    {
        $response = "";
        $permissions = permissionsFn($user);
        if (in_array(21, $permissions)) 
        {
            $id_part = $_GET['id_part'];
            //Extrae datos parte
            $con = selectFullDataParteSql($conexion, $id_part);
            $fila = mysqli_fetch_array($con, MYSQLI_ASSOC);
            $response = $response.'<br />'.headerDataView('Editar Parte').'
            <table>
                <tr>
                    <td>Nombre del empleado</td>
                    <td>'.$fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'].'</td>
                </tr>
                <tr>
                    <td>Información</td>
                    <td>'.checkInputFn($fila['inf_part']).'</td>
                </tr>
                <tr>
                    <td>Tecnico a cargo</td>
                    <td>'.checkInputFn($fila['tec_res']).'</td>
                </tr>
                <tr>
                    <td>Fecha de creación</td>
                    <td>'.checkInputFn($fila['fecha_hora_creacion']).'</td>
                </tr>
                <tr>
                    <td>Pieza afectada</td>
                    <td>'.checkInputFn($fila['pieza']).'</td>
                </tr>
            </table>'.getNotesView($conexion, $id_part, $user).'
            <form action="veremp.php" method="post">
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
                <tr>
                    <td>Función</td>
                    <td>
                        <select name="funcion">
                            <option value="insertparte">Actualizar parte</option>
                            <option value="cierraparte">Cerrar parte</option>
                        </select>
                    </td>
                </tr>
                <input type="hidden" name="id_part" value="'.$id_part.'" />
                <input type="hidden" name="id_emp" value="'.$fila['id'].'" />
                <tr>
                    <td>
                        <input type="submit" name="Guardar" value="Guardar" />
                    </td>
                </tr>
            </table>';
        }
        return $response;
    }
    function buttonsView($id, int $state, $user, $maker)
    {
        $permissions = permissionsFn($user);
        //State
        //1: New
        //2: Attended
        //3: Closed
        //4: Hidden
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
        else if ($state == 1 && (in_array(2, $permissions) || in_array(9, $permissions)) && $maker != $user->id) {
            $data = $data.'<td colspan="2"><a href="veremp.php?funcion=Atender_parte&id_emp='.$user->id.'&dni='.$user->dni.'&id_part='.$id.'">Atender</a></td>';
        }
        else if ($state == 2 && (in_array(4, $permissions) || in_array(10, $permissions)) && $maker != $user->id) {
            $data = $data.'<td colspan="2"><a href="veremp.php?funcion=Modificar_parte&id_emp='.$user->id.'&dni='.$user->dni.'&id_part='.$id.'">Modificar</a></td>';
        }
        else if ($state == 3 && in_array(22, $permissions)) {
            $data = $data.'<td colspan="2"><a href="veremp.php?id_part='.$id.'&funcion=Ocultar_parte">Ocultar</a></td>';
        }
        else if ($state == 4 && in_array(8, $permissions)) {
            $data = $data.'<td colspan="2"><a href="veremp.php?id_part='.$id.'&funcion=Mostrar_parte">Mostrar</a></td>';
        }
        return $data;
    }
    function personalDataView($user)
	{
		return '
        <br />'.htmlMakerView("table", htmlMakerView("tr", htmlMakerView("th", "Datos personales"))).'<br />
        
            '.htmlMakerView("table", htmlMakerView("tr", htmlMakerView("td", "Id Empleado").htmlMakerView("td", $user->id))
            .htmlMakerView("tr", htmlMakerView("td", "DNI").htmlMakerView("td", $user->dni))
            .htmlMakerView("tr", htmlMakerView("td", "Nombre").htmlMakerView("td", $user->name))
            .htmlMakerView("tr", htmlMakerView("td", "Primer apellido").htmlMakerView("td", $user->surname1))
            .htmlMakerView("tr", htmlMakerView("td", "Segundo apellido").htmlMakerView("td", $user->surname2))
            .htmlMakerView("tr", htmlMakerView("td", "Tipo").htmlMakerView("td", $user->tipo))).'<br />';
    }
    function checkInputFn($input)
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
    function htmlMakerView($tag, $data)
    {
        return '<'.$tag.'>'.$data.'</'.$tag.'>';
    }
    function linksView($user, $nums)
    {
        $table = "";
        $permissions = permissionsFn($user);
        if(in_array(12, $permissions))
        {
            $table = $table.'<a class="link" href="veremp.php?funcion=Agregar_parte&id_emp='.$user->id.'&dni='.$user->dni.'">Crear parte</a>';
        }
        if($nums[0] > 0 || $nums[1] > 0)
        {
            $table = $table.'&nbsp'.'<a class="link" href="veremp.php?id_emp='.$user->id.'&dni='.$user->dni.'&funcion=Partes">Ver partes</a>';
        }	
        if(in_array(1, $permissions) && $nums[1] > 0)
        {
            $table=$table.'&nbsp<a class="link" href="veremp.php?funcion=Estadisticas&id_emp='.$user->id.'&dni='.$user->dni.'">Estadísticas</a>';
        }
        if(in_array(15, $permissions))
        {
            $table = $table.'&nbsp<a class="link" href="veremp.php?funcion=Lista&id_emp='.$user->id.'&dni='.$user->dni.'">Lista empleados</a>';
        }
        if (in_array(0, $permissions)) {
            $table = $table.'<a class="link" href="veremp.php?funcion=Datos_personales&id_emp='.$user->id.'&dni='.$user->dni.'">Datos personales</a>';
        }
        
        return $table;
    }
    function readIncidencesView($con, $user, int $state)
    {
        $response = "";
        while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
        {
            $response = $response.'
            <tr>
            <td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state='.$state.'">'.$fila['id_part'].'</a></td>
                <td>'.checkInputFn($fila['fecha_hora_creacion']).'</td>
                <td>'.checkInputFn($fila['inf_part']).'</td>
                <td>'.checkInputFn($fila['pieza']).'</td>
            </tr>';
        }
        return $response;
    }
    function showParteView($con, $user, int $state)
    {
        return '
        <table>
            <tr>
                <th>Nº parte</th>
                <th>Fecha de creación</th>
                <th>Información</th>
                <th>Piezas afectadas</th>
            </tr>'.readIncidencesView($con, $user, $state).'
        </table><br />';
    }
    function showPartesView($conexion, $user)
    {
        $permissions = permissionsFn($user);
        $response = "";
        if (in_array(5, $permissions)) {
            //Partes abiertos propios (empleado o admin)
            $con = selectNewPartesSql($conexion, $user);
            if ($con->num_rows>0) {
                $response = $response.headerDataView('Partes abiertos', 'colspan="10"');
                $response = $response.showParteView($con, $user, 0);
            }
        }
        if (in_array(6, $permissions)) {
            //Partes atendidos propios (empleado o admin)
            $con = selectOwnPartesSql($conexion, $user);
            if($con->num_rows>0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th colspan="10">Partes atendidos</th>
                    </tr>
                </table><br />';
                $response = $response.showParteView($con, $user, 1);
            }
        }
        if (in_array(7, $permissions)) {
            //Partes cerrados propios (empleado o admin)
            $num = countOldPartesSql($conexion, $user);
            if ($num>0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th colspan="10">Partes cerrados</th>
                    </tr>
                </table>';
            }
            $con = selectOldPartesSql($conexion, $user);	
            if ($con->num_rows > 0)
            {
                $response = $response.showParteView($con, $user, 2);
            }
        }
        if (in_array(8, $permissions)) {
            //Partes propios ocultos (Empleado)
            $data = countHiddenPartesSql($conexion, $user);
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
            $con = selectNewOtherPartesSql($conexion, $user);
            if($con->num_rows>0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th colspan="10">Partes abiertos</th>
                    </tr>
                </table><br />';
                $response = $response.showParteView($con, $user, 0);
            }
        }
        if (in_array(3, $permissions) || in_array(10, $permissions)) {
            //Partes atendidos no propios (Técnico o Admin)
            $con = selectOtherPartesSql($conexion, $user);
            if($con->num_rows>0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th colspan="10">Partes atendidos</th>
                    </tr>
                </table><br />';
                $response = $response.showParteView($con, $user, 1);
            }
        }
        if (in_array(4, $permissions) || in_array(11, $permissions)) {
            //Partes cerrados no propios (Técnico o Admin)
            $con = selectOldOtherPartesSql($conexion, $user);
            if ($con->num_rows > 0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th>Partes cerrados</th>
                    </tr>
                </table><br />';
                $response = $response.showParteView($con, $user, 2);
            }
        }
        return $response;
    }
    function getNotesView($conexion, $id_part, $user)
    {
        $response = "";
        $con = selectNotesSql($conexion, $id_part);
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
    /*function getNotesView2($incidence)
    {
        $response = "";
        //$con = selectNotesSql($conexion, $id_part);
        if ($incidence->$notes != null  && $incidence->$notes.count() >0) {
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
                foreach ($incidence->$notes as $note) {
                    $response = $response.'
                    <tr>
                        <td>'.$note.'</td>
                    </tr>';
                }
            $response = $response.'</table><br />';
        }
        return $response;
    }*/
    function showDetailParteView($conexion, $user, $id_part)
    {
        $id_part = $_GET['id_part'];
        $incidence = getIncidenceByIdFn();
        $state = $incidence->state;
        return '
        <br />'.headerDataView('Ver Parte').'
		<table>
			<tr>
				<td>Nº parte</td>
				<td>'.checkInputFn($incidence->id).'</td>
			</tr>
			<tr>
				<td>Empleado</td>
				<td>'.checkInputFn($incidence->owner->id).'</td>
            </tr>
            <tr>
				<td>Información</td>
				<td>'.checkInputFn($incidence->issueDesc).'</td>
            </tr>
			<tr>
				<td>Tecnico a cargo</td>
				<td>'.checkInputFn($incidence->solver->id).'</td>
			</tr>
			<tr>
				<td>Fecha de creación</td>
				<td>'.checkInputFn($incidence->initDateTime).'</td>
			</tr>
			<tr>
				<td>Piezas afectadas</td>
				<td>'.checkInputFn($incidence->piece).'</td>
			</tr>
			<tr>'.buttonsView($id_part, $state, $user, $incidence->owner->id).'</tr>
        </table>'.getNotesView($conexion, $id_part, $user);
    }
    function addParteView($user)
    {
        $response = "";
        $permissions = permissionsFn($user);
        if (in_array(12, $permissions)) {
            $response = $response.'
            <form class="crearP" action="veremp.php" method="post">
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
    function showHiddenPartesView($conexion, $user)
    {
        $response = "";
        $permissions = permissionsFn($user);
        if (in_array(8, $permissions)) 
        {
            $con = selectHiddenPartesSql($conexion, $user);
            if($con->num_rows>0)
            {
                $response = $response.headerDataView('Partes ocultos', 'colspan="10"');
                $response = $response.showParteView($con, $user, 3);
            }
        }
        return $response;
    }
    function editParteView($conexion)
    {
        $id_part = $_GET['id_part'];
		$con = selectParteSql($conexion, $id_part);
		$fila = mysqli_fetch_array($con, MYSQLI_ASSOC);
		$nombreCom = $fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'];
		return '
		<br />'.headerDataView('Editar parte').'
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
    function editEmployeeView($conexion, $user)
    {
        $response = "";
        $permissions = permissionsFn($user);
        if (in_array(20, $permissions)) 
        {
            $id_emp = $_GET['id_emp'];
            $con = selectEmpleadoNoAdminSql($conexion);
            $fila = mysqli_fetch_array($con, MYSQLI_ASSOC);
            $nombreCom = $fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'];
            $response = $response.'
            <br />'.headerDataView('Editar empleado').'
            <form action="veremp.php" method="post">
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
    function showGlobalStatisticsView($user_data, $conexion)
    {
        $response = "";
        $permissions = permissionsFn($user_data);
        if(in_array(17, $permissions))
        {
            $tiempo_medio_global = tiempoMedioAdminSql($conexion);
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
                        <td>'.SecondsToTimeFn($fila3['tiempo_medio']).'</td>
                        <td>'.$fila3['nom_tec'].'</td>
                    </tr>';
                }
                $response = $response.'</table><br />';
            }
        }
        return $response;
    }
    function reportedPiecesView($conexion, $user)
    {
        $response = "";
        $permissions = permissionsFn($user);
        if (in_array(18, $permissions)) 
        {
            $piez = countPiezasSql($conexion);
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
    function showStadisticsView($conexion, $user)
    {
        $response = "";
        $permissions = permissionsFn($user);
        if (in_array(16, $permissions)) 
        { 
            $tiempo_medio = tiempoMedioSql($conexion, $user);           
            $rows = $tiempo_medio->num_rows;
            if ($rows > 0)
            {
                $fila2 = $tiempo_medio->fetch_array(MYSQLI_ASSOC);
                $response = $response.headerDataView('Estadisticas', 'colspan="2"').'
                <table>
                    <tr>
                        <th>Tiempo medio</th>
                        <th>Partes resueltos</th>
                    </tr>
                    <tr>
                        <td>'.SecondsToTimeFn($fila2['tiempo_medio']).'</td>
                        <td>'.$fila2['cantidad_partes'].'</td>
                    </tr>
                </table><br />';
            }
        }
        return $response;
    }
    function headerDataView(string $headerData, ?string $extraData = null)
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
    function employeeListView($conexion, $user)
    {
        $response = "";
        $permissions = permissionsFn($user);
        if (in_array(15, $permissions)) 
        {
                    //Lista de empleados
            $con = selectEmpleadosSql($conexion);
            //comprobación partes existentes no cerrados
            if($con->num_rows>0)		
            {
                //insercion titulos tabla (html)
                $users = array();
                //recorrer datos de los empleados
                $response = $response.'
                <br />'.headerDataView('Lista de empleados').'
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
                    $user = getUserFn($fila['dni'], $fila['nombre'], $fila['apellido1'], $fila['apellido2'], $fila['tipo'], $fila['id']);

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
                        <td><a href="veremp.php?id='.$fila['id'].'&funcion=Borrar_empleado">Borrar</a></td>
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
    function addEmployeeView($user)
    {
        $response = "";
        $permissions = permissionsFn($user);
        if (in_array(19, $permissions)) {
            $response = $response.'
            <form class="nuevoemp" action="veremp.php" method="post" id="formulario">
                <input type="hidden" name="funcion" value="Crear_empleado" />
                <h1>Hoja del nuevo empleado:</h1><br />
                <label>DNI:</label>
                <input type="text" name="dni" required /><br />
                <label>Nombre:</label>
                <input type="text" name="nombre" required /><br />
                <label>Primer Apellido:</label>
                <input type="text" name="apellido1" required /><br />
                <label>Segundo Apellido:</label>
                <input type="text" name="apellido2" /><br />
                <label>Username:</label>
                <input type="username" name="username" required /><br />
                <label>Contraseña:</label>
                <input type="password" name="pass" required /><br />
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
                </p><br />
                <input type="Submit"id="Submit />
            </form><br/>';
        }
        return $response;
    }
    function mainStrutureView($funcion, $conexion, $user)
    {
        $response = "";
        switch ($funcion) {
            case 'Admin':
                $id = $_GET['id_emp'];
                $dni = $_GET['dni'];
                $con = selectEmployeeSql($conexion);
                $result = $con->fetch_array(MYSQLI_ASSOC);
                $userA = getUserFn($dni, $result['nombre'], $result['apellido1'], $result['apellido2'], $result['tipo'], $id);
                $response = $response.personalDataView($userA);
                $response = $response.showPartesView($conexion, $userA);
                $response = $response.showStadisticsView($conexion, $userA);
                $response = $response.showGlobalStatisticsView($userA, $conexion);
                $response = $response.reportedPiecesView($conexion, $userA);
                break;
            case 'Datos_personales':
                //Vista Datos personales
                $response = $response.personalDataView($user);
                break;
    
            case 'Ver_parte':
                //Vista ver parte
                $id_part = $_GET['id_part'];
                $response = $response.showDetailParteView($conexion, $user, $id_part);
                break;
    
            case 'Borrar_parte':
                //Borrar parte no atendido
                $id_part = $_GET['id_part'];
                $id_emp = $user->id;
                deleteParteSql($conexion, $id_part, $user);
                $_SESSION['funcion'] = 'Partes';
                break;
    
            case 'Ocultar_parte':
                //Ocultar parte cerrado
                $id_part = $_GET['id_part'];
                hideParteSql($conexion, $user, $id_part);
                $_SESSION['funcion'] = 'Partes';
                break;
    
            case 'Mostrar_parte':
                //Mostrar parte oculto
                $id_part = $_GET['id_part'];
                showHiddenParteSql($conexion, $id_part);
                $_SESSION['funcion'] = 'Partes'; 
                break;
    
            case 'Partes':
                //Vista Partes
                $response = $response.showPartesView($conexion, $user);
                break;
    
            case 'Agregar_parte':
                //Vista Agregar parte
                $response = $response.addParteView($user);
                break;
    
            case 'Ocultos':
                //Vista Partes ocultos
                $response = $response.showHiddenPartesView($conexion, $user);
                break;
    
            case 'Editar_parte':
                //Vista Editar parte
                $response = $response.editParteView($conexion);
                break;
    
            case 'Estadisticas':
                //Vista Estadísticas
                $response = $response.showStadisticsView($conexion, $user);
                $response = $response.showGlobalStatisticsView($user, $conexion);
                $response = $response.reportedPiecesView($conexion, $user);
                break;
    
            case 'Lista':
                //Vista Lista de empleados
                $response = $response.employeeListView($conexion, $user);
                break;
    
            case 'Agregar_empleado':
                //Vista Agregar empleado
                $response = $response.addEmployeeView($user);
                break;
    
            case 'Editar_empleado':
                //Vista Editar empleado
                $response = $response.editEmployeeView($conexion, $user);
                break;
    
            case 'Atender_parte':
                //Vista Atender parte
                $response = $response.modParteView($conexion, $user);
                break;
    
            case 'Modificar_parte':
                //Vista Modificar parte
                $response = $response.modParteView($conexion, $user);
                break;
            case 'Actualizar_parte':
                updateNotesFn($conexion, $user);
                break;
            case 'Crear_empleado':
                //addEmployeeFn($username, $password, $dni, $name, $surname1, $surname2, $type)
                buildEmployeeFn($conexion, $user);
                break;
            case 'insertparte':
                updateParteFn($conexion, $user);
                break;
            case 'cierraparte':
                closeParteFn($conexion, $user);
                break;
            case 'Crear_parte':
                buildParteFn($conexion);
                break;
            case 'Borrar_empleado':
                removeEmployeeFn();
                break;
            case 'Editar_empleado':
                updateEmpleadoFn($conexion);
                break;
            case 'Logout':
                logoutFn();
                break;
            case 'Actualizar_empleado':
                Actualizar_empleadoFn($conexion);
                break;
            default:
                break;
        }
        return $response;
    }

?>