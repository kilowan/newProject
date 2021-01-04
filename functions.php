<?php
include 'classes.php';
include 'sql.php';
include 'html.php';
    define('OneMonth', 2592000);
    define('OneWeek', 604800);
    define('OneDay', 86400);
    define('OneHour', 3600);
    define('OneMinute', 60);
    function SecondsToTime($seconds)
    { 
        $num_units = setNumUnits($seconds);
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
    function setNumUnits($seconds)
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
    function check($input)
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
                $nums[1] = countPartes($conexion, $user->id);
            }
            else
            {
                //Lista de partes (admin)
                $nums[0] = countNewPartes($conexion);
                $nums[1] = countPartes($conexion, $user->id);
                $nums[2] = countOwnPartes($conexion, $user->dni);
            }
        }
        return $nums;
    }
    function updateNotes($conexion, $user)
    {
        $id_part = $_POST['id_part'];
		$inf_part = $_POST['inf_part'];
        insertNote($id_part, $user, $inf_part);
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
                $_SESSION['funcion'] = 'Partes';
                break;
    
            case 'Ocultar_parte':
                //Ocultar parte cerrado
                $id_part = $_GET['id_part'];
                hideParte($conexion, $user, $id_part);
                $_SESSION['funcion'] = 'Partes';
                break;
    
            case 'Mostrar_parte':
                //Mostrar parte oculto
                $id_part = $_GET['id_part'];
                showHiddenParte($conexion, $id_part);
                $_SESSION['funcion'] = 'Partes'; 
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
            case 'Actualizar_parte':
                updateNotes($conexion, $user);
                break;
            case 'Crear_empleado':
                buildEmployee($conexion, $user);
                break;
            case 'insertparte':
                updateParte($conexion, $user);
                break;
            case 'cierraparte':
                closeParte($conexion, $user);
                break;
            case 'Crear_parte':
                buildParte($conexion);
                break;
            case 'Borrar_empleado':
                deleteEmpleado($conexion);
                break;
            case 'Editar_empleado':
                updateEmpleado($conexion);
                break;
            case 'Logout':
                logout();
                break;
            case 'Login':
                login();
                break;
            case 'Actualizar_empleado':
                Actualizar_empleado($conexion);
                break;
            default:
                break;
        }
        return $response;
    }
    function connection()
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
    function sessionStart()
    {
        $_SESSION['loggedin'] = true;
		$_SESSION['start'] = time();
		$_SESSION['expire'] = $_SESSION['start'] + (5 * 60);
    }
    function getUserData($conexion, $dni)
    {
        $con = getEmployeeByUsername($conexion, $dni);
        $data = $con->fetch_array(MYSQLI_ASSOC);
        return getUser($data['dni'], $data['nombre'], $data['apellido1'], $data['apellido2'], $data['tipo'], $data['id']);
    }
    function buildEmployee($conexion, $user)
    {
        $permissions = permissions($user);
        if (in_array(19, $permissions)) {
            //datos
            $credentials = new credentials($_POST['username'], $_POST['pass']);
            $_POST['credentials'] = json_encode($credentials);
            $con = getEmployeeByUsername($conexion, $_POST['dni']);
            if ($data = $con->num_rows >0) 
            {
                //update
                $olduser = getUserData($conexion, $_POST['dni']);
                $user2 = getUser($_POST['dni'], $_POST['nombre'], $_POST['apellido1'], $_POST['apellido2'], $_POST['type'], $olduser->id);
                insertEmployee2($conexion, $user2);
                insertCredentials2($conexion, $credentials, $user2->id);
            }
            else 
            {
                //insert
                $usertmp = buildEmployee($obj->dni, $obj->name, $obj->surname1, $obj->surname2, $obj->type, null);
                insertEmployee($conexion, $usertmp);
                $user2 = getUserData($conexion, $obj->dni);
                insertCredentials($conexion, $credentials, $user2->id);
            }
            $_SESSION['funcion'] = 'Lista';
        }
    }
    function getUser($dni, $name, $surname1, $surname2, $type, $id=null)
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
    function closeParte($conexion, $user)
    {
        $id_part = $_POST['id_part'];
        $not_tec = $_POST['not_tec'];
        $pieza = $_POST['pieza'];
        if($pieza == '--')
        {
            closeParte1($conexion, $id_part, $user);
        }
        else
        {
            closeParte2($conexion, $pieza, $id_part, $user);
        }
        updateNoteList($conexion, $user, $id_part, $not_tec);
        $_SESSION['funcion'] = 'Partes';
    }
    function updateParte($conexion, $user)
    {
        $id_part = $_POST['id_part'];
        $not_tec = $_POST['not_tec'];
        $pieza = $_POST['pieza'];
        if($pieza == '--')
        {
            updateParte1($conexion, $id_part, $user);
        }
        else
        {
            updateparte2($conexion, $pieza, $id_part, $user);
        }
        updateNoteList($conexion, $user, $id_part, $not_tec);
        $_SESSION['funcion'] = 'Partes';
    }
    function buildParte($conexion)
    {
		$pieza = $_POST['pieza'];
		$descripcion = $_POST['descripcion'];
		if($pieza != "--")
		{	
            insertParte1($conexion, $user, $descripcion, $pieza);
		}
		else
		{
            insertParte2($conexion, $user, $descripcion);
		}
		$_SESSION['funcion'] = 'Partes';
    }
    function deleteEmpleado($conexion)
    {
        $id_emp = $_GET['id_emp'];
        $con = getEmployee($conexion, $id_emp);
        $data = $con->fetch_array(MYSQLI_ASSOC);
        $user = getUser($data['dni'], $data['nombre'], $data['apellido1'], $data['apellido2'], $data['tipo'], $data['id']);
        deleteEmployee($conexion, $user);
        $_SESSION['funcion'] = 'Lista';
    }
    function updateEmpleado($conexion)
    {
        $user = json_decode($_POST['user']);
        $bool = check($user->dni);
        $bool = check($user->name);
        $bool = check($user->surname1);
        $bool = check($user->surname2);
        if($bool == true)
        {
            updateEmployee($conexion, $user);
        }
        $_SESSION['funcion'] = 'Lista';
    }
    function login($username, $password)
    {
        $conexion = connection();
        if ($conexion->connect_error)
        {
            $_SESSION['mensaje'] = die("La conexión falló: " . $conexion->connect_error);
            header('Location: login.html');
        }
        else
        {
            $credentials = new credentials($username, $password);
            $con = checkCredentialsData($credentials, $conexion);
            if ($con->num_rows > 0)
            {
                $creds = $con->fetch_array(MYSQLI_ASSOC);
                sessionStart();
                $credentials->employee = $creds['employee'];
                $_SESSION['credentials'] = json_encode($credentials);
                $con = selectEmployeeData($conexion, $credentials);
                //extrae datos personales
                $fila = $con->fetch_array(MYSQLI_ASSOC);
                $user_info = getUser($fila['dni'], $fila['nombre'], $fila['apellido1'], $fila['apellido2'], $fila['tipo'], $fila['id']);
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
    function logout()
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
    function Actualizar_empleado($conexion)
    {
        $dni = $_POST['dni'];
        $nombre = $_POST['nombre'];
        $apellido1 = $_POST['apellido1'];
        $apellido2 = $_POST['apellido2'];
        $tipo = $_POST['tipo'];
        $con = getEmployeeByUsername($conexion, $dni);
        $fila = $con->fetch_array(MYSQLI_ASSOC);
        //$user = getUser($fila['dni'], $fila['nombre'], $fila['apellido1'], $fila['apellido2'], $fila['tipo'], $fila['id_emp']);
        $user->name = $_POST['nombre'];
        $user->surname1 = $_POST['apellido1'];
        $user->surname2 = $_POST['apellido2'];
        $user->tipo = $_POST['tipo'];
        $user->dni = $_POST['dni'];
        $user->id = $fila['id'];
        updateEmployee($conexion, $user);
        $_SESSION['funcion'] = 'Lista';
        //header('Location: veremp.php');
    }
?>