<?php
include 'sql.php';
include 'classes.php';
    define('OneMonth', 2592000);
    define('OneWeek', 604800);
    define('OneDay', 86400);
    define('OneHour', 3600);
    define('OneMinute', 60);
    //old
    function SecondsToTimeFn($seconds)
    { 
        $num_units = setNumUnitsFn($seconds);
        $time_descr = array( 
            "meses" => floor($seconds / OneMonth), 
            "semanas" => floor(($seconds%OneMonth) / OneWeek), 
            "días" => floor(($seconds%OneWeek) / OneDay), 
            "horas" => floor(($seconds%OneDay) / OneHour), 
            "minutos" => floor(($seconds%OneHour) / OneMinute), 
            "segundos" => floor($seconds%OneMinute), 
        );
        $res = ""; $counter = 0;
        foreach ($time_descr as $k => $v) 
        { 
            if ($v) 
            { 
                $res.=$v." ".$k; $counter++; 
                if($counter>=$num_units) break; 
                elseif($counter) 
                $res.=", "; 
            } 
        }
        $_SESSION['time'] = $res;
        return $_SESSION['time'];
    }
    //old
    function setNumUnitsFn($seconds)
    {
        switch ($seconds) {
            case $seconds>= OneMonth:
                return 6;

            case $seconds>= OneWeek:
                return 5;

            case $seconds>= OneDay:
                return 4;

            case $seconds>= OneHour:
                return 3;

            case $seconds>= OneMinute:
                return 2;

            default:
                return 1;
                break;
        }
    }
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
                    return ($array->state == 1 && $array->owner->dni != $_GET['dni']);
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

        return count($new_array) == 0 || $new_array == null? $empty : array_pop($new_array);
    }
    function getIncidencesListFn()
    {
        $conexion = connectionFn();
        $con = selectSQL($conexion, 'parte', ['*']);
        $incidences = null;
        $incidence_count = 0;
        while ($fila = $con->fetch_array(MYSQLI_ASSOC)) {
            $tec = new user();
            
            $noteList = null;
            $count = 0;
            $columns = makeConditionsFn(['incidence'], [$fila['id_part']]);
            $con2 = selectSQL($conexion, 'notes', ['*'], $columns);
            $inf_part = '';
            while ($notes = $con2->fetch_array(MYSQLI_ASSOC)) {
                $note = new note();
                if($notes['noteType'] == 'Employee') {
                    $inf_part = $notes['noteStr'];
                } else {
                    $note->noteStr = $notes['noteStr'];
                    $note->date = $notes['date'];
                    $noteList[$count] = $note;
                    $count++;
                }
            }
            $owner = getEmployeeByIdFn($fila['emp_crea']);


            if ($fila['tec_res'] != null && $fila['tec_res'] != "") {
                $tec = getEmployeeByIdFn($fila['tec_res']);
            }
            $pieces = getPiecesFn($fila['id_part']);

            $incidence = makeIncidenceFn($owner, $inf_part, $pieces, $noteList, $fila['state'], $tec, $fila['fecha_hora_creacion'], $fila['hora_resolucion'], $fila['fecha_resolucion'], $fila['id_part']);
            $incidences[$incidence_count] = $incidence;
            $incidence_count++;
        }
        return $incidences;
    }
    function getEmpolyeeListFn()
    {
        $conexion = connectionFn();
        $columns = makeConditionsFn(['borrado'], [0]);
        $con = selectSQL($conexion, 'Empleados', ['*'], $columns);
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
        $columns = makeConditionsFn(['employee'], [$id]);
        $con = selectSQL($conexion, 'employee_permissions', ['*'], $columns);
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
        $columns = makeConditionsFn(['id'], [$id]);
        $con = selectSQL($conexion, 'piece', ['*'], $columns);
        $fila = $con->fetch_array(MYSQLI_ASSOC);
        $piece = makePiece($fila['id'], $fila['name'], $fila['price'], $fila['description']);
        $type = getPieceTypeFn($conexion, $fila['type']);
        $piece->type = $type;
        return $piece;
    }
    function getPiecesByIdsFn($pieces)
    {
        $conexion = connectionFn();
        $counter = 0;
        foreach ($pieces as $piece) {
            $columns = makeConditionsFn(['id'], [$piece]);
            $con = selectSQL($conexion, 'piece', ['*'], $columns);
            $fila = $con->fetch_array(MYSQLI_ASSOC);
            $piece = makePiece($fila['id'], $fila['name'], $fila['price'], $fila['description']);
            $type = getPieceTypeFn($conexion, $fila['type']);
            $piece->type = $type;
            $pieces[$counter] = $piece;
            $counter++;
        }
        return $pieces;
    }
    function getPiecesListFn()
    {
        $conexion = connectionFn();
        $con = selectSQL($conexion, 'piece', ['*']);
        $pieces = null;
        $counter = 0;
        while ($fila = $con->fetch_array(MYSQLI_ASSOC))
        {
            $piece = makePiece($fila['id'], $fila['name'], $fila['price'], $fila['description']);
            $type = getPieceTypeFn($conexion, $fila['type']);
            $piece->type = $type;
            $pieces[$counter] = $piece;
            $counter++;
        }
        return $pieces;
    }
    function getPieceTypeFn($conexion, $type)
    {
        $condition = new dictionary();
        $condition->column = 'id';
        $condition->value = $type;
        $conditions = [];
        array_push($conditions, $condition);
        $con2 = selectSQL($conexion, 'piece_type', ['*'], $conditions);
        $fila2 = $con2->fetch_array(MYSQLI_ASSOC);
        $type = makePieceType($fila2['name'], $fila2['description']);
        return $type;
    }

    function addIncidenceFn($obj)
    {
        $conexion = connectionFn();
        $owner = getEmployeeByIdFn($obj->ownerId);
        insertSQL($conexion, 'parte', ['emp_crea', 'state'], [$owner->id, 1]);
        $con = selectSQL($conexion, 'parte', ["MAX(id_part) AS 'id_part'"]);
        $fila = $con->fetch_array(MYSQLI_ASSOC);
        $id = $fila['id_part'];
        insertSQL($conexion, 'notes', ['employee', 'incidence', 'noteType', 'noteStr'], [$owner->id, $id, 'Employee', $obj->issueDesc]);
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
    function getStatisticsFn($id)
    {
        $conexion = connectionFn();
        $con = tiempoMedioSql($conexion, getEmployeeByIdFn($id));
        $statistics = new statistics();
        if ($con->num_rows > 0) {
            $fila = $con->fetch_array(MYSQLI_ASSOC);
            $statistics->average = SecondsToTimeFn($fila['tiempo_medio']);
            $statistics->solvedIncidences = $fila['cantidad_partes'];
        }
        return $statistics;
    }
    function getReportedPiecesFn()
    {
        $conexion = connectionFn();
        $reportedPieces = [];
        $con = piecesCountSql($conexion);
        $number = 0;
        while ($fila = $con->fetch_array(MYSQLI_ASSOC)) {
            $reportedPiece = new reportedPiece();
            $reportedPiece->pieceName = $fila['pieceName'];
            $reportedPiece->pieceNumber = $fila['pieceNumber'];
            $reportedPieces[$number] = $reportedPiece;
            $number++;
        }
        return $reportedPieces;
    }
    function getGlobalStatisticsFn()
    {
        $conexion = connectionFn();
        $number = 0;
        $globalData = [];
        $con = tiempoMedioAdminSql($conexion);
        while ($fila = $con->fetch_array(MYSQLI_ASSOC)) {
            
            $globalStatistics = new statistics();
            $globalStatistics->average = SecondsToTimeFn($fila['tiempo_medio']);
            $globalStatistics->employeeName = $fila['nom_tec'];
            $globalData[$number] = $globalStatistics;
            $number++;
        }
        return $globalData;
    }
    function showIncidenceFn($id, $userId)
    {
        $conexion = connectionFn();
        $incidence = getIncidenceByIdFn($id);
        if ($incidence->state == 4 && $incidence->owner->id == $userId) {
            updateSQL($conexion, 'parte', makeConditionFn('state', 3), makeConditionFn('id_part', $id));
            return getIncidenceByIdFn($id);
        } else {
            return 'Error de inserción';
        }
    }
    function hideIncidenceFn($id, $userId)
    {
        $conexion = connectionFn();
        $incidence = getIncidenceByIdFn($id);
        if ($incidence->state == 3 && $incidence->owner->id == $userId) {
            updateSQL($conexion, 'parte', makeConditionFn('state', 4), makeConditionFn('id_part', $id));
            return getIncidenceByIdFn($id);
        } else {
            return 'Error de inserción';
        }
    }
    function updateNoteFn($note, $incidenceId, $employeeId)
    {
        $conexion = connectionFn();
        updateNoteSql($conexion, $note, $incidenceId, $employeeId);
        return 'OK';
    }
    function insertemployeeNoteFn($NoteDesc, $incidencesId, $userId)
    {
        $conexion = connectionFn();
        insertSQL($conexion, 'notes', ['employee', 'incidence', 'noteType', 'noteStr'], [$userId, $incidenceId, 'Employee', $NoteDesc]);
        return 'OK';
    }
    function inserttechnicianNoteFn($NoteDesc, $incidencesId, $userId)
    {
        $conexion = connectionFn();
        insertSQL($conexion, 'notes', ['employee', 'incidence', 'noteType', 'noteStr'], [$userId, $incidenceId, 'Technician', $NoteDesc]);
        return 'OK';
    }
    function deleteIncidenceFn($id_part, $userId)
    {
        $conexion = connectionFn();
        deleteIncidenceSql($conexion, $id_part, $userId);
    }
    function updateIncidenceFn($incidenceId, $userId, $note, $pieces, $close)
    {
        $conexion = connectionFn();
        $incidence = getIncidenceByIdFn($incidenceId);
        if ($incidence->solver->id == $userId || $incidence->state == 1) {
            if ($close) {
                closeIncidenceSql($conexion, $incidenceId, $userId);
            } else {
                updateSQL($conexion, 'parte', makeConditionsFn(['tec_res', 'state'], [$userId, 2]), makeConditionFn('id_part', $incidenceId));
            }
            insertNoteSql($conexion, $incidenceId, $userId, 'Technician', $note);
            insertPiecesSql($conexion, $pieces, $incidenceId);
            return getIncidenceByIdFn($userId);
        }
        return 'Inserción no satisfactoria';
    }
    function makeConditionFn($field, $value)
    {
        $column = new dictionary();
        $column->column = $field;
        $column->value = $value;
        $columns = [];
        array_push($columns, $column);
        return $columns;
    }
    function makeNewConditionFn($field, $value)
    {
        $column = new dictionary();
        $column->column = $field;
        $column->value = $value;
        return $column;
    }
    function makeConditionsFn($fields, $Values)
    {
        $columns = [];
        for ($i=0; $i < count($fields); $i++) { 
            $column = makeNewConditionFn($fields[$i], $Values[$i]);
            array_push($columns, $column);
        }
        return $columns;
    }
?>