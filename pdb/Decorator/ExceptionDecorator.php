<?
namespace pdb\Decorator;

use pdb\SQL,
	pdb\Tools,
	pdb\SQLException;

/**
 * Decorator that uses throw exception in case of SQL error
 *
 */
class ExceptionDecorator implements SQL{
	private $_sqlAdapter;


	/**
	 * constructor
	 *
	 * @param SQL $sqlAdapter
	 *
	 */
	function __construct(SQL $sqlAdapter){
		$this->_sqlAdapter = $sqlAdapter;
	}


	function getName(){
		return $this->_sqlAdapter->getName();
	}


	function getParamsHelp(){
		return $this->_sqlAdapter->getParamsHelp();
	}


	function open(){
		return $this->decorate(
			$this->_sqlAdapter->open(),
			__METHOD__
		);
	}


	function close(){
		return $this->decorate(
			$this->_sqlAdapter->close(),
			__METHOD__
		);
	}


	function escape($string){
		return $this->_sqlAdapter->escape($string);
	}


	function query($sql, array $params){
		$originalSQL = $sql;
		$sql = Tools::escapeQuery($this, $sql, $params);

		return $this->decorate(
			$this->_sqlAdapter->query($originalSQL, $params),
			__METHOD__,
			$sql
		);
	}


	private function decorate($value, $method, $sql = ""){
		if ($value)
			return $value;

		//echo $sql;

		$name = $this->_sqlAdapter->getName();

		throw new SQLException("SQL Exception in $name::$method()");
	}

	// =======================

	static function test(){
		$db = new self( \pdb\UnitTests\MockTests::factory() );
		\pdb\UnitTests\MockTests::test( $db );
	}
}

