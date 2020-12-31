<?php
include 'sql.php';
include 'classes.php';
    $json = file_get_contents('php://input');
    $obj = json_decode($json);
    $funcion = $obj->funcion;
    $conexion = connection();
    
    switch ($funcion) {
        case 'service01':
            service01();
            break;        
        case 'service02':
            service02();
            break;
            
        default:
            break;
    }
    function connection()
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
    function getEmployeeData($credentials)
    {
        $conexion = connection();
        $con = checkCredentialsData($credentials, $conexion);
        if ($con->num_rows > 0)
        {
            //extrae datos personales
            $con = selectEmployeeData($conexion, $credentials);
            $fila = $con->fetch_array(MYSQLI_ASSOC);
            $user_info = getEmployee($fila);
            $_SESSION['user'] =  json_encode($user_info);
            return $user_info;
        }
        else 
        {
            echo 'error desconocido';
        }
    }
    function getEmployee($fila)
    {
        $user = new user;
        $user->dni = $fila['dni'];
        $user->name = $fila['nombre'];
        $user->surname1 = $fila['apellido1'];
        $user->surname2 = $fila['apellido2'];
        $user->tipo = $fila['tipo'];
        $user->id = $fila['id'];
        return $user;
    }

    function service01()
    {
        session_start();
        $conexion = connection();
        $emp_crea = $_GET['id_emp'];
        $user = takeEmployee($conexion, $emp_crea);
        header('Content-Type: application/json');
        echo json_encode($user);
        exit();
    }
    function service02()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json);
        $credentials = new credentials($obj->username, $obj->password);
        $_SESSION['credentials'] = json_encode($credentials);
        $user = getEmployeeData($credentials);
        if ($user != 'error desconocido') {
            header('Content-Type: application/json');
            echo json_encode($user);
            exit();
        }
        else {
            echo $user;
            exit();
        }
    }
?>