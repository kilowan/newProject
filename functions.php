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
    function checkLoging(string $username, string $password, $conexion)
    {
        $response = false;
        $credentials = new credentials($username, $password);
        $con = checkCredentials($credentials, $conexion);
        if ($con->num_rows > 0)
        {
            $_SESSION['loggedin'] = true;
            $_SESSION['start'] = time();
            $_SESSION['expire'] = $_SESSION['start'] + (5 * 60);
            $response = true;
        }
        return $response;
    }
    function getEmployeeData($credentials)
    {
        $conexion = connnection();
        if(checkLoging($credentials->username, $credentials->password, $conexion))
        {
            $user_info = new user;
            $user_info->dni = $credentials->username;
            $con = $conexion->query("SELECT * 
            FROM Empleados 
            WHERE dni='$credentials->username'");

            //extrae datos personales
            $fila = $con->fetch_array(MYSQLI_ASSOC);
            $user_info->tipo = $fila['tipo'];
            $user_info->comName = $fila['nombre']." ".$fila['apellido1']." ".$fila['apellido2'];
            $user_info->id = $fila['id'];
            $user_info->name = $fila['nombre'];
            $user_info->surname1 = $fila['apellido1'];
            $user_info->surname2 = $fila['apellido2'];
            $_SESSION['user'] =  json_encode($user_info);
            return $user_info;
        }
        else 
        {
            echo 'error desconocido';
        }
    }
    function takeEmployee($emp_crea)
    {
        $sql_data = new sql;
        $sql_data->host_db = "localhost";
        $sql_data->user_db = "Ad";
        $sql_data->pass_db = "1234";
        $sql_data->db_name = "Fabrica";
        $conexion = new mysqli($sql_data->host_db, $sql_data->user_db, $sql_data->pass_db, $sql_data->db_name);
        $con = $conexion->query("SELECT nombre, apellido1, apellido2, tipo, dni FROM Empleados WHERE id = $emp_crea");
        $data = $con->fetch_array(MYSQLI_ASSOC);
        $user = new user;
        $user->name = $data['nombre'];
        $user->surname1 = $data['apellido1'];
        $user->surname2 = $data['apellido2'];
        $user->comName = $data['nombre'].' '.$data['apellido1'].' '.$data['apellido2'];
        $user->dni = $data['dni'];
        $user->tipo = $data['tipo'];
        $user->id = $emp_crea;
        return $user;
    }
	if (!isset($_GET['funcion'])) {
        $funcion = "";
    } else {
        $funcion = $_GET['funcion'];
    }
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && $funcion == 'GET')
    {
        $emp_crea = $_GET['id_emp'];
        $user = takeEmployee($emp_crea);
        header('Content-Type: application/json');
        echo json_encode($user);
        exit();
    }
    else if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json);
        if ($obj->funcion == 'TAKE') {
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
        }
    }
?>