<?php
    function checkCredentialsSql($credentials, $conexion)
    {
        return $conexion->query("SELECT C.*
		FROM credentials C INNER JOIN Empleados E
        ON E.id=C.employee
		WHERE E.borrado=0 AND C.username='$credentials->username' AND C.password='$credentials->password'");
    }

    function selectNewPartesSql($conexion, $user)
	{
		//Partes sin atender propios
		return $conexion->query("SELECT P.id_part, P.fecha_hora_creacion, P.inf_part, P.pieza
		FROM parte P INNER JOIN Empleados E
		ON E.id=P.emp_crea 
		WHERE E.dni='$user->dni' AND E.id=$user->id AND P.state=1");
	}
	function selectOwnPartesSql($conexion, $user)
	{
		//Partes atendidos propios
		return $conexion->query("SELECT P.id_part, P.fecha_hora_creacion, P.inf_part, P.pieza, P.nom_tec
		FROM parte P INNER JOIN Empleados E
		ON E.id=P.emp_crea 
		WHERE E.dni='$user->dni' AND E.id=$user->id AND P.state=2");
	}
	function selectOtherPartesSql($conexion, $user)
	{
		//Partes atendidos no propios
		return $conexion->query("SELECT P.id_part, P.fecha_hora_creacion, P.inf_part, P.pieza, P.nom_tec
		FROM parte P INNER JOIN  Empleados E
		ON E.id=P.tec_res
		WHERE E.dni='$user->dni' AND P.state=2");
	}
	function selectNewOtherPartesSql($conexion, $user)
	{
		//Partes sin atender no propios
		return $conexion->query("SELECT P.id_part, P.fecha_hora_creacion, P.inf_part, P.pieza, E.nombre, E.apellido1, E.apellido2
		FROM parte P INNER JOIN Empleados E
        ON E.id=P.emp_crea
		WHERE E.dni!='$user->dni' AND P.state=1");
	}
	function countOldPartesSql($conexion, $user)
	{
		//Partes cerrados propios
		$con = $conexion->query("SELECT *
		FROM parte
        WHERE emp_crea=$user->id AND state=3");
        return $con->num_rows;
        
    }
    function countPiezasSql($conexion)
    {
        return $conexion->query("SELECT pieza, COUNT(pieza) AS 'numeroP' 
        FROM parte
        WHERE state IN (3, 4)
        GROUP BY pieza");
    }
	function selectOldOtherPartesSql($conexion, $user)
	{
		//Partes cerrados	
		return $conexion->query("SELECT T.tiempo, P.id_part, P.inf_part, P.fecha_hora_creacion, P.fecha_resolucion, P.hora_resolucion, P.emp_crea, P.pieza
		FROM Empleados E INNER JOIN parte P
		ON E.id=P.tec_res
		INNER JOIN tiempo_resolucion T
		ON T.id_part=P.id_part
		WHERE P.tec_res!=P.emp_crea and E.id=$user->id and E.dni='$user->dni' AND P.state=3");
	}
	function selectOldPartesSql($conexion, $user)
	{
		return $conexion->query("SELECT T.tiempo, P.id_part, P.nom_tec, P.inf_part, P.pieza, P.fecha_hora_creacion
		FROM Empleados E INNER JOIN parte P
		ON E.id=P.emp_crea
		INNER JOIN tiempo_resolucion T
		ON T.id_part=P.id_part
		WHERE E.id=$user->id AND E.dni='$user->dni' AND P.state=3");
    }
	function countHiddenPartesSql($conexion, $user)
	{
		$con = $conexion->query("SELECT COUNT(*) AS Partes
		FROM parte 
        WHERE state=4 AND emp_crea = $user->id");
        $result = $con->fetch_array(MYSQLI_ASSOC);
        return $result['Partes'];
    }
	function selectHiddenPartesSql($conexion, $user)
	{
		return $conexion->query("SELECT P.id_part, P.inf_part, P.pieza, E.nombre, E.apellido1, E.apellido2, E.id, P.fecha_resolucion, P.hora_resolucion, P.fecha_hora_creacion, nom_tec
		FROM parte P INNER JOIN Empleados E
		ON P.emp_crea=E.id 
		WHERE state=4 AND E.dni='$user->dni' 
		GROUP BY P.id_part, P.inf_part, E.nombre, E.id 
		ORDER BY P.id_part ASC");
	}
	function selectParteSql($conexion, $id_part)
	{
		return $conexion->query("SELECT *
		FROM parte
		WHERE id_part=$id_part AND state=1");
	}
    //new
	function getEmployeeByUsernameSql($conexion, $dni)
	{
        return $conexion->query("SELECT * FROM Empleados WHERE dni = '$dni'");
    }
    //new
    function getEmployeeSql($conexion, $id)
	{
        return $conexion->query("SELECT * FROM Empleados WHERE id = $id");
    }
    //new
    function getAllEmployeeDataSql($conexion)
    {
        return $conexion->query("SELECT * FROM Empleados");
    }
	function selectEmpleadoNoAdminSql($conexion)
	{
		$id_emp = $_GET['id_emp'];
		return $conexion->query("SELECT *
		FROM Empleados
		WHERE tipo NOT IN ('Admin') AND id=$id_emp");
	}
	function selectEmpleadosSql($conexion)
	{
		//Lista de empleados no administradores.
		return $conexion->query("SELECT id, dni, nombre, apellido1, apellido2, tipo
		FROM Empleados
		WHERE tipo NOT IN ('Admin') AND borrado=0");
    }
    function selectEmployeeSql($conexion)
    {
        $id_emp = $_GET['id_emp'];
        $dni = $_GET['dni'];
		return $conexion->query("SELECT *
		FROM Empleados
		WHERE id=$id_emp AND dni='$dni'");
    }
    //new
    function selectEmployeeDataSql($conexion, $credentials)
    {
        return $conexion->query("SELECT *
		FROM Empleados
		WHERE id='$credentials->employee' OR dni='$credentials->username'");
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
    function insertEmployeeSql($conexion, $user)
    {
        $conexion->query("INSERT INTO Empleados (dni, nombre, apellido1, apellido2, tipo)
        VALUES ('$user->dni', '$user->name', '$user->surname1', '$user->surname2' ,'$user->tipo')");
    }
    //new
    function insertCredentialsSql($conexion, $credentials, $id)
    {
        $conexion->query("INSERT INTO credentials (username, password, employee) VALUES ('$credentials->username', '$credentials->password', $id)");
    }
    //new
    function insertCredentials2Sql($conexion, $credentials, $id)
    {
        $conexion->query("UPDATE credentials SET username='$credentials->username', password='$credentials->password' WHERE employee=$id");
    }
    function insertNoteSql($id_part, $user, $inf_part)
    {
        $conexion->query("INSERT INTO notes VALUES ($id_part, $user->id, '$user->tipo', '$inf_part')");
    }
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
    function updateEmployeeSql($conexion, $user)
    {
        $conexion->query("UPDATE empleados SET dni = '$user->dni', nombre='$user->name', apellido1='$user->surname1', apellido2='$user->surname2', tipo='$user->tipo' 
        WHERE id = $user->id");
    }
    //new
    function insertEmployee2Sql($conexion, $user)
    {
        $conexion->query("UPDATE empleados SET nombre='$user->name', apellido1='$user->surname1', apellido2='$user->surname2', borrado=0 WHERE id=$user->id");
    }
    //new
    function selectIncidencesSql($conexion)
    {
        return $conexion->query("SELECT * FROM parte");
    }
    //new
    function getPermissionsSql($conexion, $user)
    {
        return $conexion->query("SELECT * FROM employee_permissions WHERE employee=$user->id");
    }
    //new
    function insertPermissionsSql($conexion, $user, $permission)
    {
        $conexion->query("INSERT INTO employee_permissions (employee, permission) VALUES ($user->id, $permission)");
    }
?>