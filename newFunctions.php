<?php
include 'sql.php';
include 'classes.php';
    function makeEmployeeFn($conexion, $username, $password, $dni, $name, $surname1, $surname2, $type)
    {
        $credentials = new credentials($username, $password);
        $olduser = getEmployeeByUsernameFn($username);
        

        if (count($olduser) >0)
        {
            //update
            $user = updateEmployeeFn($conexion, $dni, $name, $surname1, $surname2, $type, $olduser);
            updateCredentialsSql($conexion, $credentials, $olduser->id);
        } 
        else 
        {
            //insert
            $usertmp = getUserFn($dni, $name, $surname1, $surname2, $type, null, 0, null);
            insertEmployeeSql($conexion, $usertmp, $credentials, setPermissionsFn($type));
            $user = getEmployeeByUsernameFn($dni);
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
    function getUserFn($dni, $name, $surname1, $surname2, $type, $permissions = null, $borrado = 0, $id=null)
    {
        $user = new user();
        $user->dni = $dni;
        $user->name = $name;
        $user->surname1 = $surname1;
        $user->surname2 = $surname2;
        $user->tipo = $type;
        $user->id = $id;
        $user->permissions = $permissions;
        $user->borrado = $borrado;
        return $user;
    }
    function getEmployeeByUsernameFn($username)
    {
        $empty = [];
        $users = getEmpolyeeListFn();
        $_SESSION['var'] = $username;
        $users = getEmpolyeeListFn();
        $new_array = array_filter($users, function($array) {
            return ($array->dni == $_SESSION['var']);
        });

        if (count($new_array) == 0 || $new_array == null) {
            return $empty;
        }
        else 
        {
            return array_pop($new_array);
        }
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
            $owner = getEmployeeByIdFn($fila['emp_crea']);


            if ($fila['tec_res'] != null && $fila['tec_res'] != "") {
                $tec = getEmployeeByIdFn($fila['tec_res']);
            }
            $pieces = getPiecesFn($fila['id_part']);

            $incidence = makeIncidenceFn($owner, $fila['inf_part'], $pieces, $noteList, $fila['state'], $tec, $fila['fecha_hora_creacion'], $fila['hora_resolucion'], $fila['fecha_resolucion'], $fila['id_part']);
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
            $permissions = getPermissionsFn($fila['id']);
            $employee = getUserFn($fila['dni'], $fila['nombre'], $fila['apellido1'], $fila['apellido2'], $fila['tipo'], $permissions, $fila['borrado'], $fila['id']);
            $employees[$employee_count] = $employee;
            $employee_count++;
        }
        return $employees;
    }
    function getEmployeeByIdFn($id)
    {
        $_SESSION['var'] = $id;
        $users = getEmpolyeeListFn();
        $new_array = array_filter($users, function($array) {
            return ($array->id == $_SESSION['var']);
        });
        return array_pop($new_array);
    }
    function getIncidenceByIdFn($id)
    {
        $conexion = connectionFn();
        $incidences = getIncidencesListFn();
        $_SESSION['var'] = $id;
        $new_array = array_filter($incidences, function($array) {
            return ($array->id == $_SESSION['var']);
        });
        return array_pop($new_array);
    }
    function removeEmployeeFn()
    {
        $conexion = connectionFn();
        $user = getEmployeeByIdFn($_GET['id']);
        deleteEmployeeSql($conexion, $user);
        return $user;
    }
    function addEmployeeFn($username, $password, $dni, $name, $surname1, $surname2, $type)
    {
        $conexion = connectionFn();
        return makeEmployeeFn($conexion, $username, $password, $dni, $name, $surname1, $surname2, $type);
    }
    function getPermissionsFn($id)
    {
        $conexion = connectionFn();
        $con = getPermissionsSql($conexion, $id);
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
        $user = getUserFn($dni, $name, $surname1, $surname2, $type, setPermissionsFn($type), 0, $olduser->id);
        updateEmployeeSql($conexion, $user, setPermissionsFn($type));
        return $user;
    }
    function setPermissionsFn($tipo)
    {
        $permissions = null;
        switch ($tipo) {
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
        return $permissions;
    }
    function getPiecesFn($id)
    {
        $conexion = connectionFn();
        $con = getPiecesSql($conexion, $id);
        $pieces = null;
        $counter = 0;
        while ($fila = $con->fetch_array(MYSQLI_ASSOC))
        {
            $piece = new piece();
            $piece->id = $fila['piece'];
            $piece->name = $fila['piece_name'];
            $piece->price = $fila['price'];
            $piece->description = $fila['piece_description'];
            $piece->type = new pieceType();
            $piece->type->name = $fila['piece_type_name'];
            $piece->type->description = $fila['piece_type_description'];
            $pieces[$counter] = $piece;
            $counter++;
        }
        return $pieces;
    }
    function getPieceByIdFn($id)
    {
        $conexion = connectionFn();
        $con = getPieceByIdSql($conexion, $id);
        $fila = $con->fetch_array(MYSQLI_ASSOC);
        $piece = makePiece($fila['id'], $fila['name'], $fila['price'], $fila['description']);
        $con2 = getPieceTypeSql($conexion, $fila['type']);
        $fila2 = $con2->fetch_array(MYSQLI_ASSOC);
        $type = makePieceType($fila2['name'], $fila2['description']);
        $piece->type = $type;
        return $piece;
    }
    function getPiecesByIdsFn($pieces)
    {
        $conexion = connectionFn();
        $counter = 0;
        foreach ($pieces as $piece) {
            $con = getPieceByIdSql($conexion, $piece);
            $fila = $con->fetch_array(MYSQLI_ASSOC);
            $piece = makePiece($fila['id'], $fila['name'], $fila['price'], $fila['description']);
            $con2 = getPieceTypeSql($conexion, $fila['type']);
            $fila2 = $con2->fetch_array(MYSQLI_ASSOC);
            $type = makePieceType($fila2['name'], $fila2['description']);
            $piece->type = $type;
            $pieces[$counter] = $piece;
            $counter++;
        }
        return $pieces;
    }
    function getPiecesListFn()
    {
        $conexion = connectionFn();
        $con = getPiecesListSql($conexion);
        $pieces = null;
        $counter = 0;
        while ($fila = $con->fetch_array(MYSQLI_ASSOC))
        {
            $piece = makePiece($fila['id'], $fila['name'], $fila['price'], $fila['description']);
            $con2 = getPieceTypeSql($conexion, $fila['type']);
            $fila2 = $con2->fetch_array(MYSQLI_ASSOC);
            $type = makePieceType($fila2['name'], $fila2['description']);
            $piece->type = $type;
            $pieces[$counter] = $piece;
            $counter++;
        }
        return $pieces;
    }
    function addIncidenceFn($obj)
    {
        $conexion = connectionFn();
        $owner = getEmployeeByIdFn($obj->ownerId);
        $id = insertIncidenceSql($conexion, $owner, $obj->issueDesc);
        insertPiecesSql($conexion, $obj->pieces, $id);
        return getIncidenceByIdFn($id);
    }
    function makeIncidenceFn($owner, string $info, $pieces, $noteList = null, ?int $state = 1, $tec = null, $init_date = null, $finishTime = null, $finishDate = null, ?int $id = null)
    {
        $incidence = new incidence($owner, $init_date, $info, $pieces, $noteList);
        $incidence->solver = $tec;
        $incidence->finishTime = $finishTime;
        $incidence->finishDate = $finishDate;
        $incidence->state = $state;
        $incidence->id = $id;
        $incidence->pieces = $pieces;
        return $incidence;
    }
    function makePiece(int $id, string $name, $price, string $description, $pieceType = null)
    {
        $piece = new piece();
        $piece->id = $id;
        $piece->name = $name;
        $piece->price = $price;
        $piece->description = $description;
        $piece->type = $pieceType;
        return $piece;
    }
    function makePieceType(string $name, string $description)
    {
        $type = new pieceType();
        $type->name = $name;
        $type->description = $description;
        return $type;
    }
    function checkCredentialsFn($username, $password)
    {
        $conexion = connectionFn();
        $credentials = new credentials($username, $password);
        $con = checkCredentialsSql($credentials, $conexion);
        if ($con->num_rows > 0)
        {
            return $credentials;
        }
    }
?>