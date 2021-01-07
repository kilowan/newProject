<?php
    include 'functions.php';
    function modParteView($conexion, $user)
    {
        $response = "";
        $_GET['id'] = $user->id;
        if (in_array(21, $user->permissions))
        {
            //Extrae datos parte
            $incidence = getIncidenceByIdFn();
            $response = $response.'<br />'.headerDataView('Editar Parte').'
            <table>
                <tr>
                    <td>Nombre del empleado</td>
                    <td>'.$incidence->owner->name.' '.$incidence->owner->surname1.' '.$incidence->owner->surname2.'</td>
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
                    <td>Pieza afectada</td>
                    <td>'.checkInputFn($incidence->piece).'</td>
                </tr>
            </table>'.getNotesView($incidence).'
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
                <input type="hidden" name="id_part" value="'.$incidence->id.'" />
                <input type="hidden" name="id_emp" value="'.$incidence->solver->id.'" />
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
        $_GET['id'] = $user->id;
        //State
        //1: New
        //2: Attended
        //3: Closed
        //4: Hidden
        $data = "";
        if ($state == 1 && in_array(6, $user->permissions)&& $maker == $user->id) {
            $data = $data.'
            <td>
                <a href="veremp.php?id_part='.$id.'&funcion=Borrar_parte">Borrar</a>
            </td>
            <td>
                <a href="veremp.php?funcion=Editar_parte&id_emp='.$user->id.'&dni='.$user->dni.'&id_part='.$id.'">Editar</a>
            </td>';
        }
        else if ($state == 1 && (in_array(3, $user->permissions) || in_array(10, $user->permissions)) && $maker != $user->id) {
            $data = $data.'<td colspan="2"><a href="veremp.php?funcion=Atender_parte&id_emp='.$user->id.'&dni='.$user->dni.'&id_part='.$id.'">Atender</a></td>';
        }
        else if ($state == 2 && (in_array(5, $user->permissions) || in_array(11, $user->permissions)) && $maker != $user->id) {
            $data = $data.'<td colspan="2"><a href="veremp.php?funcion=Modificar_parte&id_emp='.$user->id.'&dni='.$user->dni.'&id_part='.$id.'">Modificar</a></td>';
        }
        else if ($state == 3 && in_array(22, $user->permissions)) {
            $data = $data.'<td colspan="2"><a href="veremp.php?id_part='.$id.'&funcion=Ocultar_parte">Ocultar</a></td>';
        }
        else if ($state == 4 && in_array(9, $user->permissions)) {
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
    function linksView($user)
    {
        $table = "";
        $_GET['id'] = $user->id;
        $partes = structureFn($user->permissions);
        if(in_array(13, $user->permissions))
        {
            $table = $table.'<a class="link" href="veremp.php?funcion=Agregar_parte&id_emp='.$user->id.'&dni='.$user->dni.'">Crear parte</a>';
        }
        if($partes > 0)
        {
            $table = $table.'&nbsp'.'<a class="link" href="veremp.php?id_emp='.$user->id.'&dni='.$user->dni.'&funcion=Partes">Ver partes</a>';
        }
        if(in_array(2, $user->permissions) && $partes > 0)
        {
            $table=$table.'&nbsp<a class="link" href="veremp.php?funcion=Estadisticas&id_emp='.$user->id.'&dni='.$user->dni.'">Estadísticas</a>';
        }
        if(in_array(16, $user->permissions))
        {
            $table = $table.'&nbsp<a class="link" href="veremp.php?funcion=Lista&id_emp='.$user->id.'&dni='.$user->dni.'">Lista empleados</a>';
        }
        if (in_array(1, $user->permissions)) {
            $table = $table.'<a class="link" href="veremp.php?funcion=Datos_personales&id_emp='.$user->id.'&dni='.$user->dni.'">Datos personales</a>';
        }
        
        return $table;
    }
    function readIncidencesView($incidences, int $state)
    {
        $response = "";
        foreach ($incidences as $incidence) 
        {
            $response = $response.'
            <tr>
            <td><a href="veremp.php?id_part='.$incidence->id.'&funcion=Ver_parte&state='.$state.'">'.$incidence->id.'</a></td>
                <td>'.checkInputFn($incidence->initDateTime).'</td>
                <td>'.checkInputFn($incidence->issueDesc).'</td>
                <td>'.checkInputFn($incidence->piece).'</td>
            </tr>';
        }
        return $response;
    }
    function showParteView($incidences, int $state)
    {
        return '
        <table>
            <tr>
                <th>Nº parte</th>
                <th>Fecha de creación</th>
                <th>Información</th>
                <th>Piezas afectadas</th>
            </tr>'.readIncidencesView($incidences, $state).'
        </table><br />';
    }
    function showPartesView($conexion, $user)
    {
        $_GET['id'] = $user->id;
        $response = "";
        if (in_array(6, $user->permissions)) {
            //Partes abiertos propios (empleado o admin)
            $incidences = getIncidencesListFn();
            $new_array = array_filter($incidences, function($array) {
                return ($array->owner->id == $_GET['id_emp'] && $array->state == 1);
            });
            if (count($new_array) >0) {
                $response = $response.headerDataView('Partes abiertos', 'colspan="10"');
                $response = $response.showParteView($new_array, 1);
            }
        }
        if (in_array(7, $user->permissions)) {
            //Partes atendidos propios (empleado o admin)
            $incidences = getIncidencesListFn();
            $new_array = array_filter($incidences, function($array) {
                return ($array->owner->id == $_GET['id_emp'] && $array->state == 2);
            });
            if (count($new_array) >0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th colspan="10">Partes atendidos</th>
                    </tr>
                </table><br />';
                $response = $response.showParteView($new_array, 2);
            }
        }
        if (in_array(8, $user->permissions)) {
            //Partes cerrados propios (empleado o admin)
            $incidences = getIncidencesListFn();
            $new_array = array_filter($incidences, function($array) {
                return ($array->owner->id == $_GET['id_emp'] && $array->state == 3);
            });
            if (count($new_array) >0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th colspan="10">Partes cerrados propios</th>
                    </tr>
                </table>'.showParteView($new_array, 3);
            }
        }
        if (in_array(9, $user->permissions)) {
            //Partes propios ocultos (Empleado)
            $incidences = getIncidencesListFn();
            $new_array = array_filter($incidences, function($array) {
                return ($array->owner->id == $_GET['id_emp'] && $array->state == 4);
            });
            if (count($new_array) > 0)
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
        if (in_array(3, $user->permissions) || in_array(10, $user->permissions)) {
            //Partes abiertos no propios (Técnico o Admin)
            $incidences = getIncidencesListFn();
            $new_array = array_filter($incidences, function($array) {
                return ($array->owner->id != $_GET['id_emp'] && $array->state == 1);
            });
            if (count($new_array) >0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th colspan="10">Partes abiertos</th>
                    </tr>
                </table><br />';
                $response = $response.showParteView($new_array, 1);
            }
        }
        if (in_array(4, $user->permissions) || in_array(11, $user->permissions)) {
            //Partes atendidos no propios (Técnico o Admin)
            $incidences = getIncidencesListFn();
            $new_array = array_filter($incidences, function($array) {
                return ($array->owner->id != $_GET['id_emp'] && $array->solver->id == $_GET['id'] && $array->state == 2);
            });
            if (count($new_array) >0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th colspan="10">Partes atendidos</th>
                    </tr>
                </table><br />';
                $response = $response.showParteView($new_array, 2);
            }
        }
        if (in_array(5, $user->permissions) || in_array(12, $user->permissions)) {
            //Partes cerrados no propios (Técnico o Admin)
            $incidences = getIncidencesListFn();
            $new_array = array_filter($incidences, function($array) {
                return ($array->owner->id != $_GET['id_emp'] && $array->solver->id == $_GET['id'] && $array->state == 3);
            });

            if (count($new_array) >0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th>Partes cerrados</th>
                    </tr>
                </table><br />';
                $response = $response.showParteView($new_array, 3);
            }
        }
        return $response;
    }
    function getNotesView($incidence)
    {
        $response = "";
        if ($incidence->notes != null  && count($incidence->notes) >0) {
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
                foreach ($incidence->notes as $note) {
                    $response = $response.'
                    <tr>
                        <td>'.$note->noteStr.'</td>
                        <td>'.$note->date.'</td>
                    </tr>';
                }
            $response = $response.'</table><br />';
        }
        return $response;
    }
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
        </table>'.getNotesView($incidence);
    }
    function addParteView($user)
    {
        $response = "";
        $_GET['id'] = $user->id;
        if (in_array(13, $user->permissions)) {
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
        $_GET['id'] = $user->id;
        if (in_array(9, $user->permissions)) 
        {
            $incidences = getIncidencesListFn();
            $new_array = array_filter($incidences, function($array) {
                return ($array->owner->id == $_GET['id_emp'] && $array->state == 4);
            });
            if (count($new_array) >0)
            {
                $response = $response.headerDataView('Partes ocultos', 'colspan="10"');
                $response = $response.showParteView($new_array, 4);
            }
        }
        return $response;
    }
    function editParteView()
    {
        $incidence = getIncidenceByIdFn();
		return '
		<br />'.headerDataView('Editar parte').'
		<form action="veremp.php" method="post">
			<input type="hidden" name="id_part" value="'.$incidence->id.'" />
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
					<td>'.$incidence->id.'</td>
					<td>'.$incidence->initDateTime.'</td>
					<td><input type="text" name="inf_part" value="'.$incidence->issueDesc.'" required /></td>
					<td>'.$incidence->piece.'</td>
					<td><input type="submit" value="Guardar" /></td>
				</tr>
			</table>
		</form>';
    }
    function editEmployeeView($conexion, $user)
    {
        $response = "";
        $_GET['id'] = $user->id;
        if (in_array(20, $user->permissions)) 
        {
            $id_emp = $_GET['id_emp'];
            $users = getEmpolyeeListFn();
            $new_array = array_filter($users, function($array) {
                return ($array->id == $_GET['id'] && $array->tipo != 'Admin');
            });
            $user = array_pop($new_array);
            $response = $response.'
            <br />'.headerDataView('Editar empleado').'
            <form action="veremp.php" method="post">
                <input type="hidden" name="id_emp" value="'.$user->id.'" />
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
                        <td><input type="text" name="dni" value="'.$user->dni.'" required /></td>
                        <td><input type="text" name="nombre" value="'.$user->name.'" required /></td>
                        <td><input type="text" name="apellido1" value="'.$user->surname1.'" required /></td>
                        <td><input type="text" name="apellido2" value="'.$user->surname2.'" /></td>
                        <td><input type="text" name="tipo" value="'.$user->tipo.'" required /></td>
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
        $_GET['id'] = $user_data->id;
        if(in_array(17, $user_data->permissions))
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
        $_GET['id'] = $user->id;
        if (in_array(18, $user->permissions)) 
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
        $_GET['id'] = $user->id;
        if (in_array(2, $user->permissions)) 
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
        $_GET['id'] = $user->id;
        if (in_array(16, $user->permissions)) 
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
        $_GET['id'] = $user->id;
        if (in_array(19, $user->permissions)) {
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
                $permissions = getPermissionsFn($userA);
                $userA->permissions = $permissions;
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
                $response = $response.editParteView();
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
                updateNotesFn($conexion);
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
                buildParteFn($conexion, $user);
                break;
            case 'Borrar_empleado':
                removeEmployeeFn();
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