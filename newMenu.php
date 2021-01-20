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
            showFn(getIncidenceByIdFn($_GET['id_part']));
            break;
        case 'getEmployeeByUsername':
            showFn(getEmployeeByUsernameFn($_GET['username']));
            break;
        case 'addEmployee':
            $obj = getPostData();
            showFn(addEmployeeFn($obj->username, $obj->password, $obj->dni, $obj->name, $obj->surname1, $obj->surname2, $obj->type));
            break;
        case 'addIncidence':
            showFn(addIncidenceFn(getPostData()));
        case 'removeEmployee':
            showFn(removeEmployeeFn());
            break;
        case 'getPermissions':
            showFn(getEmployeeByIdFn($_GET['id'])->permissions);
            break;
        case 'getPieces':
            showFn(getPiecesFn($_GET['id']));
            break;
        case 'getPiecesByIds':
            showFn(getPiecesByIdsFn(getPostData()->pieces));
            break;
        case 'getPiecesList':
            showFn(getPiecesListFn());
            break;
        case 'getPieceById':
            showFn(getPieceByIdFn($_GET['id']));
            break;
        default:
            break;
    }
    function showFn($new_array)
    {
        header('Content-Type: application/json');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            die();
        }

        echo json_encode($new_array);
        exit();
    }
    function getPostData()
    {
        $json = file_get_contents('php://input');
        return json_decode($json);
    }
?>