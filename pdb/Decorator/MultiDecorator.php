<?
namespace pdb\Decorator;

use pdb\SQL,
	pdb\Tools;


use pfc\Loggable,
	pfc\Logger;


/**
 * Decorator that uses Profiler to profile the queries
 *
 */
class MultiDecorator implements SQL{
	use Loggable;


	private $_sqlAdapter = null;
	private $_sqlAdaptersKeys;
	private $_sqlAdapters;


	/**
	 * constructor
	 *
	 * @param array $sqlAdapter array of SQL adapters
	 *
	 */
	function __construct(array $sqlAdapters, Logger $logger = null){
		$array = array();
		foreach($sqlAdapters as $k => $adapter)
			if ($adapter instanceof SQL)
				$array[$k] = $adapter;

		if (count($array) == 0)
			throw new SQLException("You must provide at least one SQL adapter");


		$this->_sqlAdapters     = $array;
		$this->_sqlAdaptersKeys = array_keys($array);
		
		$this->setLogger($logger);
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

		shuffle($this->_sqlAdaptersKeys);

		foreach($this->_sqlAdaptersKeys as $k){
			$adapter = $this->_sqlAdapters[$k];
			
			$result = $adapter->open();

			if ($result){
				$this->_sqlAdapter = $adapter;

				$this->logDebug("Connect to SQL adapter '$k'.");

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


	function query($sql, array $params){
		if ($this->open() == false)
			return false;

		// We will not failover here,
		// because error could be just bad sql statement...
		return $this->_sqlAdapter->query($sql, $params);
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
		$adapters = array();
		
		for($i = 0; $i < 10; $i++)
			$adapters[] = \pdb\UnitTests\MockTests::factory();


		$logger = new \pfc\Logger();
		$logger->addOutput(new \pfc\OutputAdapter\Console());

		$db = new self($adapters);
		\pdb\UnitTests\MockTests::test( $db);


		$result = $db->query("select * from bla", array(), "id");
		$result = $result->fetchArray();
	}
}

