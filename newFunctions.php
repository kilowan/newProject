<?php
include 'sql.php';
include 'classes.php';
    function makeEmployeeFn($conexion, $username, $password, $dni, $name, $surname1, $surname2, $type)
    {
        $credentials = new credentials($username, $password);
        $con = getEmployeeByUsernameSql($conexion, $dni);
        if ($data = $con->num_rows >0) 
        {
            //update
            $olduser = getUserDataFn($conexion, $dni);
            $user = getUserFn($dni, $name, $surname1, $surname2, $type, $olduser->id);
            insertEmployee2Sql($conexion, $user);
            insertCredentials2Sql($conexion, $credentials, $olduser->id);
        } 
        else 
        {
            //insert
            $usertmp = getUserFn($dni, $name, $surname1, $surname2, $type, null);
            insertEmployee($conexion, $usertmp);
            $user = getUserDataFn($conexion, $dni);
            insertCredentialsSql($conexion, $credentials, $user->id);
        }
        return $user;
    }
    function filterFn($incidences)
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
                case 'getOwnHiddenIncidences':
                    return ($array->owner->dni == $_GET['dni'] && $array->state == 4);
                default:
                    break;
            }
        });
    }
    function getUserDataFn($conexion, $dni)
    {
        $con = getEmployeeByUsernameSql($conexion, $dni);
        $data = $con->fetch_array(MYSQLI_ASSOC);
        return getUserFn($data['dni'], $data['nombre'], $data['apellido1'], $data['apellido2'], $data['tipo'], $data['id']);
    }
    function connectionFn()
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
    function sessionStartFn()
    {
        $_SESSION['loggedin'] = true;
        $_SESSION['start'] = time();
        $_SESSION['expire'] = $_SESSION['start'] + (5 * 60);
    }
    function getUserFn($dni, $name, $surname1, $surname2, $type, $id=null)
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
    function getEmployeeByUsernameFn($username)
    {
        $conexion = connectionFn();
        $con = getEmployeeByUsernameSql($conexion, $username);
        $data = $con->fetch_array(MYSQLI_ASSOC);
        return getUserFn($data['dni'], $data['nombre'], $data['apellido1'], $data['apellido2'], $data['tipo'], $data['id']);
    }
?>