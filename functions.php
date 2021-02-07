<?php
include 'newFunctions.php';
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
    function structureFn($permissions)
    {        
        $partes = 0;
        $incidences = getIncidencesListFn();
        if(in_array(7, $permissions) && in_array(8, $permissions) && in_array(9, $permissions))
        {
            $new_array = array_filter($incidences, function($array) {
                return ($array->owner->id == $_GET['id']);
            });
            $partes = count($new_array);
        }
        else if (in_array(3, $permissions) && in_array(4, $permissions) && in_array(5, $permissions))
        {
            $new_array = array_filter($incidences, function($array) {
                return ($array->solver->id == $_GET['id'] || $array->state == 1);
            });
            $partes = count($new_array);
        }
        else if (in_array(6, $permissions) && in_array(7, $permissions) && in_array(8, $permissions) && in_array(9, $permissions) && in_array(10, $permissions) && in_array(11, $permissions) && in_array(12, $permissions)) 
        {
            $new_array = array_filter($incidences, function($array) {
                return ($array->solver->id == $_GET['id'] || ($array->state == 1 || $array->state == 2 || $array->state == 3 || $array->state == 4) || $array->owner->id == $_GET['id']);
            });
            $partes = count($new_array);
        }
        return $partes;
    }
    function updateNotesFn($conexion)
    {
        $id_part = $_POST['id_part'];
		$inf_part = $_POST['inf_part'];
        updateIncidence($conexion, $inf_part, $id_part);
    }
    function buildEmployeeFn($conexion, $user)
    {
        $_GET['id'] = $user->id;
        if (in_array(19, $user->permissions)) {
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
    function buildParteFn($conexion, $user)
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
                $_SESSION['var'] = $creds['employee'];
                sessionStartFn();
                $credentials->employee = $creds['employee'];
                $users = getEmpolyeeListFn();
                $new_array = array_filter($users, function($array) 
                {
                    return ($array->id == $_SESSION['var']);
                });
                $user_info = array_pop($new_array);
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
        $olduser = getEmployeeByUsernameFn($_POST['dni']);
        updateEmployeeFn($conexion, $fila['dni'], $_POST['nombre'], $_POST['apellido1'], $_POST['apellido2'], $_POST['tipo'], $olduser);
        $_SESSION['funcion'] = 'Lista';
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