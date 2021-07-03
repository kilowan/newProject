<?php
include 'newFunctions.php';
    $obj = '';
    $funcion = '';
    $conexion = connectionFn();
    $method = $_SERVER['REQUEST_METHOD'];
    switch ($method) {
        case 'POST':
            $json = file_get_contents('php://input');
            $obj = json_decode($json);
            $funcion = $obj->funcion;
            break;
        case 'GET':
            $obj = $_GET;
            $funcion = $obj['funcion'];
            break;
        case 'DELETE':
            $obj = $_GET;
            $funcion = $obj['funcion'];
            break;
        case 'PUT':
            $json = file_get_contents('php://input');
            $obj = json_decode($json);
            $funcion = $obj->funcion;
            break;
        default:
            break;
    }
    if(checkMethod('GET', $funcion) == false) {
        echo checkMethod('GET', $funcion);
        exit();
    } else {
        switch ($funcion) {
            case 'getEmployeeById':
                showFn(getEmployeeByIdFn($obj['id']));
                break;
            case 'getEmployeeByUsername':
                showFn(getEmployeeByUsernameFn($_GET['username']));
                break;
            case 'addEmployee':
                showFn(addEmployeeFn($obj->username, $obj->password, $obj->dni, $obj->name, $obj->surname1, $obj->surname2, $obj->type));
                break;
            case 'removeEmployee':
                showFn(removeEmployeeFn());
                break;
            case 'getPermissions':
                showFn(getEmployeeByIdFn($obj['id'])->permissions);
                break;
            case 'checkCredentials':
                showFn(checkCredentialsFn($obj['username'], $obj['pass']));
                break;
            case 'getStatistics':
                showFn(getStatisticsFn($obj['id']));
                break;
            case 'getReportedPieces':
                showFn(getReportedPiecesFn());
                break;
            case 'getGlobalStatistics':
                showFn(getGlobalStatisticsFn());
                break;
            case 'getEmpolyeeList':
                showFn(getEmpolyeeListFn());
                break;
            case 'updateWorker':
                showFn(updateWorker($obj->fields, $obj->values, $obj->dni));
                break;
            default:
                break;
        }
    }

    function checkMethod($permitted, $used)
    {
        if ($permitted !== $used) {
            return 'Method Not allowed';
        } else {
            return true;
        }
    }
    function showFn($new_array)
    {
        header('content-type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            die();
        }

        echo json_encode($new_array);
        exit();
    }
?>