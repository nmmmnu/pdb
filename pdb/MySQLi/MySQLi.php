<?
namespace pdb\MySQLi;


use pdb\SQL,
	pdb\SQLResult,
	pdb\EmptyResult,
	pdb\Tools;


/**
 * PDO adapter
 *
 * query always returns the array passed by constructor
 *
 */
class MySQLi implements SQL{
	private $_link;
	private $_connection;


	function __construct(array $connection = array()){
		$this->_connection = $connection;
		$this->_link = null;
	}


	function getName(){
		return "mysqli";
	}


	function getParamsHelp(){
		return array(
			"host",
			"port",
			"database",
			"user",
			"password",
			"socket",

			"init_command"
		);
	}


	private function getC($what, $default = null){
		if (isset($this->_connection[$what]))
			return $this->_connection[$what];

		return $default;
	}


	function open(){
		if ($this->_link)
			return true;

		/*
		// not seems to be true
		if (@$this->_connection["socket"])
			@$this->_connection["host"] = ".";
		*/

		$this->_link = new \mysqli(
			$this->getC("host")	,
			$this->getC("user")	,
			$this->getC("password")	,
			$this->getC("database")	,
			$this->getC("port")	,
			$this->getC("socket")
		);

		if ($this->_link->connect_error){
			$this->_link = null;
			return false;
		}

		if ($this->getC("init_command"))
			$this->query($this->getC("init_command"), array());

		return true;
	}


	function close(){
		$this->_link->close();

		return true;
	}


	function escape($string){
		return $this->_link->real_escape_string($string);
	}


	function query($sql, array $params){
		if ($this->open() == false)
			return false;


		$sql = Tools::escapeQuery($this, $sql, $params);

		$result = $this->_link->query($sql);

		if ($result === false)
			return false;

		if ($result === true){
			// query with no results
			return new SQLResult(
				new EmptyResult(
					$this->_link->affected_rows,
					$this->_link->insert_id));
		}

		//var_dump($result);

		// result set
		return new SQLResult(
			new MySQLiResult($result)
			);
	}
}

