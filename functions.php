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
		return $conexion->query("SELECT T.tiempo, P.id_part, P.inf_part, P.not_tec, P.fecha_hora_creacion, P.fecha_resolucion, P.hora_resolucion, P.emp_crea, P.pieza
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
    function selectEmployee($conexion)
    {
        $id_emp = $_GET['id_emp'];
        $dni = $_GET['dni'];
		return $conexion->query("SELECT *
		FROM Empleados
		WHERE id=$id_emp AND dni='$dni'");
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
            $response = $response.'
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
                <a href="veremp.php?funcion=Editar_parte&id_emp='.$user->id.'&dni='.$dni.'&id_part='.$id.'">Editar</a>
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
    }
    function countNewPartes($conexion)
    {
        //Partes sin atender (tecnico)
        $con = $conexion->query("SELECT COUNT(P.id_part) AS Partes
        FROM parte P INNER JOIN Empleados E 
        ON P.emp_crea=E.id 
        WHERE P.not_tec IS NULL 
        GROUP BY P.id_part, P.inf_part, E.nombre, E.id");
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
            if(mysqli_num_rows($con)>0)
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
            if(mysqli_num_rows($con)>0)
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
            if (mysqli_num_rows($con) > 0)
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
            if(mysqli_num_rows($con)>0)
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
            if (mysqli_num_rows($tiempo_medio_global) > 0)
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
            if (mysqli_num_rows($piez) > 0)
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
            if(mysqli_num_rows($con)>0)		
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
    function mainStruture($funcion, $conexion, $user)
    {
        $response = "";
        switch ($funcion) {
            case 'Admin':
                $id = $_GET['id_emp'];
                $dni = $_GET['dni'];
                $con = selectEmployee($conexion);
                $result = $con->fetch_array(MYSQLI_ASSOC);
                $userA = new user();
                $userA->tipo = $result['tipo'];
                $userA->name = $result['nombre'];
                $userA->surname1 = $result['apellido1'];
                $userA->surname2 = $result['apellido2'];
                $userA->dni = $dni;
                $userA->id = $id;
                $response = $response.personalData($userA);
                $response = $response.showPartes($conexion, $userA);
                $response = $response.showStadistics($conexion, $userA);
                $response = $response.showGlobalStatistics($userA, $conexion);
                $response = $response.reportedPieces($conexion, $userA);
                break;
            case 'Datos_personales':
                //Vista Datos personales
                $response = $response.personalData($user);
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
                deleteParte($conexion, $id_part, $user);
                $funcion = 'Partes';
                break;
    
            case 'Ocultar_parte':
                //Ocultar parte cerrado
                $id_part = $_GET['id_part'];
                hideParte($conexion, $user, $id_part);
                $funcion = 'Partes';
                break;
    
            case 'Mostrar_parte':
                //Mostrar parte oculto
                $id_part = $_GET['id_part'];
                showHiddenParte($conexion, $id_part);
                $funcion = 'Partes'; 
                break;
    
            case 'Partes':
                //Vista Partes
                $response = $response.showPartes($conexion, $user);
                break;
    
            case 'Agregar_parte':
                //Vista Agregar parte
                $response = $response.addParte($user);
                break;
    
            case 'Ocultos':
                //Vista Partes ocultos
                $response = $response.showHiddenPartes($conexion, $user);
                break;
    
            case 'Editar_parte':
                //Vista Editar parte
                $response = $response.editParte($conexion);
                break;
    
            case 'Estadisticas':
                //Vista Estadísticas
                $response = $response.showStadistics($conexion, $user);
                $response = $response.showGlobalStatistics($user, $conexion);
                $response = $response.reportedPieces($conexion, $user);
                break;
    
            case 'Lista':
                //Vista Lista de empleados
                $response = $response.employeeList($conexion, $user);
                break;
    
            case 'Agregar_empleado':
                //Vista Agregar empleado
                $response = $response.addEmployee($user);
                break;
    
            case 'Editar_empleado':
                //Vista Editar empleado
                $response = $response.editEmployee($conexion, $user);
                break;
    
            case 'Atender_parte':
                //Vista Atender parte
                $response = $response.modParte($conexion, $user);
                break;
    
            case 'Modificar_parte':
                //Vista Modificar parte
                $response = $response.modParte($conexion, $user);
                break;
            default:
                break;
        }
        return $response;
    }
?>