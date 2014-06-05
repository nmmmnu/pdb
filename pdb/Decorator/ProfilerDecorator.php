<?
namespace pdb\Decorator;

use pdb\SQL,
	pdb\Tools;

use pfc\Loggable,
	pfc\Logger;

use pfc\Profiler;


/**
 * Decorator that uses Profiler to profile the queries
 *
 */
class ProfilerDecorator implements SQL{
	use Loggable;


	private $_sqlAdapter;
	private $_profiler;


	/**
	 * constructor
	 *
	 * @param SQL $sqlAdapter
	 * @param Profiler $profiler
	 */
	function __construct(SQL $sqlAdapter, Profiler $profiler, Logger $logger){
		$this->_sqlAdapter = $sqlAdapter;
		$this->_profiler   = $profiler;

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

		$this->_profiler->stop("query start", $sql);
		$result = $this->_sqlAdapter->query($originalSQL, $params);
		$m = $this->_profiler->stop("query end", $sql);

		if ($result === false){
			$message = sprintf("Query **FAILED** for %s seconds...", $m);
		}else{
			$message = sprintf("Query executed for %s seconds, %d afected rows...",
				$m, $result->affectedRows() );
		}

		$this->logDebug($message);

		return $result;
	}

	// =======================

	static function test(){
		$profiler = new Profiler();

		$logger = new \pfc\Logger();
		$logger->addOutput(new \pfc\OutputAdapter\Console());

		$db = new self( \pdb\UnitTests\MockTests::factory(),  $profiler, $logger);
		\pdb\UnitTests\MockTests::test( $db );

		print_r($profiler->getData());
	}
}

