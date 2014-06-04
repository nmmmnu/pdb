<?
$THRIFT_PATH = "/home/nmmm/GIT/cql_connect";


require_once __DIR__ . "/__autoload.php";


spl_autoload_register(
	function($class){
		global $THRIFT_PATH;

		$parts = explode("\\", $class);

		if ($parts[0] != "Thrift")
			return;

		$file = implode("/", $parts) . ".php";

		$file = $THRIFT_PATH . "/" . $file;

		//echo "Loading $file...\n";

		require_once $file;
	}
);

require_once $THRIFT_PATH . '/cassandra/Types.php';
require_once $THRIFT_PATH . '/cassandra/Cassandra.php';


