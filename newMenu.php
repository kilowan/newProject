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
            showFn(getEmployeeByIdFn($_GET['id']));
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
        case 'getIncidenceById':
            showFn(getIncidenceByIdFn());
            break;
        case 'getEmployeeByUsername':
            showFn(getEmployeeByUsernameFn($_GET['username']));
            break;
        case 'addEmployee':
            $json = file_get_contents('php://input');
            $obj = json_decode($json);
            showFn(addEmployeeFn($obj->username, $obj->password, $obj->dni, $obj->name, $obj->surname1, $obj->surname2, $obj->type));
            break;
        case 'removeEmployee':
            showFn(removeEmployeeFn());
            break;
        case 'getPermissions':
            showFn(getEmployeeByIdFn($_GET['id'])->permissions);
            break;
        default:
            break;
    }
    function showFn($new_array)
    {
        header('Content-Type: application/json');
        echo json_encode($new_array);
        exit();
    }
?>