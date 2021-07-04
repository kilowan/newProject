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
    switch ($funcion) {
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
        case 'addIncidence':
            showFn(addIncidenceFn(getPostData()));
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
        case 'showIncidence':
            showFn(showIncidenceFn($_GET['incidenceId'], $_GET['userId']));
            break;
        case 'hideIncidence':
            showFn(hideIncidenceFn($_GET['incidenceId'], $_GET['userId']));
            break;
        case 'updateNotes':
            $obj = getPostData();
            showFn(updateNoteFn($obj->incidenceDesc, $obj->incidenceId, $obj->employeeId));
            break;
        case 'insertemployeeNote':
            $obj = getPostData();
            insertemployeeNoteFn($obj->NoteDesc, $obj->incidencesId, $obj->userId);
            break;
        case 'inserttechnicianNote':
            $obj = getPostData();
            showFn(inserttechnicianNoteFn($obj->NoteDesc, $obj->incidencesId, $obj->userId));
            break;
        case 'deleteIncidence':
            showFn(deleteIncidenceFn($_GET['incidenceId'], $_GET['userId']));
            break;
        case 'updateIncidence':
            $obj = getPostData();
            showFn(updateIncidenceFn($obj->incidenceId, $obj->userId, $obj->note, $obj->pieces, $obj->close));
            break;
        default:
            break;
    }

    function checkMethod($permitted, $used)
    {
        if ($permitted !== $used) {
            return false;
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