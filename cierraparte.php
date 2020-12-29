<?php
session_start();
?>
<?php
include 'sql.php';
if (isset($_SESSION['loggedin']))
{
    //Conexion Mysql
	$sql = json_decode($_SESSION['sql']);
	$conexion = new mysqli($sql->host_db, $sql->user_db, $sql->pass_db, $sql->db_name);
	$user = json_decode($_SESSION['user']);
	$id_part = $_POST['id_part'];
	$not_tec = $_POST['not_tec'];
    $pieza = $_POST['pieza'];
	if($pieza == '--')
	{
        closeParte1($conexion, $id_part, $user);
	}
	else
	{
        closeParte2($conexion, $pieza, $id_part, $user);
    }
    updateNoteList($conexion, $user, $id_part, $not_tec);
	$_SESSION['funcion'] = 'Partes';
	header('Location: veremp.php');	
}
else
{
	header('Location: menu.php');
}
mysqli_close($conexion);
?>