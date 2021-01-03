<?php
    function checkCredentialsData($credentials, $conexion)
    {
        return $conexion->query("SELECT C.*
		FROM credentials C INNER JOIN Empleados E
        ON E.id=C.employee
		WHERE E.borrado=0 AND C.username='$credentials->username' AND C.password='$credentials->password'");
    }

    function selectNewPartes($conexion, $user)
	{
		//Partes sin atender propios
		return $conexion->query("SELECT P.id_part, P.fecha_hora_creacion, P.inf_part, P.pieza
		FROM parte P INNER JOIN Empleados E
		ON E.id=P.emp_crea 
		WHERE E.dni='$user->dni' AND E.id=$user->id AND P.state=1");
	}
	function selectOwnPartes($conexion, $user)
	{
		//Partes atendidos propios
		return $conexion->query("SELECT P.id_part, P.fecha_hora_creacion, P.inf_part, P.pieza, P.nom_tec
		FROM parte P INNER JOIN Empleados E
		ON E.id=P.emp_crea 
		WHERE E.dni='$user->dni' AND E.id=$user->id AND P.state=2");
	}
	function selectOtherPartes($conexion, $user)
	{
		//Partes atendidos no propios
		return $conexion->query("SELECT P.id_part, P.fecha_hora_creacion, P.inf_part, P.pieza, P.nom_tec
		FROM parte P INNER JOIN  Empleados E
		ON E.id=P.tec_res
		WHERE E.dni='$user->dni' AND P.state=2");
	}
	function selectNewOtherPartes($conexion, $user)
	{
		//Partes sin atender no propios
		return $conexion->query("SELECT P.id_part, P.fecha_hora_creacion, P.inf_part, P.pieza, E.nombre, E.apellido1, E.apellido2
		FROM parte P INNER JOIN Empleados E
        ON E.id=P.emp_crea
		WHERE E.dni!='$user->dni' AND P.state=1");
	}
	function countOldPartes($conexion, $user)
	{
		//Partes cerrados propios
		$con = $conexion->query("SELECT *
		FROM parte
        WHERE emp_crea=$user->id AND state=3");
        return $con->num_rows;
        
    }
    function countPiezas($conexion)
    {
        return $conexion->query("SELECT pieza, COUNT(pieza) AS 'numeroP' 
        FROM parte
        WHERE resuelto=1
        GROUP BY pieza");
    }
	function selectOldOtherPartes($conexion, $user)
	{
		//Partes cerrados	
		return $conexion->query("SELECT T.tiempo, P.id_part, P.inf_part, P.fecha_hora_creacion, P.fecha_resolucion, P.hora_resolucion, P.emp_crea, P.pieza
		FROM Empleados E INNER JOIN parte P
		ON E.id=P.tec_res
		INNER JOIN tiempo_resolucion T
		ON T.id_part=P.id_part
		WHERE P.tec_res!=P.emp_crea and E.id=$user->id and E.dni='$user->dni' AND P.state=3");
	}
	function selectOldPartes($conexion, $user)
	{
		return $conexion->query("SELECT T.tiempo, P.id_part, P.nom_tec, P.inf_part, P.pieza, P.fecha_hora_creacion
		FROM Empleados E INNER JOIN parte P
		ON E.id=P.emp_crea
		INNER JOIN tiempo_resolucion T
		ON T.id_part=P.id_part
		WHERE E.id=$user->id AND E.dni='$user->dni' AND P.oculto=0 AND P.state=3");
    }
	function countHiddenPartes($conexion, $user)
	{
		$con = $conexion->query("SELECT COUNT(*) AS Partes
		FROM parte 
        WHERE oculto=1 AND emp_crea = $user->id");
        $result = $con->fetch_array(MYSQLI_ASSOC);
        return $result['Partes'];
    }
	function selectHiddenPartes($conexion, $user)
	{
		return $conexion->query("SELECT P.id_part, P.inf_part, P.pieza, E.nombre, E.apellido1, E.apellido2, E.id, P.fecha_resolucion, P.hora_resolucion, P.fecha_hora_creacion, nom_tec
		FROM parte P INNER JOIN Empleados E
		ON P.emp_crea=E.id 
		WHERE oculto='1' AND E.dni='$user->dni' 
		GROUP BY P.id_part, P.inf_part, E.nombre, E.id 
		ORDER BY P.id_part ASC");
	}
	function selectParte($conexion, $id_part)
	{
		return $conexion->query("SELECT *
		FROM parte
		WHERE id_part=$id_part AND tec_res is null");
	}
	function selectFullDataParte($conexion, $id_part)
	{
		return $conexion->query("SELECT E.nombre, E.apellido1, E.apellido2, E.id, P.inf_part, P.pieza, P.fecha_hora_creacion 
		FROM Empleados E INNER JOIN parte P 
		ON E.id=P.emp_crea 
		WHERE id_part=$id_part");
    }
	function getEmployeeByUsername($conexion, $dni)
	{
        return $conexion->query("SELECT * FROM Empleados WHERE dni = '$dni'");
    }
    function getEmployee($conexion, $id)
	{
        return $conexion->query("SELECT * FROM Empleados WHERE id = $id");
    }
    function getAllEmployeeData($conexion)
    {
        return $conexion->query("SELECT * FROM Empleados");
    }
	function selectEmpleadoNoAdmin($conexion)
	{
		$id_emp = $_GET['id_emp'];
		return $conexion->query("SELECT *
		FROM Empleados
		WHERE tipo NOT IN ('Admin') AND id=$id_emp");
	}
	function selectEmpleados($conexion)
	{
		//Lista de empleados no administradores.
		return $conexion->query("SELECT id, dni, nombre, apellido1, apellido2, tipo
		FROM Empleados
		WHERE tipo NOT IN ('Admin') AND borrado=0");
    }
    function selectEmployee($conexion)
    {
        $id_emp = $_GET['id_emp'];
        $dni = $_GET['dni'];
		return $conexion->query("SELECT *
		FROM Empleados
		WHERE id=$id_emp AND dni='$dni'");
    }
    function selectEmployee2($conexion, $user)
    {
        //$id_emp = $_GET['id_emp'];
        //$dni = $_GET['dni'];
		return $conexion->query("SELECT *
		FROM Empleados
		WHERE id=$user->id AND dni='$user->dni'");
    }
    function selectEmployeeData($conexion, $credentials)
    {
        return $conexion->query("SELECT *
		FROM Empleados
		WHERE id='$credentials->employee' OR dni='$credentials->username'");
    }
	function tiempoMedio($conexion, $user)
	{
		return $conexion->query("SELECT ROUND(AVG(Tiempo),0) AS 'tiempo_medio', count(nom_tec) AS 'cantidad_partes', nom_tec
		FROM Tiempo_resolucion
		WHERE tec_res=$user->id
		GROUP BY nom_tec
		ORDER BY ROUND(AVG(Tiempo),0) DESC");
    }
    function tiempoMedioAdmin($conexion)
    {
        return $conexion->query("SELECT ROUND(AVG(Tiempo),0) AS 'tiempo_medio', nom_tec FROM Tiempo_resolucion
        GROUP BY nom_tec");
    }
    function countOwnPartes($conexion, $dni)
    {
        //partes no ocultos propios (empleado)
        $con = $conexion->query("SELECT COUNT(*) AS Partes
        FROM parte 
        WHERE oculto=0 AND emp_crea = (SELECT id FROM Empleados WHERE dni = '$dni')");
        $result = $con->fetch_array(MYSQLI_ASSOC);
        return $result['Partes'];
    }
    function countNewPartes($conexion)
    {
        //Partes sin atender (tecnico)
        $con = $conexion->query("SELECT *
        FROM parte
        WHERE state=1");
        return $con->num_rows;
    }
    //Partes de un técnico
    function countPartes($conexion, $id_emp)
    {
        $con = $conexion->query("SELECT *
        FROM parte 
        WHERE tec_res=$id_emp");
        return $con->num_rows;
    }
    function selectIncidence($conexion, $id)
    {
        $con = $conexion->query("SELECT * 
        FROM parte
        WHERE id_part=$id");
        return $con->fetch_array(MYSQLI_ASSOC);
    }
    function hideParte($conexion, $user, $id)
    {
        return $conexion->query("UPDATE parte SET oculto=1 WHERE id_part=$id AND emp_crea='$user->id' AND state=3");
    }
    function showHiddenParte($conexion, $id_part)
    {
        return $conexion->query("UPDATE parte SET oculto=0 WHERE id_part=$id_part");
    }
    function deleteParte($conexion, $id_part, $user)
    {
        return $conexion->query("DELETE 
        FROM parte 
        WHERE id_part=$id_part AND emp_crea=$user->id AND tec_res IS NULL");
    }
    function selectNotes($conexion, $id_part)
    {
        return $conexion->query("SELECT * 
        FROM notes
        WHERE incidence=$id_part");
    }
    function insertEmployee($conexion, $user)
    {
        $conexion->query("INSERT INTO Empleados (dni, nombre, apellido1, apellido2, tipo)
        VALUES ('$user->dni', '$user->name', '$user->surname1', '$user->surname2' ,'$user->tipo')");
    }
    function insertCredentials($conexion, $credentials, $id)
    {
        $conexion->query("INSERT INTO credentials (username, password, employee) VALUES ('$credentials->username', MD5('$credentials->password'), $id)");
    }
    function insertNote($id_part, $user, $inf_part)
    {
        $conexion->query("INSERT INTO notes VALUES ($id_part, $user->id, '$user->tipo', '$inf_part')");
    }
    function updateNoteList($conexion, $user, $id_part, $not_tec)
    {
        $conexion->query("INSERT INTO notes (employee, incidence, noteType, noteStr) VALUES ($user->id, $id_part, '$user->tipo', '$not_tec')");
    }
    function updateParte1($conexion, $id_part, $user)
    {
        $nombre_tecnico = $user->name.' '.$user->surname1.' '.$user->surname2;
        $conexion->query("UPDATE parte  SET tec_res = $user->id, nom_tec='$nombre_tecnico', state=2
		WHERE id_part = $id_part AND (tec_res=$user->id OR tec_res IS NULL) AND state IN (1, 2)");
    }
    function updateparte2($conexion, $pieza, $id_part, $user)
    {
        $nombre_tecnico = $user->name.' '.$user->surname1.' '.$user->surname2;
        $conexion->query("UPDATE parte SET pieza = '$pieza', nom_tec='$nombre_tecnico', tec_res = $user->id, state=2
		WHERE id_part = $id_part AND (tec_res=$user->id OR tec_res IS NULL) AND state IN (1, 2)");
    }
    function closeParte1($conexion, $id_part, $user)
    {
        $nombre_tecnico = $user->name.' '.$user->surname1.' '.$user->surname2;
        $con = $conexion->query("UPDATE parte SET nom_tec = '$nombre_tecnico', fecha_resolucion=CURRENT_DATE(), hora_resolucion=CURRENT_TIME(), state=3, tec_res=$user->id  
		WHERE id_part = $id_part and (tec_res=$user->id or tec_res IS NULL) AND state IN (1, 2)");
    }
    function closeParte2($conexion, $pieza, $id_part, $user)
    {
        $nombre_tecnico = $user->name.' '.$user->surname1.' '.$user->surname2;
        $con = $conexion->query("UPDATE parte SET pieza='$pieza', nom_tec='$nombre_tecnico', fecha_resolucion=CURRENT_DATE(), hora_resolucion=CURRENT_TIME(), state=3, tec_res=$user->id  
		WHERE id_part = $id_part AND (tec_res=$user->id or tec_res IS NULL) AND state IN (1, 2)");
    }
    function insertParte1($conexion, $user, $descripcion, $pieza)
    {
        $conexion->query("INSERT INTO parte (emp_crea, inf_part , pieza)
        VALUES ($user->id, '$descripcion', '$pieza')");
    }
    function insertParte2($conexion, $user, $descripcion)
    {
        $conexion->query("INSERT INTO parte (emp_crea, inf_part)
        VALUES ($user->id, '$descripcion')");
    }
    function deleteEmployee($conexion, $user)
    {
        $conexion->query("UPDATE empleados SET borrado=1 WHERE id = $user->id");
    }
    function updateEmployee($conexion, $user)
    {
        $conexion->query("UPDATE Empleados SET dni = '$user->dni', nombre = '$user->name', apellido1 = '$user->surname1', apellido2 = '$user->surname2', tipo = '$user->tipo' 
        WHERE id = $user->id");
    }
    function selectIncidences($conexion)
    {
        return $conexion->query("SELECT * FROM parte");
    }
?>