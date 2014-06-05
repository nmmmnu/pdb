<?
namespace pdb;


/**
 * Adapter that converts SQLIntermediateResult to SQLResult
 *
 */
class SQLResult implements \Iterator{
	private $_result;

	private $_row;
	private $_rowID;


	/**
	 * constructor
	 *
	 * @param Iterator $iterator iterator with the row data
	 * @param int $affectedRows affected rows value for affectedRows()
	 * @param int $insertID last insert id for insertID()
	 */
	function __construct(SQLIntermediateResult $result){
		$this->_result		= $result;

		$this->_rowID		= 0;
	}


	function affectedRows(){
		return $this->_result->affectedRows();
	}


	function insertID(){
		return $this->_result->insertID();
	}


	// =============================

	function fetch(){
		return $this->_result->fetch();
	}


	function fetchField($field = false){
		$result = $this->fetch();

		if (!is_array($result))
			return null;

		if ($field === false)
			return array_values($result)[0];

		if (isset($result[$field]))
			return $result[$field];

		return null;
	}


	function fetchArray($primaryKey = false){
		$array = array();
		foreach($this as $row){
			if ($primaryKey !== false && isset($row[$primaryKey]))
				$array[$row[$primaryKey]] = $row;
			else
				$array[] = $row;
		}

		return $array;
	}


	// =============================


	function rewind(){
		if ($this->_rowID == 0){
			// Rewind is OK as long as hasNext() is not called.
			return;
		}

		throw new SQLException("SQLResult_results->rewind() unimplemented");
	}


	function current(){
		if ($this->_rowID == 0){
			// fetch first result
			$this->next();
		}

		if ($this->_row == null)
			return null;

		return $this->_row;
	}


	function key(){
		if ($this->_rowID == 0){
			// fetch first result
			$this->next();
		}

		return $this->_rowID - 1;
	}


	function next(){
		$this->_rowID++;
		$this->_row = $this->_result->fetch();
	}


	function valid(){
		if ($this->_rowID == 0){
			// fetch first result
			$this->next();
		}

		return $this->_row !== null;
	}


	// =============================


	static function test(){
		$data = array(
			array("name" => "niki", "age" => 22),
		);


		$sr = new self(new ArrayResult($data));
		assert($sr->fetchArray() == $data );
		assert($sr->affectedRows() == 1 );


		$sr = new self(new ArrayResult($data));
		assert($sr->fetch() == $data[0] );


		$sr = new self(new ArrayResult($data));
		assert($sr->fetchField() == "niki");


		$sr = new self(new ArrayResult($data));
		assert($sr->fetchField("age") == 22);
	}
}

