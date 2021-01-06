<?php
include 'newFunctions.php';
    define('OneMonth', 2592000);
    define('OneWeek', 604800);
    define('OneDay', 86400);
    define('OneHour', 3600);
    define('OneMinute', 60);
    function SecondsToTimeFn($seconds)
    { 
        $num_units = setNumUnitsFn($seconds);
        $time_descr = array( 
            "meses" => floor($seconds / OneMonth), 
            "semanas" => floor(($seconds%OneMonth) / OneWeek), 
            "días" => floor(($seconds%OneWeek) / OneDay), 
            "horas" => floor(($seconds%OneDay) / OneHour), 
            "minutos" => floor(($seconds%OneHour) / OneMinute), 
            "segundos" => floor($seconds%OneMinute), 
        );
        $res = ""; $counter = 0;
        foreach ($time_descr as $k => $v) 
        { 
            if ($v) 
            { 
                $res.=$v." ".$k; $counter++; 
                if($counter>=$num_units) break; 
                elseif($counter) 
                $res.=", "; 
            } 
        }
        $_SESSION['time'] = $res;
        return $_SESSION['time'];
    }
    function setNumUnitsFn($seconds)
    {
        switch ($seconds) {
            case $seconds>= OneMonth:
                return 6;

            case $seconds>= OneWeek:
                return 5;

            case $seconds>= OneDay:
                return 4;

            case $seconds>= OneHour:
                return 3;

            case $seconds>= OneMinute:
                return 2;

            default:
                return 1;
                break;
        }
    }
    function checkFn($input)
    {
        if($input == "")
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    function structureFn($user, $conexion)
    {
        if($user->tipo != 'Tecnico' && $user->tipo != 'Admin')
        {
            //partes no ocultos propios (empleado)
            $nums[0] = countOwnPartesSql($conexion, $user->dni);
            $nums[1] = 0;
        }
        else
        {
            if($user->tipo == 'Tecnico')
            {
                //Partes sin atender (tecnico)
                $nums[0] = countNewPartesSql($conexion);
                //Partes atendidos propios (tecnico)
                $nums[1] = countPartesSql($conexion, $user->id);
            }
            else
            {
                //Lista de partes (admin)
                $nums[0] = countNewPartesSql($conexion);
                $nums[1] = countPartesSql($conexion, $user->id);
                $nums[2] = countOwnPartesSql($conexion, $user->dni);
            }
        }
        return $nums;
    }
    function updateNotesFn($conexion, $user)
    {
        $id_part = $_POST['id_part'];
		$inf_part = $_POST['inf_part'];
        insertNoteSql($id_part, $user, $inf_part);
    }
    function buildEmployeeFn($conexion, $user)
    {
        $_GET['id'] = $user->id;
        $permissions = getPermissionsFn();
        if (in_array(19, $permissions)) {
            makeEmployeeFn($conexion, $_POST['username'], $_POST['pass'], $_POST['dni'], $_POST['nombre'], $_POST['apellido1'], $_POST['apellido2'], $_POST['tipo']);
            $_SESSION['funcion'] = 'Lista';
        }
    }
    function closeParteFn($conexion, $user)
    {
        $id_part = $_POST['id_part'];
        $not_tec = $_POST['not_tec'];
        $pieza = $_POST['pieza'];
        if($pieza == '--')
        {
            closeParte1Sql($conexion, $id_part, $user);
        }
        else
        {
            closeParte2Sql($conexion, $pieza, $id_part, $user);
        }
        updateNoteListSql($conexion, $user, $id_part, $not_tec);
        $_SESSION['funcion'] = 'Partes';
    }
    function updateParteFn($conexion, $user)
    {
        $id_part = $_POST['id_part'];
        $not_tec = $_POST['not_tec'];
        $pieza = $_POST['pieza'];
        if($pieza == '--')
        {
            updateParte1Sql($conexion, $id_part, $user);
        }
        else
        {
            updateparte2Sql($conexion, $pieza, $id_part, $user);
        }
        updateNoteListSql($conexion, $user, $id_part, $not_tec);
        $_SESSION['funcion'] = 'Partes';
    }
    function buildParteFn($conexion)
    {
		$pieza = $_POST['pieza'];
		$descripcion = $_POST['descripcion'];
		if($pieza != "--")
		{	
            insertParte1Sql($conexion, $user, $descripcion, $pieza);
		}
		else
		{
            insertParte2Sql($conexion, $user, $descripcion);
		}
		$_SESSION['funcion'] = 'Partes';
    }
    function updateEmpleadoFn($conexion)
    {
        $user = json_decode($_POST['user']);
        $bool = checkFn($user->dni);
        $bool = checkFn($user->name);
        $bool = checkFn($user->surname1);
        $bool = checkFn($user->surname2);
        if($bool == true)
        {
            updateEmployeeSql($conexion, $user);
        }
        $_SESSION['funcion'] = 'Lista';
    }
    function loginFn($username, $password)
    {
        $conexion = connectionFn();
        if ($conexion->connect_error)
        {
            $_SESSION['mensaje'] = die("La conexión falló: " . $conexion->connect_error);
            header('Location: login.html');
        }
        else
        {
            $credentials = new credentials($username, $password);
            $con = checkCredentialsSql($credentials, $conexion);
            if ($con->num_rows > 0)
            {
                $creds = $con->fetch_array(MYSQLI_ASSOC);
                sessionStartFn();
                $credentials->employee = $creds['employee'];
                $_SESSION['credentials'] = json_encode($credentials);
                $con = selectEmployeeDataSql($conexion, $credentials);
                //extrae datos personales
                $fila = $con->fetch_array(MYSQLI_ASSOC);
                $user_info = getUserFn($fila['dni'], $fila['nombre'], $fila['apellido1'], $fila['apellido2'], $fila['tipo'], $fila['id']);
                $_SESSION['user'] =  json_encode($user_info);
                header('Location: menu.php');
            }
            else
            {
                $_SESSION['mensaje'] = '<p class="respuesta">Username o Password estan incorrectos.</p>';
                header('Location: login.php');
            }
        }
    }
    function logoutFn()
    {
        $_SESSION = array();
		if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
		}
		session_destroy();
		header("Location: login.php");
    }
    function Actualizar_empleadoFn($conexion)
    {
        $dni = $_POST['dni'];
        $nombre = $_POST['nombre'];
        $apellido1 = $_POST['apellido1'];
        $apellido2 = $_POST['apellido2'];
        $tipo = $_POST['tipo'];
        $con = getEmployeeByUsernameSql($conexion, $dni);
        $fila = $con->fetch_array(MYSQLI_ASSOC);
        //$user = getUserFn($fila['dni'], $fila['nombre'], $fila['apellido1'], $fila['apellido2'], $fila['tipo'], $fila['id_emp']);
        $user->name = $_POST['nombre'];
        $user->surname1 = $_POST['apellido1'];
        $user->surname2 = $_POST['apellido2'];
        $user->tipo = $_POST['tipo'];
        $user->dni = $_POST['dni'];
        $user->id = $fila['id'];
        updateEmployeeSql($conexion, $user);
        $_SESSION['funcion'] = 'Lista';
        //header('Location: veremp.php');
    }
    		/*Lectura
		Tecnico
		Permiso 1 -> Datos personales
		Permiso 2 -> Estadísticas
		Permiso 3 -> Partes abiertos de empleados
		Permiso 4 -> Partes cerrados de empleados
        Permiso 5 -> Partes atendidos de empleados
        Permiso 18 -> Piezas reportadas
		Empleado
		Permiso 1 -> Datos personales
		Permiso 6 -> Partes abiertos creados por el mismo
		Permiso 7 -> Partes atendidos creados por el mismo
		Permiso 8 -> Partes cerrados (visibles) creados por el mismo
		Permiso 9 -> Partes cerrados (ocultos) creados por el mismo
		Admin
		Permiso 1 -> Datos personales
		Permiso 2 -> Estadísticas
		Permiso 6 -> Partes abiertos creados por el mismo
		Permiso 7 -> Partes atendidos creados por el mismo
		Permiso 8 -> Partes cerrados (visibles) creados por el mismo
		Permiso 9 -> Partes cerrados (ocultos) creados por el mismo
		Permiso 10 -> Partes abiertos de empleados (no propios)
		Permiso 11 -> Partes cerrados de empleados (no propios)
        Permiso 12 -> Partes atendidos de empleados (no propios)
        Permiso 16 -> Lista de empleados
        Permiso 17 -> Estadísticas globales
        Permiso 18 -> Piezas eportadas
		Escritura
		Tecnico
        Permiso 21 -> Modificar parte de empleados
		Empleado
		Permiso 13 -> Crear parte
        Permiso 14 -> Borrar parte propio no atendido
        Permiso 22 -> Ocultar parte propio cerrado
		Admin
		Permiso 13 -> Crear parte
		Permiso 14 -> Borrar parte propio no atendido
        Permiso 15 -> Atender parte de empleados (no propios)
        Permiso 19 -> Crear empleado
        Permiso 20 -> Editar empleado
        Permiso 21 -> Modificar parte de empleados*/
?>