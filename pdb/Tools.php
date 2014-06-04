<?
namespace pdb;


final class Tools{
	static function escapeQuery(SQL $adapter, $sql_string, array $params){
		$adapter->open();

		$paramsEscaped = array();

		foreach($params as $p)
			$paramsEscaped[] = $adapter->escape($p);

		return vsprintf($sql_string, $paramsEscaped);
	}


}

