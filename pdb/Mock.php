<?
namespace pdb;


/**
 * Mock class
 *
 * query always returns the array passed by constructor
 *
 */
class Mock implements SQL{
	private $_data;


	/**
	 * constructor
	 *
	 * @param array $data array to be used as result
	 */
	function __construct(array $data = array() ){
		$this->_data = array_values($data);
	}


	function getName(){
		return "test";
	}


	function getParamsHelp(){
		return array();
	}


	function open(){
		// Connected
	}


	function close(){
		// Disconnected
	}


	function escape($string){
		return $string;
	}


	function query($sql, array $params, $primaryKey = false){
		return new SQLResult(
			new ArrayResult($this->_data),
			$primaryKey);
	}
}

