<?
namespace pfc\SQL;

use pfc\SQL;

use pfc\Loggable,
	pfc\Logger;

use pfc\Profiler;


/**
 * Decorator that uses Profiler to profile the queries
 *
 */
class ProfilerDecorator implements SQL{
	use TraitEscape;
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


	function query($sql, array $params, $primaryKey = null){
		$originalSQL = $sql;
		$sql = $this->escapeQuery($sql, $params);

		$this->_profiler->stop("query start", $sql);
		$result = $this->_sqlAdapter->query($originalSQL, $params, $primaryKey);
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
}

