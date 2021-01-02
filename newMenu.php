<?php
include 'sql.php';
include 'classes.php';
    if(isset($_GET['funcion']))
    {
        $funcion = $_GET['funcion'];
        session_start();
        $_SESSION['funcion'] = $_GET['funcion'];
    }
    else {
        $json = file_get_contents('php://input');
        $obj = json_decode($json);
        $funcion = $obj->funcion;
        $conexion = connection();
    }
    
    switch ($funcion) {
        case 'getEmployeeById':
            getEmployeeById();
            break;
        case 'getEmployeeByUsername':
            $conexion = connection();
            $username = $_GET['username'];
            $con = getEmployeeByUsername($conexion, $username);
            $data = $con->fetch_array(MYSQLI_ASSOC);
            $user = buildEmployee($data);
            header('Content-Type: application/json');
            echo json_encode($user);
            exit();
            break;

        case 'getAllincidences':
            show(getIncidencesList());
            break;
        case 'getOwnNewIncidences':
            show(filter(getIncidencesList()));
            break;
        case 'getOwnIncidences':
            show(filter(getIncidencesList()));
            break;
        case 'getOwnOldIncidences':
            show(filter(getIncidencesList()));
            break;
        case 'getNewIncidences':
            show(filter(getIncidencesList()));
            break;
        case 'getOtherIncidences':
            show(filter(getIncidencesList()));
            break;
        case 'getOtherOldIncidences':
            show(filter(getIncidencesList()));
            break;            
        default:
            break;
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
    function getEmployeeData($credentials)
    {
        $conexion = connection();
        $con = checkCredentialsData($credentials, $conexion);
        if ($con->num_rows > 0)
        {
            //extrae datos personales
            $con = selectEmployeeData($conexion, $credentials);
            $fila = $con->fetch_array(MYSQLI_ASSOC);
            $user_info = buildEmployee($fila);
            $_SESSION['user'] =  json_encode($user_info);
            return $user_info;
        }
        else 
        {
            echo 'error desconocido';
        }
    }
    function buildEmployee($data)
    {
        $user = new user;
        $user->dni = $data['dni'];
        $user->name = $data['nombre'];
        $user->surname1 = $data['apellido1'];
        $user->surname2 = $data['apellido2'];
        $user->tipo = $data['tipo'];
        $user->id = $data['id'];
        return $user;
    }
    function getEmployeeById()
    {
        $users = getEmpolyeeList();
        $new_array = array_filter($users, function($array) {
            return ($array->id == $_GET['id_emp']);
        });
        header('Content-Type: application/json');
        echo json_encode(array_pop($new_array));
        exit();
    }
    /*function getEmployeeByCredentials()
    {
        //$json = file_get_contents('php://input');
        //$obj = json_decode($json);
        //$credentials = new credentials($obj->username, $obj->password);
        //$_SESSION['credentials'] = json_encode($credentials);
        $users = getEmpolyeeList();
        $new_array = array_filter($users, function($array) {
            $json = file_get_contents('php://input');
            $obj = json_decode($json);
            return ($array->dni == $obj->username);
        });
        header('Content-Type: application/json');
        echo json_encode(array_pop($new_array));
        exit();
        //$user = getEmployeeData($credentials);
        /*if ($user != 'error desconocido') {
            header('Content-Type: application/json');
            echo json_encode($user);
            exit();
        }
        else {
            echo $user;
            exit();
        }
    }*/
    function getIncidencesList()
    {
        $conexion = connection();
        $con = selectIncidences($conexion);
        $incidences = null;
        $incidence_count = 0;
        while ($fila = $con->fetch_array(MYSQLI_ASSOC)) {
            $tec = new user();
            
            $noteList = null;
            $count = 0;
            $con2 = selectNotes($conexion, $fila['id_part']);
            while ($notes = $con2->fetch_array(MYSQLI_ASSOC)) {
                $noteList[$count] = $notes['noteStr'];
                $count++;
            }
            $con3 = getEmployee($conexion, $fila['emp_crea']);
            $emp1 = $con3->fetch_array(MYSQLI_ASSOC);
            $owner = buildEmployee($emp1);


            if ($fila['tec_res'] != null && $fila['tec_res'] != "") {
                $con3 = getEmployee($conexion, $fila['tec_res']);
                $emp2 = $con3->fetch_array(MYSQLI_ASSOC);
                $tec = buildEmployee($emp2);
            }

            $incidence = new incidence($owner, $fila['fecha_hora_creacion'], $fila['inf_part'], $fila['pieza'], $noteList);
            $incidence->solver = $tec;
            $incidence->finishTime = $fila['hora_resolucion'];
            $incidence->finishDate = $fila['fecha_resolucion'];
            $incidence->state = $fila['state'];
            $incidences[$incidence_count] = $incidence;
            $incidence_count++;
        }
        return $incidences;
    }
    function getEmpolyeeList()
    {
        $conexion = connection();
        $con = getAllEmployeeData($conexion);
        $employees = null;
        $employee_count = 0;
        while ($fila = $con->fetch_array(MYSQLI_ASSOC)) 
        {
            $employee = buildEmployee($fila);
            $employees[$employee_count] = $employee;
            $employee_count++;
        }
        return $employees;
    }
    function show($new_array)
    {
        header('Content-Type: application/json');
        echo json_encode($new_array);
        exit();
    }
    function filter($incidences)
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
                default:
                    break;
            }
        });
    }
?>