<?php
include 'classes.php';
include 'sql.php';
include 'html.php';
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
                buildEmployee($conexion);
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
                deleteEmpleado($conexion, $user);
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
            case 'service01':
                service01();
                break;
            case 'service02':
                service02();
                break;
            default:
                break;
        }
        return $response;
    }
    function connnection()
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
    function getEmployeeData($credentials)
    {
        $conexion = connnection();
        $con = checkCredentialsData($credentials, $conexion);
        if ($con->num_rows > 0)
        {
            //extrae datos personales
            $fila = $con->fetch_array(MYSQLI_ASSOC);
            $user_info = getEmployee($fila);
            $_SESSION['user'] =  json_encode($user_info);
            return $user_info;
        }
        else 
        {
            echo 'error desconocido';
        }
    }
    function takeEmployee($conexion, $emp_crea)
    {
        $con = selectEmpleado($conexion, $emp_crea);
        $data = $con->fetch_array(MYSQLI_ASSOC);
        return getEmployee($data);
    }
    function buildEmployee($conexion)
    {
        $permissions = permissions($user);
        if (in_array(19, $permissions)) {
            $user = new user();
            $user->dni = $_POST['dni'];
            $user->name = $_POST['nombre'];
            $user->surname1 = $_POST['apellido1'];
            $user->surname2 = $_POST['apellido2'];
            $_POST['user'] = json_encode($user);
            $user->tipo = $_POST['tipo'];
            $credentials = new credentials($_POST['dni'], $_POST['pass']);
            $_POST['credentials'] = json_encode($credentials);
            insertEmployee($conexion, $user);
            $con = selectEmployee2($conexion, $user);
            $data = $con->fetch_array(MYSQLI_ASSOC);
            $id = $data['id'];
            insertCredentials($conexion, $credentials, $id);
            $_SESSION['funcion'] = 'Lista';
        }
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
    function deleteEmpleado($conexion, $user)
    {
        $id_emp = $_GET['id_emp'];
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
    function login()
    {
        $conexion = connnection();
        if ($conexion->connect_error)
        {
            $_SESSION['mensaje'] = die("La conexión falló: " . $conexion->connect_error);
            header('Location: login.html');
        }
        else
        {
            $credentials = new credentials($_POST['username'], $_POST['password']);
            $_SESSION['credentials'] = json_encode($credentials);
            $con = checkCredentialsData($credentials, $conexion);
            if ($con->num_rows > 0)
            {
                sessionStart();
                $con = selectEmployeeData($conexion, $credentials);
                //extrae datos personales
                $fila = $con->fetch_array(MYSQLI_ASSOC);
                $user_info = getEmployee($fila);
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
    function service01()
    {
        session_start();
        $conexion = connnection();
        $emp_crea = $_GET['id_emp'];
        $user = takeEmployee($conexion, $emp_crea);
        header('Content-Type: application/json');
        echo json_encode($user);
        exit();
    }
    function service02()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json);
        //if ($obj->funcion == 'service02') {
        $credentials = new credentials($obj->username, $obj->password);
        $_SESSION['credentials'] = json_encode($credentials);
        $user = getEmployeeData($credentials);
        if ($user != 'error desconocido') {
            header('Content-Type: application/json');
            echo json_encode($user);
            exit();
        }
        else {
            echo $user;
            exit();
        }
        //}
    }
?>