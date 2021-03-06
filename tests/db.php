<?
namespace tests;


error_reporting(E_ALL);


$conn = "pdosqlite";
//$conn = "pdomysql";
//$conn = "mysqli";
//$conn = "cql";


if ($conn == "pdosqlite"){
	// PDO with SQLITE

	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		$dir = sys_get_temp_dir();
	else
		$dir = "/dev/shm/";

	$connection = array(
		"connection_string" => "sqlite:" . $dir . "test.database.sqlite3"
	);

	$real_db = new \pdb\PDO\PDO($connection);


}else if ($conn == "pdomysql"){
	// PDO with MySQL

	$socket = "/tmp/akonadi-nmmm.*/mysql.socket";

	foreach(glob($socket) as $socket)
		break;

	$connection = array(
		"connection_string" => "mysql:unix_socket=$socket;dbname=test",
		"user"		=> "",
		"password"	=> ""
	);

	$real_db = new \pdb\PDO\PDO($connection);


}else if ($conn == "mysqli"){
	// MySQLi

	$socket = "/tmp/akonadi-nmmm.*/mysql.socket";

	foreach(glob($socket) as $socket)
		break;

	$connection = array(
		"database"	=> "test",
		"socket"	=> $socket
	);

	$real_db = new \pdb\MySQLi\MySQLi($connection);
}


$db = $real_db;
