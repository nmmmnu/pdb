<?
namespace pdb;


/**
 * Adapter that converts empty result to SQLIntermediateResult
 *
 * Useful for Mock objects, but also for update / delete / insert
 *
 */
class EmptyResult implements SQLIntermediateResult{
	private $_affectedRows;
	private $_insertID;


	/**
	 * constructor
	 *
	 * @param int $insertID last insert id for insertID()
	 */
	function __construct($affectedRows = 0, $insertID = 0){
		$this->_affectedRows	= $affectedRows;
		$this->_insertID	= $insertID;
	}


	function affectedRows(){
		return $this->_affectedRows;
	}


	function insertID(){
		return $this->_insertID;
	}


	function fetch(){
		return null;
	}
}

