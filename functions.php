<?php
include 'classes.php';
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
		ON E.id=P.emp_crea 
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
		$con = $conexion->query("SELECT COUNT(P.id_part) AS Partes
		FROM Empleados E INNER JOIN parte P
		ON E.id=P.emp_crea
        WHERE E.id=$user->id AND E.dni='$user->dni'");
        $result = $con->fetch_array(MYSQLI_ASSOC);
        return $result['Partes'];
    }
    function countPiezas($conexion)
    {
        return $conexion->query("SELECT pieza, COUNT(pieza) AS 'numeroP' 
        FROM parte
        WHERE resuelto=1
        GROUP BY pieza");
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
		$con = $conexion->query("SELECT COUNT(*) AS Partes
		FROM parte 
        WHERE oculto=1 AND emp_crea = $user->id");
        $result = $con->fetch_array(MYSQLI_ASSOC);
        return $result['Partes'];
    }
	function selectHiddenPartes($conexion, $user)
	{
		return $conexion->query("SELECT P.id_part, P.inf_part, P.pieza, E.nombre, E.apellido1, E.apellido2, E.id, P.fecha_resolucion, P.hora_resolucion, nom_tec, not_tec
		FROM parte P INNER JOIN Empleados E
		ON P.emp_crea=E.id 
		WHERE oculto='1' AND E.dni='$user->dni' 
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
    function tiempoMedioAdmin($conexion)
    {
        return $conexion->query("SELECT ROUND(AVG(Tiempo),0) AS 'tiempo_medio', nom_tec FROM Tiempo_resolucion
        GROUP BY nom_tec");
    }
    function modParte($conexion)
    {
        $id_part = $_GET['id_part'];
        //Extrae datos parte
        $con = selectFullDataParte($conexion, $id_part);
        $fila = mysqli_fetch_array($con, MYSQLI_ASSOC);
        return '
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
                <a href="veremp.php?funcion=Editar_parte&id_emp='.$user->id.'&dni='.$dni.'&id_part='.$id.'">Editar</a>
            </td>';
        }
        else if ($state == 0 && (in_array(2, $permissions) || in_array(9, $permissions)) && $maker != $user->id) {
            $data = $data.'<td colspan="2"><a href="veremp.php?funcion=Atender_parte&id_emp='.$user->id.'&dni='.$user->$dni.'&id_part='.$id.'">Atender</a></td>';
        }
        else if ($state == 1 && (in_array(4, $permissions) || in_array(10, $permissions)) && $maker != $user->id) {
            $data = $data.'<td colspan="2"><a href="veremp.php?funcion=Modificar_parte&id_emp='.$user->id.'&dni='.$user->$dni.'&id_part='.$id.'">Modificar</a></td>';
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

		//Escritura

		//Tecnico
		//Permiso 11 -> Atender parte de empleados

		//Empleado
		//Permiso 12 -> Crear parte
		//Permiso 13 -> Borrar parte propio no atendido

		//Admin
		//Permiso 12 -> Crear parte
		//Permiso 13 -> Borrar parte propio no atendido
		//Permiso 14 -> Atender parte de empleados (no propios)

		if($user->tipo == 'Tecnico')
		{
			$permissions[0] = 0;
			$permissions[1] = 1;
			$permissions[2] = 2;
			$permissions[3] = 3;
			$permissions[4] = 4;
			$permissions[5] = 11;
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
    function htmlMaker($tag, $data)
    {
        return '<'.$tag.'>'.$data.'</'.$tag.'>';
    }
    function countOwnPartes($conexion, $dni)
    {
        //partes no ocultos propios (empleado)
        $con = $conexion->query("SELECT COUNT(*) AS Partes
        FROM parte 
        WHERE oculto=0 AND emp_crea = (SELECT id FROM Empleados WHERE dni = '$dni')");
        $result = $con->fetch_array(MYSQLI_ASSOC);
        return $result['Partes'];
        //return mysqli_num_rows($con);
    }
    function countNewPartes($conexion)
    {
        //Partes sin atender (tecnico)
        $con = $conexion->query("SELECT COUNT(P.id_part) AS Partes
        FROM parte P INNER JOIN Empleados E 
        ON P.emp_crea=E.id 
        WHERE P.not_tec IS NULL 
        GROUP BY P.id_part, P.inf_part, E.nombre, E.id");
        //return mysqli_num_rows($con);
        //return $con->num_rows;
        $result = $con->fetch_array(MYSQLI_ASSOC);
        return $result['Partes'];
    }
    //Partes de un técnico
    function countPartes($conexion, $nombreCom)
    {
        $con = $conexion->query("SELECT COUNT(P.id_part) AS Partes
        FROM parte P INNER JOIN Empleados E 
        ON P.emp_crea=E.id 
        WHERE P.nom_tec='$nombreCom' 
        GROUP BY P.id_part, P.inf_part, E.nombre, E.id");
        //return mysqli_num_rows($con);
        //return $con->num_rows;
        $result = $con->fetch_array(MYSQLI_ASSOC);
        return $result['Partes'];
    }
    //Partes de un empleado
    function countAllPartes($conexion)
    {
        $con = $conexion->query("SELECT COUNT(P.id_part) AS Partes
        FROM parte P INNER JOIN Empleados E
        ON P.emp_crea=E.id
        GROUP BY P.id_part, P.inf_part, E.nombre, E.id");
        //return $con->num_rows;
        //return mysqli_num_rows($con);
        $result = $con->fetch_array(MYSQLI_ASSOC);
        return $result['Partes'];
    }
    function selectIncidence($conexion, $id)
    {
        $con = $conexion->query("SELECT * 
        FROM parte
        WHERE id_part=$id");
        return $con->fetch_array(MYSQLI_ASSOC);
    }
    //OTHER
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
        return $table;
    }
    function structure($user, $conexion)
    {
        if($user->tipo != 'Tecnico' && $user->tipo != 'Admin')
        {
            //partes no ocultos propios (empleado)
            $nums[0] = countOwnPartes($conexion, $user->dni);
            $nums[1] = 0;
        }
        else
        {
            if($user->tipo == 'Tecnico')
            {
                //Partes sin atender (tecnico)
                $nums[0] = countNewPartes($conexion);
                //Partes atendidos propios (tecnico)
                $nums[1] = countPartes($conexion, $user->comName);
            }
            else
            {
                //Lista de partes (admin)
                $nums[0] = countAllPartes($conexion);
                $nums[1] = 0;
            }
        }
        return $nums;
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
    function getEmployee($fila)
    {
        $user = new user;
        $user->dni = $fila['dni'];
        $user->name = $fila['nombre'];
        $user->surname1 = $fila['apellido1'];
        $user->surname2 = $fila['apellido2'];
        $user->tipo = $fila['tipo'];
        $user->id = $fila['id'];
        return $user;
    }
    function hideParte($conexion, $user, $id)
    {
        return $conexion->query("update parte set oculto=1 where id_part=$id and emp_crea='$user->id' and resuelto=1");
    }
    function showHiddenParte($conexion, $id_part)
    {
        return $conexion->query("update parte set oculto=0 where id_part=$id_part");
    }
    function deleteParte($conexion, $id_part, $user)
    {
        return $conexion->query("DELETE 
        FROM parte 
        WHERE id_part=$id_part AND emp_crea=$user->id AND tec_res IS NULL");
    }
    function readIncidences($con, $user)
    {
        $response = "";
        while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
        {
            $response = $response.'
            <tr>
                <td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state=0">'.$fila['id_part'].'</a></td>
                <td>'.$fila['fecha_hora_creacion'].'</td>
                <td>'.$fila['inf_part'].'</td>
                <td>'.$fila['pieza'].'</td>
                <td>
                    <a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Borrar_parte">Borrar</a>
                </td>
                <td>
                    <a href="veremp.php?funcion=Editar_parte&id_emp='.$user->id.'&dni='.$user->dni.'&id_part='.$fila['id_part'].'">Editar</a>
                </td>
            </tr>';
        }
        return $response;
    }
    function readIncidences2($con)
    {
        $response = "";
        while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
        {
            $id=$fila['id_part'];
            $response = $response.'
            <tr>
            <td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state=1">'.$fila['id_part'].'</a></td>
                <td>'.$fila['fecha_hora_creacion'].'</td>
                <td>'.$fila['inf_part'].'</td>
                <td>'.$fila['pieza'].'</td>
                <td>'.$fila['not_tec'].'</td>
                <td>'.$fila['nom_tec'].'</td>			
            </tr>';
        }
        return $response;
    }
    function readIncidence3($con)
    {
        $response = "";
        while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
        {
            $response = $response.'
            <tr>
                <td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state=2">'.$fila['id_part'].'</a></td>
                <td>'.$fila['fecha_hora_creacion'].'</td>
                <td>'.$fila['inf_part'].'</td>
                <td>'.$fila['pieza'].'</td>
                <td>'.$fila['not_tec'].'</td>
                <td>'.$fila['nom_tec'].'</td>
                <td>'.tiempo($fila['tiempo'], 0).'</td>
                <td>
                    <a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ocultar_parte">Ocultar</a>
                </td>
            </tr>';
        }
        return $response;
    }
    function readIncidence4($user, $con)
    {
        $response = "";
        while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
        {
            $response = $response.'
            <tr>
                <td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state=0">'.$fila['id_part'].'</a></td>
                <td>'.$fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'].'</td>
                <td>'.$fila['fecha_hora_creacion'].'</td>
                <td>'.$fila['inf_part'].'</td>
                <td>'.$fila['pieza'].'</td>
                <td>
                    <a href="veremp.php?funcion=Modificar_parte&id_emp='.$user->id.'&dni='.$user->dni.'&id_part='.$fila['id_part'].'">Atender</a>
                </td>
            </tr>';
        }
    }
    function readIncidence5($con, $user)
    {
        $response = "";
        while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
        {
            $response = $response.'
            <tr>
                <td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state=1">'.$fila['id_part'].'</a></td>
                <td>'.$fila['fecha_hora_creacion'].'</td>
                <td>'.$fila['inf_part'].'</td>
                <td>'.$fila['pieza'].'</td>
                <td>'.$fila['not_tec'].'</td>
                <td>'.$fila['nom_tec'].'</td>
                <td>
                    <a href="veremp.php?funcion=Modificar_parte&id_emp='.$user->id.'&dni='.$user->dni.'&id_part='.$fila['id_part'].'">Modificar</a>
                </td>
            </tr>';
        }
        return $response;
    }
    function readIncidences6($con, $conexion)
    {
        $response = "";
        while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
        {		
            $emp_crea = $fila['emp_crea'];
            $nom_emp = selectEmpleado($conexion, $emp_crea);
            $filas = mysqli_fetch_array($nom_emp, MYSQLI_ASSOC);
            $response = $response.'
                <tr>
                    <td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state=2">'.$fila['id_part'].'</a></td>
                    <td>'.$filas['nombre'].' '.$filas['apellido1'].' '.$filas['apellido2'].'</td>
                    <td>'.$fila['inf_part'].'</td>
                    <td>'.$fila['not_tec'].'</td>
                    <td>'.$fila['fecha_hora_creacion'].'</td>
                    <td>'.tiempo($fila['tiempo'], 0).'</td>
                </tr>';
        }
        return $response;
    }
    function showPartes($conexion, $user)
    {
        $permissions = permissions($user);
        $response = "";
        if (in_array(5, $permissions)) {
            //Partes abiertos propios (empleado o admin)
            $con = selectNewPartes($conexion, $user);
            if ($con->num_rows>0) {
                $response = $response.'
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
                        <th>Piezas afectadas</th>
                        <th colspan="2">--</th>
                    </tr>'.readIncidences($con, $user).'
                </table><br />';
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
                    </tr>'.readIncidences2($con).'
                </table><br />';
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
                        <th colspan="10">Partes propios cerrados</th>
                    </tr>
                </table>';
            }
            $con = selectOldPartes($conexion, $user);	
            if ($con->num_rows > 0)
            {			
                $response = $response.'<br />
                <table>
                    <tr>
                        <th>Nº parte</th>
                        <th>Fecha de creación</th>
                        <th>Información</th>
                        <th>Piezas afectadas</th>
                        <th>Notas técnico</th>
                        <th>Tecnico a cargo</th>
                        <th>Tiempo de resolución</th>
                        <th>--</th>
                    </tr>'.readIncidence3($con).'
                </table><br />';
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
            if(mysqli_num_rows($con)>0)
            {
                $response = $response.'
                <table>
                    <tr>
                        <th colspan="10">Partes abiertos no propios (Técnico o Admin)</th>
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
                    </tr>';//readIncidence4($user, $con);
                    while($fila = mysqli_fetch_array($con, MYSQLI_ASSOC))
                    {
                        $response = $response.'
                        <tr>
                            <td><a href="veremp.php?id_part='.$fila['id_part'].'&funcion=Ver_parte&state=0">'.$fila['id_part'].'</a></td>
                            <td>'.$fila['nombre'].' '.$fila['apellido1'].' '.$fila['apellido2'].'</td>
                            <td>'.$fila['fecha_hora_creacion'].'</td>
                            <td>'.$fila['inf_part'].'</td>
                            <td>'.$fila['pieza'].'</td>
                            <td>
                                <a href="veremp.php?funcion=Modificar_parte&id_emp='.$user->id.'&dni='.$user->dni.'&id_part='.$fila['id_part'].'">Atender</a>
                            </td>
                        </tr>';
                    }
                    $response = $response.'</table><br />';
            }
        }
        if (in_array(3, $permissions) || in_array(10, $permissions)) {
            //Partes atendidos no propios (Técnico o Admin)
            $con = selectOtherPartes($conexion, $user);
            if(mysqli_num_rows($con)>0)
            {
                $response = $response.'
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
                    </tr>'.readIncidence5($con, $user).'
                </table><br />';
            }
        }
        if (in_array(4, $permissions) || in_array(11, $permissions)) {
            //Partes cerrados no propios (Técnico o Admin)
            $con = selectOldOtherPartes($conexion, $user);
            if (mysqli_num_rows($con) > 0)
            {
                $response = $response.'
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
                    </tr>'.readIncidences6($con, $conexion).'
                </table><br />';
            }
        }
        return $response;
    }
?>