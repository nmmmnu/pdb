<?
namespace pdb\PDO;


use pdb\SQLIntermediateResult;


/**
 * PDO adapter for SQLIntermediateResult
 *
 */
class PDOResult implements SQLIntermediateResult{
	const FETCH_MODE = \PDO::FETCH_ASSOC;


	private $_pdo;


	private $_insertID;


	function __construct(\PDOStatement $query, $insertID = 0){
		$this->_pdo        = $query;
		$this->_insertID   = $insertID;
	}


	function affectedRows(){
		return $this->_pdo->rowCount();
	}


	function insertID(){
		return  $this->_insertID;
	}


	function fetch(){
		$data = $this->_pdo->fetch(self::FETCH_MODE);

		if (!$data)
			return null;

		return $data;
	}
}

