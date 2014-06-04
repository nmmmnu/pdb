<?
namespace pdb;


/**
 * Adapter that converts Array to SQLIntermediateResult
 *
 * Useful for Mock objects, but also for cache, JSON etc.
 *
 */
class ArrayResult implements SQLIntermediateResult{
	private $_data;
	private $_pos;


	private $_insertID;


	/**
	 * constructor
	 *
	 * @param array $data iterator with the row data
	 * @param int $affectedRows affected rows value for affectedRows()
	 * @param int $insertID last insert id for insertID()
	 */
	function __construct(array $data, $insertID = 0){
		$this->_data		= array_values($data);
		$this->_pos		= 0;

		$this->_insertID	= $insertID;
	}


	function affectedRows(){
		return count($this->_data);
	}


	function insertID(){
		return $this->_insertID;
	}


	function fetch(){
		if ($this->_pos >= count($this->_data))
			return null;

		$row = $this->_data[$this->_pos];

		$this->_pos++;

		return $row;
	}
}

