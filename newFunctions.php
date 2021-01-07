<?php
include 'sql.php';
include 'classes.php';
    function makeEmployeeFn($conexion, $username, $password, $dni, $name, $surname1, $surname2, $type)
    {
        $credentials = new credentials($username, $password);
        $con = getEmployeeByUsernameSql($conexion, $dni);
        if ($con->num_rows >0)
        {
            //update
            $olduser = getUserDataFn($conexion, $dni);
            $user = updateEmployeeFn($conexion, $dni, $name, $surname1, $surname2, $type, $olduser);
            insertCredentials2Sql($conexion, $credentials, $olduser->id);
        } 
        else 
        {
            //insert
            $usertmp = getUserFn($dni, $name, $surname1, $surname2, $type, null);
            insertEmployeeSql($conexion, $usertmp);
            $user = getUserDataFn($conexion, $dni);
            insertPermissionsFn($user);
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
                $note = new note();
                $note->noteStr = $notes['noteStr'];
                $note->date = $notes['date'];
                $noteList[$count] = $note;
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
            $incidence->id = $fila['id_part'];
            $incidences[$incidence_count] = $incidence;
            $incidence_count++;
        }
        return $incidences;
    }
    function getEmpolyeeListFn()
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
    function getEmployeeByIdFn()
    {
        $conexion = connectionFn();
        $users = getEmpolyeeListFn();
        $new_array = array_filter($users, function($array) {
            return ($array->id == $_GET['id']);
        });
        return array_pop($new_array);
    }
    function getIncidenceByIdFn()
    {
        $conexion = connectionFn();
        $incidences = getIncidencesListFn();
        $new_array = array_filter($incidences, function($array) {
            return ($array->id == $_GET['id_part']);
        });
        return array_pop($new_array);
    }
    function getEmployeeByDniFn()
    {
        $conexion = connectionFn();
        $users = getEmpolyeeListFn();
        $new_array = array_filter($users, function($array) {
            return ($array->dni == $_GET['dni']);
        });
        return array_pop($new_array);
    }
    function removeEmployeeFn()
    {
        $conexion = connectionFn();
        $user = getEmployeeByIdFn();
        deleteEmployeeSql($conexion, $user);
        return $user;
    }
    function addEmployeeFn($username, $password, $dni, $name, $surname1, $surname2, $type)
    {
        $conexion = connectionFn();
        return makeEmployeeFn($conexion, $username, $password, $dni, $name, $surname1, $surname2, $type);
    }
    function getPermissionsFn()
    {
        $conexion = connectionFn();
        $user = getEmployeeByIdFn();
        $con = getPermissionsSql($conexion, $user);
        $permission = 0;
        $permissions = null;
        while ($fila = $con->fetch_array(MYSQLI_ASSOC)) 
        {
            $permissions[$permission] = $fila['permission'];
            $permission++;
        }
        return $permissions;
    }
    function updateEmployeeFn($conexion, $dni, $name, $surname1, $surname2, $type, $olduser)
    {
        $user = getUserFn($dni, $name, $surname1, $surname2, $type, $olduser->id);
        insertEmployee2Sql($conexion, $user);
        return $user;
    }

    function insertPermissionsFn($user)
    {
        $permissions = null;
        switch ($user->tipo) {
            case 'Tecnico':
                $permissions = [1,2,3,4,5,18,21];
                break;

            case 'Admin':
                $permissions = [1,2,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21];
                break;
            
            default:
                $permissions = [1,6,7,8,9,13,14,22];
                break;
        }
        $conexion = connectionFn();
        foreach ($permissions as $permission) {
            insertPermissionsSql($conexion, $user, $permission);
        }
        return $permissions;
    }
?>