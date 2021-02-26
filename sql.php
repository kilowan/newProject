<?php
    //new
    function checkCredentialsSql($credentials, $conexion)
    {
        return $conexion->query("SELECT C.*
		FROM credentials C INNER JOIN Empleados E
        ON E.id=C.employee
		WHERE E.borrado=0 AND C.username='$credentials->username' AND C.password='$credentials->password'");
    }
    //new
    function piecesCountSql($conexion)
    {
        return $conexion->query("SELECT p.name AS pieceName, COUNT(ip.piece) AS 'pieceNumber' 
        FROM incidence_piece ip 
        INNER JOIN piece p
        ON p.id = ip.piece
        INNER JOIN parte pa
        ON ip.incidence = pa.id_part
        WHERE pa.state IN (3, 4)
        GROUP BY piece");
    }
    //old
	function tiempoMedioSql($conexion, $user)
	{
		return $conexion->query("SELECT ROUND(AVG(Tiempo),0) AS 'tiempo_medio', count(nom_tec) AS 'cantidad_partes', nom_tec
		FROM Tiempo_resolucion
		WHERE tec_res=$user->id
		GROUP BY nom_tec
		ORDER BY ROUND(AVG(Tiempo),0) DESC");
    }
    //new
    function insertEmployeeSql($conexion, $user, $credentials, $permissions)
    {
        $conexion->query("INSERT INTO empleados (dni, nombre, apellido1, apellido2, tipo)
        VALUES ('$user->dni', '$user->name', '$user->surname1', '$user->surname2' ,'$user->tipo')");
        $con = $conexion->query("SELECT * FROM Empleados WHERE dni = '$user->dni'");
        $data = $con->fetch_array(MYSQLI_ASSOC);
        $id = $data['id'];
        $conexion->query("INSERT INTO credentials (username, password, employee) VALUES ('$credentials->username', '$credentials->password', $id)");
        foreach ($permissions as $permission) {
            $conexion->query("INSERT INTO employee_permissions (employee, permission) VALUES ($id, $permission)");
        }
    }
    function updateEmployeeSql($conexion, $user, $permissions)
    {
        $conexion->query("UPDATE empleados SET dni = '$user->dni', nombre='$user->name', apellido1='$user->surname1', apellido2='$user->surname2', tipo='$user->tipo' 
        WHERE id = $user->id");
        $conexion->query("DELETE employee_permissions WHERE employee=$user->id)");
        foreach ($permissions as $permission) {
            $conexion->query("INSERT INTO employee_permissions (employee, permission) VALUES ($user->id, $permission)");
        }
    }
    //new
    function updateNoteSql($conexion, $note, $incidenceId, $employeeId)
    {
        $conexion->query("DELETE FROM notes WHERE incidence=$incidenceId");
        $conexion->query("INSERT INTO notes (employee, incidence, noteType, noteStr) VALUES ($employeeId, $incidenceId, 'Employee', '$note')");
    }
    //new
    function getPiecesSql($conexion, $id)
    {
        return $conexion->query("SELECT ip.piece, ip.incidence, p.name AS 'piece_name', p.price, p.quantity, p.description AS 'piece_description', pt.name AS 'piece_type_name', pt.description AS 'piece_type_description'
        FROM incidence_piece ip INNER JOIN piece p
        ON ip.piece=p.id
        INNER JOIN piece_type pt
        ON p.type=pt.id
        WHERE incidence=$id");
    }
    //new 
    function insertPiecesSql($conexion, $pieces, int $incidenceId)
    {
        foreach ($pieces as $piece) {
            $conexion->query("INSERT INTO incidence_piece (piece, incidence) VALUES ($piece, $incidenceId)");
        }
    }
    //new
    function insertSQL($conexion, $table, $columns, $values)
    {
        $text = 'INSERT INTO '.$table.' ('.implode(', ', $columns).') VALUES ('.implode(', ', $values).')';
        return $conexion->query($text);
    }
    //new
    function selectSQL($conexion, $table, $columns, $conditions = null, $group = null)
    {
        $text = 'SELECT '.implode(', ', $columns).' FROM '.$table;
        if ($conditions) {
            $text = $text.whereSQL($conditions);
        }
        if ($group) {
            $text = $text.groupBySQL($group);
        }
        return $conexion->query($text);
    }
    //new
    function whereSQL($conditions)
    {
        $position = 0;
        foreach ($conditions as $condition) {
            $result = $condition->column.' = '.$condition->value;
            $results[$position] = $result;
            $position++;
        }
        return ' WHERE '.implode(' AND ', $results);
    }
    //new
    function updateSQL($conexion, $table, $columns, $conditions)
    {
        $conditionsValues = [];
        $position = 0;
        foreach ($columns as $data) {
            $conditionsValues[$position] = $data->column.' = '.$data->value;
            $position++;
        }
        $text = 'UPDATE '.$table.' SET '.implode(' AND ', $conditionsValues).whereSQL($conditions);
        return $conexion->query($text);
    }
    //new
    function deleteSQL($conexion, $table, $where)
    {
        $text = 'DELETE FROM '.$table.whereSQL($where);
        return $conexion->query($text);
    }
    //new
    function groupBySQL($fields)
    {
        return ' GROUP BY '.implode(', ', $fields);
    }
?>