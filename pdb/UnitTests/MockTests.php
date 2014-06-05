<?
namespace pdb\UnitTests;

class MockTests{
	static function factory($row = false){
		$array = array(
			array("id" => 1, "city" => "London"	),
			array("id" => 2, "city" => "Bonn"	),
			array("id" => 3, "city" => "Boston"	),
		);

		if ($row)
			$array[] = $row;

		return new \pdb\Mock($array);
	}

	static function test(\pdb\SQL $db, $affectedRows = 3){
		$result = $db->query("select * from bla", array());

		assert($result->affectedRows() == $affectedRows);

		$res = $result->fetchArray("city");

		//echo "You should see SQL result as array:\n";
		//print_r($res);

		assert($res["Bonn"]["id"  ] == 2      );
		assert($res["Bonn"]["city"] == "Bonn" );


		// ====================


		$result = $db->query("select * from bla", array() );

		assert($result->affectedRows() == $affectedRows);

		$res = $result->fetchArray();

		//echo "You should see SQL result as array:\n";
		//print_r($res);

		assert($res[1]["id"  ] == 2      );
		assert($res[1]["city"] == "Bonn" );
	}
}

