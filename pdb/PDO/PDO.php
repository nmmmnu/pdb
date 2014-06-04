<?
namespace pdb\PDO;

use pdb\SQL,
	pdb\SQLResult,
	pdb\Tools;


/**
 * PDO adapter
 *
 * query always returns the array passed by constructor
 *
 */
class PDO implements SQL{
	private $_pdo;
	private $_connection;


	function __construct(array $connection = array()){
		$this->_connection = $connection;
		$this->_pdo = null;
	}


	function getName(){
		return "pdo";
	}


	function getParamsHelp(){
		return array(
			"connection_string",
			"user",
			"password",

			"init_command"
		);
	}


	private function getC($what, $default = null){
		if (isset($this->_connection[$what]))
			return $this->_connection[$what];

		return $default;
	}


	function open(){
		if ($this->_pdo)
			return true;

		try{
			$this->_pdo = new \PDO(
				$this->getC("connection_string"),
				$this->getC("user"),
				$this->getC("password")
			);
		}catch( \PDOException $e ){
			return false;
		}

		if ($this->getC("init_command"))
			$this->query($this->getC("init_command"), array());

		return true;
	}


	function close(){
		$this->_pdo = NULL;

		return true;
	}


	function escape($string){
		$data = $this->_pdo->quote($string);

		// remove first and last single quote 'text'
		return substr($data, 1, strlen($data) - 2);
	}


	function query($sql, array $params, $primaryKey = null){
		$this->open();

		$sql = Tools::escapeQuery($this, $sql, $params);

		$result = $this->_pdo->query($sql);

		if ($result === false)
			return false;

		$lastID = $this->_pdo->lastInsertId();

		return new SQLResult(
			new PDOResult($result, $lastID),
			$primaryKey);
	}
}

