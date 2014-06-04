<?
namespace pdb\Decorator;

use pdb\SQL,
	pdb\Tools;


/**
 * Decorator that uses Profiler to profile the queries
 *
 */
class MultiDecorator implements SQL{
	private $_sqlAdapter = null;
	private $_sqlAdapters;


	/**
	 * constructor
	 *
	 * @param array $sqlAdapter array of SQL adapters
	 * 
	 */
	function __construct(array $sqlAdapters){
		$array = array();
		foreach($sqlAdapters as $adapter)
			if ($adapter instanceof SQL)
				$array[] = $adapter;
		
		if (count($array) == 0)
			throw new SQLException("You must provide at least one SQL adapter");
			
		shuffle($array);
		
		$this->_sqlAdapters = $array;
	}


	function getName(){
		return $this->getAdapter()->getName();
	}


	function getParamsHelp(){
		return $this->getAdapter()->getParamsHelp();
	}


	function open(){
		if ($this->_sqlAdapter)
			return true;
		
		foreach($this->_sqlAdapters as $adapter){
			$result = $adapter->open();
			
			if ($result){
				$this->_sqlAdapter = $adapter;
				
				return true;
			}
			
			// open() do failover
			// result is false, try next adapter...
		}
		
		// no more adapters...
		return false;
	}


	function close(){
		if ($this->_sqlAdapter)
			return $this->_sqlAdapter->close();
		
		return true;
	}


	function escape($string){
		return $this->getAdapter()->escape($string);
	}


	function query($sql, array $params, $primaryKey = null){
		if ($this->open() == false)
			return false;

		// We will not failover here,
		// because error could be just bad sql statement...
		return $this->_sqlAdapter->query($sql, $params, $primaryKey);
	}


	/**
	 * getAdapter
	 * 
	 * return adapter for not critical uses, such getName()
	 */
	protected function getAdapter(){
		if ($this->_sqlAdapter)
			return $this->_sqlAdapter;
		
		return $this->_sqlAdapters[0];
	}
	
	
	// =======================


	static function test(){
		$adapters = array(
			\pdb\UnitTests\MockTests::factory(),
			\pdb\UnitTests\MockTests::factory(),
			\pdb\UnitTests\MockTests::factory()
		);

		$db = new self($adapters);
		\pdb\UnitTests\MockTests::test( $db );
	}
}

