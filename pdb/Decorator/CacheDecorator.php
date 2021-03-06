<?
namespace pdb\Decorator;

use pdb\SQL,
	pdb\SQLResult,
	pdb\ArrayResult,
	pdb\Tools;

use pfc\Loggable,
	pfc\Logger;

use pfc\CacheAdapter\CacheAdapter;
use pfc\Serializer\Serializer;


/**
 * Decorator that caches the SQL using CacheAdapter and Serializer
 *
 */
class CacheDecorator implements SQL{
	use Loggable;


	private $_sqlAdapter;
	private $_cacheAdapter;
	private $_serializer;


	/**
	 * constructor
	 *
	 * @param SQL $sqlAdapter
	 * @param CacheAdapter $cacheAdapter
	 * @param Serializer $serializer
	 */
	function __construct(SQL $sqlAdapter, CacheAdapter $cacheAdapter, Serializer $serializer, Logger $logger = null){
		$this->_sqlAdapter	= $sqlAdapter;
		$this->_cacheAdapter	= $cacheAdapter;
		$this->_serializer	= $serializer;

		$this->setLogger($logger);
	}


	function getName(){
		return $this->_sqlAdapter->getName();
	}


	function getParamsHelp(){
		return $this->_sqlAdapter->getParamsHelp();
	}


	function open(){
		return $this->_sqlAdapter->open();
	}


	function close(){
		return $this->_sqlAdapter->close();
	}


	function escape($string){
		return $this->_sqlAdapter->escape($string);
	}


	function query($sql, array $params){
		$originalSQL = $sql;
		$sql = Tools::escapeQuery($this, $sql, $params);
		// load from cache
		$serializedData = $this->_cacheAdapter->load($sql);

		if ($serializedData !== false){
			$arrayData = $this->_serializer->unserialize($serializedData);
			unset($serializedData);

			// Corrupted data
			if (is_array($arrayData)){
				$this->logDebug("Cache hit...");

				return new SQLResult(
					new ArrayResult($arrayData)
					);
			}
		}

		$this->logDebug("Perform the query...");

		// perform the query
		$result = $this->_sqlAdapter->query($originalSQL, $params);

		if ($result === false)
			return false;

		// make the result array
		$arrayData = $result->fetchArray();

		// store in cache
		$serializedData = $this->_serializer->serialize($arrayData);
		$this->_cacheAdapter->store($sql, $serializedData);
		unset($serializedData);

		// the iterator can not be rewind.
		// this is why we use the SQLMockResult again.
		return new SQLResult(
			new ArrayResult($arrayData, $result->affectedRows(), $result->insertID())
			);
	}


	// =======================

	static function test(){
		$cacheAdapter = new \pfc\CacheAdapter\Shm("CacheDecorator_");
		$cacheAdapter->setTTL(10);


		$serializer = new \pfc\Serializer\JSON();


		$logger = new \pfc\Logger();
		$logger->addOutput(new \pfc\OutputAdapter\Console());


		$db = new self( \pdb\UnitTests\MockTests::factory(),  $cacheAdapter, $serializer, $logger);
		\pdb\UnitTests\MockTests::test( $db );
	}
}

