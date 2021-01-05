<?php
include 'newFunctions.php';
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
        $conexion = connectionFn();
    }
    
    switch ($funcion) {
        case 'getEmployeeById':
            showFn(getEmployeeById());
            break;
        case 'getEmployeeByDni':
            showFn(getEmployeeByDni());
            break;

        case 'getAllincidences':
            showFn(getIncidencesListFn());
            break;
        case 'getOwnNewIncidences':
            showFn(filterFn(getIncidencesListFn()));
            break;
        case 'getOwnIncidences':
            showFn(filterFn(getIncidencesListFn()));
            break;
        case 'getOwnOldIncidences':
            showFn(filterFn(getIncidencesListFn()));
            break;
        case 'getOwnHiddenIncidences':
            showFn(filterFn(getIncidencesListFn()));
            break;
        case 'getNewIncidences':
            showFn(filterFn(getIncidencesListFn()));
            break;
        case 'getOtherIncidences':
            showFn(filterFn(getIncidencesListFn()));
            break;
        case 'getOtherOldIncidences':
            showFn(filterFn(getIncidencesListFn()));
            break;
        case 'addEmployee':
            addEmployeeFn();
            break;
        case 'removeEmployee':
            removeEmployee();
            break;
        default:
            break;
    }
    function getEmployeeById()
    {
        $conexion = connectionFn();
        $users = getEmpolyeeList();
        $new_array = array_filter($users, function($array) {
            return ($array->id == $_GET['id']);
        });
        return array_pop($new_array);
    }
    function getEmployeeByDni()
    {
        $conexion = connectionFn();
        $users = getEmpolyeeList();
        $new_array = array_filter($users, function($array) {
            return ($array->dni == $_GET['dni']);
        });
        return array_pop($new_array);
    }
    function getIncidencesListFn()
    {
        $conexion = connectionFn();
        $con = selectIncidencesSql($conexion);
        $incidences = null;
        $incidence_count = 0;
        while ($fila = $con->fetch_array(MYSQLI_ASSOC)) {
            $tec = new user();
            
            $noteList = null;
            $count = 0;
            $con2 = selectNotesSql($conexion, $fila['id_part']);
            while ($notes = $con2->fetch_array(MYSQLI_ASSOC)) {
                $noteList[$count] = $notes['noteStr'];
                $count++;
            }
            $con3 = getEmployeeSql($conexion, $fila['emp_crea']);
            $emp1 = $con3->fetch_array(MYSQLI_ASSOC);
            $owner = getUserFn($emp1['dni'], $emp1['nombre'], $emp1['apellido1'], $emp1['apellido2'], $emp1['tipo'], $emp1['id']);


            if ($fila['tec_res'] != null && $fila['tec_res'] != "") {
                $con3 = getEmployeeSql($conexion, $fila['tec_res']);
                $emp2 = $con3->fetch_array(MYSQLI_ASSOC);
                $tec = getUserFn($emp2['dni'], $emp2['nombre'], $emp2['apellido1'], $emp2['apellido2'], $emp2['tipo'], $emp2['id']);
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
        $conexion = connectionFn();
        $con = getAllEmployeeDataSql($conexion);
        $employees = null;
        $employee_count = 0;
        while ($fila = $con->fetch_array(MYSQLI_ASSOC)) 
        {
            $employee = getUserFn($fila['dni'], $fila['nombre'], $fila['apellido1'], $fila['apellido2'], $fila['tipo'], $fila['id']);
            $employees[$employee_count] = $employee;
            $employee_count++;
        }
        return $employees;
    }
    function removeEmployee()
    {
        $id = $_GET['id'];
        $conexion = connectionFn();
        $user = getEmployeeById();
        deleteEmployeeSql($conexion, $user);
        showFn($user);
    }
    function showFn($new_array)
    {
        header('Content-Type: application/json');
        echo json_encode($new_array);
        exit();
    }
    function addEmployeeFn()
    {
        $conexion = connectionFn();
        $json = file_get_contents('php://input');
        $obj = json_decode($json);
        $user = makeEmployeeFn($conexion, $obj->username, $obj->password, $obj->dni, $obj->name, $obj->surname1, $obj->surname2, $obj->type);
        showFn($user);
    }
?>