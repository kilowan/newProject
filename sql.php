<?php
    function checkCredentialsSql($credentials, $conexion)
    {
        return $conexion->query("SELECT C.*
		FROM credentials C INNER JOIN Empleados E
        ON E.id=C.employee
		WHERE E.borrado=0 AND C.username='$credentials->username' AND C.password='$credentials->password'");
    }

    function countPiezasSql($conexion)
    {
        return $conexion->query("SELECT pieza, COUNT(pieza) AS 'numeroP' 
        FROM parte
        WHERE state IN (3, 4)
        GROUP BY pieza");
    }
    //new
    function getAllEmployeeDataSql($conexion)
    {
        return $conexion->query("SELECT * FROM Empleados");
    }
	function tiempoMedioSql($conexion, $user)
	{
		return $conexion->query("SELECT ROUND(AVG(Tiempo),0) AS 'tiempo_medio', count(nom_tec) AS 'cantidad_partes', nom_tec
		FROM Tiempo_resolucion
		WHERE tec_res=$user->id
		GROUP BY nom_tec
		ORDER BY ROUND(AVG(Tiempo),0) DESC");
    }
    function tiempoMedioAdminSql($conexion)
    {
        return $conexion->query("SELECT ROUND(AVG(Tiempo),0) AS 'tiempo_medio', nom_tec FROM Tiempo_resolucion
        GROUP BY nom_tec");
    }
    function hideParteSql($conexion, $user, $id)
    {
        return $conexion->query("UPDATE parte SET state=4 WHERE id_part=$id AND emp_crea='$user->id' AND state=3");
    }
    function showHiddenParteSql($conexion, $id_part)
    {
        return $conexion->query("UPDATE parte SET state=3 WHERE id_part=$id_part");
    }
    function deleteParteSql($conexion, $id_part, $user)
    {
        return $conexion->query("DELETE 
        FROM parte 
        WHERE id_part=$id_part AND emp_crea=$user->id AND tec_res IS NULL");
    }
    //new
    function selectNotesSql($conexion, $id_part)
    {
        return $conexion->query("SELECT * 
        FROM notes
        WHERE incidence=$id_part");
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
    //new
    function insertCredentials2Sql($conexion, $credentials, $id)
    {
        $conexion->query("UPDATE credentials SET username='$credentials->username', password='$credentials->password' WHERE employee=$id");
    }
    /*function insertNoteSql($conexion, $id_part, $user, $inf_part)
    {
        $conexion->query("INSERT INTO notes VALUES ($id_part, $user->id, '$user->tipo', '$inf_part')");
    }*/
    function updateNoteListSql($conexion, $user, $id_part, $not_tec)
    {
        $conexion->query("INSERT INTO notes (employee, incidence, noteType, noteStr) VALUES ($user->id, $id_part, '$user->tipo', '$not_tec')");
    }
    function updateParte1Sql($conexion, $id_part, $user)
    {
        $nombre_tecnico = $user->name.' '.$user->surname1.' '.$user->surname2;
        $conexion->query("UPDATE parte  SET tec_res = $user->id, nom_tec='$nombre_tecnico', state=2
		WHERE id_part = $id_part AND (tec_res=$user->id OR tec_res IS NULL) AND state IN (1, 2)");
    }
    function updateparte2Sql($conexion, $pieza, $id_part, $user)
    {
        $nombre_tecnico = $user->name.' '.$user->surname1.' '.$user->surname2;
        $conexion->query("UPDATE parte SET pieza = '$pieza', nom_tec='$nombre_tecnico', tec_res = $user->id, state=2
		WHERE id_part = $id_part AND (tec_res=$user->id OR tec_res IS NULL) AND state IN (1, 2)");
    }
    function closeParte1Sql($conexion, $id_part, $user)
    {
        $nombre_tecnico = $user->name.' '.$user->surname1.' '.$user->surname2;
        $con = $conexion->query("UPDATE parte SET nom_tec = '$nombre_tecnico', fecha_resolucion=CURRENT_DATE(), hora_resolucion=CURRENT_TIME(), state=3, tec_res=$user->id  
		WHERE id_part = $id_part and (tec_res=$user->id or tec_res IS NULL) AND state IN (1, 2)");
    }
    function closeParte2Sql($conexion, $pieza, $id_part, $user)
    {
        $nombre_tecnico = $user->name.' '.$user->surname1.' '.$user->surname2;
        $con = $conexion->query("UPDATE parte SET pieza='$pieza', nom_tec='$nombre_tecnico', fecha_resolucion=CURRENT_DATE(), hora_resolucion=CURRENT_TIME(), state=3, tec_res=$user->id  
		WHERE id_part = $id_part AND (tec_res=$user->id or tec_res IS NULL) AND state IN (1, 2)");
    }
    function insertParte1Sql($conexion, $user, $descripcion, $pieza)
    {
        $conexion->query("INSERT INTO parte (emp_crea, inf_part , pieza)
        VALUES ($user->id, '$descripcion', '$pieza')");
    }
    function insertParte2Sql($conexion, $user, $descripcion)
    {
        $conexion->query("INSERT INTO parte (emp_crea, inf_part)
        VALUES ($user->id, '$descripcion')");
    }
    //new
    function deleteEmployeeSql($conexion, $user)
    {
        $conexion->query("UPDATE empleados SET borrado=1 WHERE id = $user->id");
    }
    function updateEmployeeSql($conexion, $user, $permissions)
    {
        $conexion->query("UPDATE empleados SET dni = '$user->dni', nombre='$user->name', apellido1='$user->surname1', apellido2='$user->surname2', tipo='$user->tipo' 
        WHERE id = $user->id");
        $conexion->query("DELETE employee_permissions WHERE employee=$user->id)");
        foreach ($permissions as $permission) {
            $conexion->query("INSERT INTO employee_permissions (employee, permission) VALUES ($id, $permission)");
        }
    }
    //new
    function selectIncidencesSql($conexion)
    {
        return $conexion->query("SELECT * FROM parte");
    }
    //new
    function getPermissionsSql($conexion, $id)
    {
        return $conexion->query("SELECT * FROM employee_permissions WHERE employee=$id");
    }
    function updateIncidence($conexion, $inf_part, $id_part)
    {
        $conexion->query("UPDATE parte SET inf_part='$inf_part' WHERE id_part=$id_part");
    }
?>