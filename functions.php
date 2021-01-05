<?php
include 'classes.php';
include 'sql.php';
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
    //new
    function connectionFn()
    {
        $sql_data = new sql;
        $sql_data->host_db = "localhost";
        $sql_data->user_db = "Ad";
        $sql_data->pass_db = "1234";
        $sql_data->db_name = "Fabrica";
        $conexion = new mysqli($sql_data->host_db, $sql_data->user_db, $sql_data->pass_db, $sql_data->db_name);
        $_SESSION['sql'] = json_encode($sql_data);
        return $conexion;
    }
    function sessionStartFn()
    {
        $_SESSION['loggedin'] = true;
		$_SESSION['start'] = time();
		$_SESSION['expire'] = $_SESSION['start'] + (5 * 60);
    }
    //new
    function getUserDataFn($conexion, $dni)
    {
        $con = getEmployeeByUsernameSql($conexion, $dni);
        $data = $con->fetch_array(MYSQLI_ASSOC);
        return getUserFn($data['dni'], $data['nombre'], $data['apellido1'], $data['apellido2'], $data['tipo'], $data['id']);
    }
    //new
    function buildEmployeeFn($conexion, $user)
    {
        $permissions = permissionsFn($user);
        if (in_array(19, $permissions)) {
            makeEmployeeFn($conexion, $_POST['username'], $_POST['pass'], $_POST['dni'], $_POST['nombre'], $_POST['apellido1'], $_POST['apellido2'], $_POST['tipo']);
            $_SESSION['funcion'] = 'Lista';
        }
    }
    function getUserFn($dni, $name, $surname1, $surname2, $type, $id=null)
    {
        $user = new user();
        $user->dni = $dni;
        $user->name = $name;
        $user->surname1 = $surname1;
        $user->surname2 = $surname2;
        $user->tipo = $type;
        $user->id = $id;
        return $user;
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
    function deleteEmpleadoFn($conexion)
    {
        $id_emp = $_GET['id_emp'];
        $con = getEmployeeSql($conexion, $id_emp);
        $data = $con->fetch_array(MYSQLI_ASSOC);
        $user = getUserFn($data['dni'], $data['nombre'], $data['apellido1'], $data['apellido2'], $data['tipo'], $data['id']);
        deleteEmployeeSql($conexion, $user);
        $_SESSION['funcion'] = 'Lista';
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
    //new
    function makeEmployeeFn($conexion, $username, $password, $dni, $name, $surname1, $surname2, $type)
    {
        $credentials = new credentials($username, $password);
        $con = getEmployeeByUsernameSql($conexion, $dni);
        if ($data = $con->num_rows >0) 
        {
            //update
            $olduser = getUserDataFn($conexion, $dni);
            $user = buildEmployeeFn($dni, $name, $surname1, $surname2, $type, $olduser->id);
            insertEmployee2Sql($conexion, $user);
            insertCredentials2Sql($conexion, $credentials, $olduser->id);
        } 
        else 
        {
            //insert
            $usertmp = buildEmployeeFn($dni, $name, $surname1, $surname2, $type, null);
            insertEmployee($conexion, $usertmp);
            $user = getUserDataFn($conexion, $dni);
            insertCredentialsSql($conexion, $credentials, $user->id);
        }
        return $user;
    }
    //new
    function filterFn($incidences)
    {
        return array_filter($incidences, function($array) {
            switch ($_SESSION['funcion']) {
                case 'getOtherOldIncidences':
                    return ($array->solver->dni == $_GET['dni'] && $array->state == 3);
                case 'getOtherIncidences':
                    return ($array->solver->dni == $_GET['dni'] && $array->state == 2);
                case 'getNewIncidences':
                    return ($array->state == 1);
                case 'getOwnOldIncidences':
                    return ($array->owner->dni == $_GET['dni'] && $array->state == 3);
                case 'getOwnIncidences':
                    return ($array->owner->dni == $_GET['dni'] && $array->state == 2);
                case 'getOwnNewIncidences':
                    return ($array->owner->dni == $_GET['dni'] && $array->state == 1);
                case 'getOwnHiddenIncidences':
                    return ($array->owner->dni == $_GET['dni'] && $array->state == 4);
                default:
                    break;
            }
        });
    }
    //new
    function showFn($new_array)
    {
        header('Content-Type: application/json');
        echo json_encode($new_array);
        exit();
    }
    //new
    function addEmployeeFn()
    {
        $conexion = connectionFn();
        $json = file_get_contents('php://input');
        $obj = json_decode($json);
        $user = makeEmployeeFn($conexion, $obj->username, $obj->password, $obj->dni, $obj->name, $obj->surname1, $obj->surname2, $obj->type);
        showFn($user);
    }
    function permissionsFn($user)
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
        //Permiso 22 -> Ocultar parte propio cerrado

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
            $permissions[7] = 22;
		}

		return $permissions;
    }
?>